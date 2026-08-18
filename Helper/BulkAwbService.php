<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Helper;

use Exception;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Sameday\Exceptions\SamedayBadRequestException;
use Sameday\Objects\ParcelDimensionsObject;
use Sameday\Objects\PostAwb\Request\AwbRecipientEntityObject;
use Sameday\Objects\PostAwb\Request\CompanyEntityObject;
use Sameday\Objects\Types\AwbPaymentType;
use Sameday\Objects\Types\PackageType;
use Sameday\Requests\SamedayDeleteAwbRequest;
use Sameday\Requests\SamedayPostAwbRequest;
use Sameday\Responses\SamedayPostAwbResponse;
use Sameday\Sameday;
use SamedayCourier\Shipping\Api\AwbRepositoryInterface;
use SamedayCourier\Shipping\Api\Data\AwbInterfaceFactory;
use SamedayCourier\Shipping\Api\Data\OrderBulkAwbInterface;
use SamedayCourier\Shipping\Api\OrderBulkAwbRepositoryInterface;
use SamedayCourier\Shipping\Api\PickupPointRepositoryInterface;
use SamedayCourier\Shipping\Api\ServiceRepositoryInterface;

class BulkAwbService
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var AwbRepositoryInterface
     */
    private $awbRepository;

    /**
     * @var AwbInterfaceFactory
     */
    private $awbFactory;

    /**
     * @var OrderBulkAwbRepositoryInterface
     */
    private $orderBulkAwbRepository;

    /**
     * @var ServiceRepositoryInterface
     */
    private $serviceRepository;

    /**
     * @var PickupPointRepositoryInterface
     */
    private $pickupPointRepository;

    /**
     * @var ApiHelper
     */
    private $apiHelper;

    /**
     * @var StoredDataHelper
     */
    private $storedDataHelper;

    /**
     * @var ShippingService
     */
    private $shippingService;

    /**
     * @var OrderShipmentHelper
     */
    private $orderShipmentHelper;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        AwbRepositoryInterface $awbRepository,
        AwbInterfaceFactory $awbFactory,
        OrderBulkAwbRepositoryInterface $orderBulkAwbRepository,
        ServiceRepositoryInterface $serviceRepository,
        PickupPointRepositoryInterface $pickupPointRepository,
        ApiHelper $apiHelper,
        StoredDataHelper $storedDataHelper,
        ShippingService $shippingService,
        OrderShipmentHelper $orderShipmentHelper,
        Json $serializer,
        UrlInterface $urlBuilder
    ) {
        $this->orderRepository = $orderRepository;
        $this->awbRepository = $awbRepository;
        $this->awbFactory = $awbFactory;
        $this->orderBulkAwbRepository = $orderBulkAwbRepository;
        $this->serviceRepository = $serviceRepository;
        $this->pickupPointRepository = $pickupPointRepository;
        $this->apiHelper = $apiHelper;
        $this->storedDataHelper = $storedDataHelper;
        $this->shippingService = $shippingService;
        $this->orderShipmentHelper = $orderShipmentHelper;
        $this->serializer = $serializer;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return array{success:bool,order_id:int,skipped?:bool,awb_number?:string,message?:string,error?:string,feedback:string,actions_html:string}
     */
    public function generateForOrder(int $orderId): array
    {
        try {
            return $this->doGenerateForOrder($orderId);
        } catch (Exception $e) {
            return $this->fail($orderId, $e->getMessage() ?: (string) __('AWB generation failed.'));
        }
    }

    /**
     * @return array{success:bool,order_id:int,skipped?:bool,awb_number?:string,message?:string,error?:string,feedback:string,actions_html:string}
     */
    private function doGenerateForOrder(int $orderId): array
    {
        try {
            $existingAwb = $this->awbRepository->getByOrderId($orderId);
            if ($existingAwb && $existingAwb->getAwbNumber()) {
                $payload = [
                    'success' => false,
                    'order_id' => $orderId,
                    'skipped' => true,
                    'awb_number' => $existingAwb->getAwbNumber(),
                    'message' => (string) __('AWB is already generated.'),
                ];
                $this->orderBulkAwbRepository->updateFeedback(
                    $orderId,
                    OrderBulkAwbInterface::STATUS_SUCCESS,
                    ['awb_number' => $existingAwb->getAwbNumber()]
                );

                return $this->enrichResponse($payload, $orderId);
            }
        } catch (NoSuchEntityException $e) {
            // continue — no AWB yet
        }

        try {
            /** @var Order $order */
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            return $this->fail($orderId, (string) __('Order not found.'));
        }

        $serviceCode = $this->resolveServiceCode($order);
        if ($serviceCode === null) {
            return $this->fail($orderId, (string) __('Order shipping method is not a Sameday service.'));
        }

        try {
            $service = $this->serviceRepository->getBySamedayCode(
                $serviceCode,
                $this->isTestingMode()
            );
        } catch (NoSuchEntityException $e) {
            return $this->fail($orderId, (string) __('Sameday service not found for this order.'));
        }

        try {
            $pickupPoint = $this->pickupPointRepository->getDefaultPickupPoint();
        } catch (NoSuchEntityException $e) {
            return $this->fail($orderId, (string) __('Default pickup point not found.'));
        }

        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress) {
            return $this->fail($orderId, (string) __('Shipping address is missing.'));
        }

        $email = (string) ($order->getCustomerEmail() ?? '');
        $phone = (string) ($shippingAddress->getTelephone() ?? '');
        if ($email === '') {
            return $this->fail($orderId, (string) __('Must complete email address!'));
        }
        if ($phone === '') {
            return $this->fail($orderId, (string) __('Must complete phone number!'));
        }

        $lockerLastMile = null;
        $oohLastMile = null;

        if ($this->apiHelper->isEligibleToLocker((string) $service->getCode())) {
            $locker = $this->unserializeArray($order->getData('samedaycourier_locker'));
            if (empty($locker['lockerId'])) {
                return $this->fail($orderId, (string) __('Locker details are required for this service.'));
            }

            $this->shippingService->updateShippingAddress(
                $shippingAddress,
                (string) ($locker['city'] ?? ''),
                (string) ($locker['county'] ?? ''),
                sprintf('%s (%s)', (string) ($locker['address'] ?? ''), (string) ($locker['name'] ?? ''))
            );

            if ($service->getCode() === GeneralHelper::SAMEDAY_SERVICE_LOCKER_CODE) {
                $lockerLastMile = (int) $locker['lockerId'];
            }
            if ($service->getCode() === GeneralHelper::SAMEDAY_SERVICE_PUDO_CODE) {
                $oohLastMile = (int) $locker['lockerId'];
            }
        } elseif ($order->getData('samedaycourier_destination_address_hd')) {
            $hdAddress = $this->unserializeArray($order->getData('samedaycourier_destination_address_hd'));
            $street = $hdAddress['street'] ?? '';
            $this->shippingService->updateShippingAddress(
                $shippingAddress,
                (string) ($hdAddress['city'] ?? ''),
                (string) ($hdAddress['region'] ?? ''),
                is_array($street) ? implode(' ', $street) : (string) $street
            );
        }

        $regionName = (string) $shippingAddress->getRegion();
        $city = (string) $shippingAddress->getCity();
        $address = implode(' ', $shippingAddress->getStreet());
        $contactPerson = trim(sprintf('%s %s', $shippingAddress->getFirstname(), $shippingAddress->getLastname()));
        $postalCode = (string) $shippingAddress->getPostcode();

        $company = null;
        if ($shippingAddress->getCompany() && trim((string) $shippingAddress->getCompany()) !== '') {
            $company = new CompanyEntityObject($shippingAddress->getCompany());
        }

        $weight = (float) $order->getWeight();
        if ($weight < 0.1) {
            $weight = 1.0;
        }

        $repayment = 0.0;
        $payment = $order->getPayment();
        if ($payment && in_array($payment->getMethod(), StoredDataHelper::COD_OPTIONS, true)) {
            $repayment = (float) $order->getGrandTotal();
        }

        $apiRequest = new SamedayPostAwbRequest(
            (int) $pickupPoint->getSamedayId(),
            null,
            new PackageType(PackageType::PARCEL),
            [new ParcelDimensionsObject($weight)],
            (int) $service->getSamedayId(),
            new AwbPaymentType(AwbPaymentType::CLIENT),
            new AwbRecipientEntityObject(
                $city,
                $regionName,
                $address,
                $contactPerson,
                $phone,
                $email,
                $company,
                $postalCode
            ),
            0,
            $repayment,
            null,
            null,
            [],
            null,
            $order->getIncrementId() . '_' . time(),
            null,
            null,
            '',
            null,
            $lockerLastMile,
            null,
            $oohLastMile,
            $this->storedDataHelper->buildDestCurrency($shippingAddress->getCountryId())
        );

        try {
            /** @var SamedayPostAwbResponse $response */
            $response = (new Sameday($this->apiHelper->initClient()))->postAwb($apiRequest);
        } catch (SamedayBadRequestException $e) {
            return $this->fail($orderId, $this->formatBadRequest($e));
        } catch (Exception $e) {
            return $this->fail($orderId, $e->getMessage());
        }

        if (!$response || empty($response->getParcels()[0])) {
            return $this->fail($orderId, (string) __('AWB generation failed.'));
        }

        $this->shippingService->updateShippingMethod($order, $service->getName());

        $parcelsArr = [];
        foreach ($response->getParcels() as $index => $parcelResponse) {
            $parcelsArr[$index]['position'] = $parcelResponse->getPosition();
            $parcelsArr[$index]['awbNumber'] = $parcelResponse->getAwbNumber();
        }

        $awb = $this->awbFactory->create()
            ->setOrderId($orderId)
            ->setAwbNumber($response->getAwbNumber())
            ->setAwbCost($repayment)
            ->setParcels($this->serializer->serialize($parcelsArr));
        $this->awbRepository->save($awb);

        if (null !== $orderShipment = $this->orderShipmentHelper->saveOrderShipment($order)) {
            $this->orderShipmentHelper->saveTracking(
                $orderShipment,
                [
                    'carrier_code' => ShippingService::SHIPPING_METHOD_CODE,
                    'title' => $service->getName(),
                    'number' => $awb->getAwbNumber(),
                ]
            );
        }

        $this->orderBulkAwbRepository->updateFeedback(
            $orderId,
            OrderBulkAwbInterface::STATUS_SUCCESS,
            ['awb_number' => $response->getAwbNumber()]
        );

        return $this->enrichResponse([
            'success' => true,
            'order_id' => $orderId,
            'awb_number' => $response->getAwbNumber(),
            'message' => (string) __('AWB was generated.'),
        ], $orderId);
    }

    /**
     * @return array{success:bool,order_id:int,awb_number?:string,message?:string,error?:string,feedback:string,actions_html:string}
     */
    public function removeForOrder(int $orderId): array
    {
        try {
            $awb = $this->awbRepository->getByOrderId($orderId);
        } catch (NoSuchEntityException $e) {
            return $this->enrichResponse([
                'success' => false,
                'order_id' => $orderId,
                'error' => (string) __('AWB not found for this order.'),
            ], $orderId);
        }

        try {
            $apiRequest = new SamedayDeleteAwbRequest($awb->getAwbNumber());
            (new Sameday($this->apiHelper->initClient()))->deleteAwb($apiRequest);
            $this->awbRepository->deleteById($awb->getId());
            $this->orderBulkAwbRepository->deleteByOrderId($orderId);

            return $this->enrichResponse([
                'success' => true,
                'order_id' => $orderId,
                'awb_number' => $awb->getAwbNumber(),
                'message' => (string) __('AWB was canceled'),
            ], $orderId);
        } catch (Exception $e) {
            return $this->enrichResponse([
                'success' => false,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ], $orderId);
        }
    }

    /**
     * @return array{success:bool,deleted:int,order_ids:int[]}
     */
    public function clearErrors(): array
    {
        $orderIds = $this->orderBulkAwbRepository->clearWithoutGeneratedAwb();

        return [
            'success' => true,
            'deleted' => count($orderIds),
            'order_ids' => $orderIds,
        ];
    }

    public function formatFeedbackHtml(int $orderId): string
    {
        try {
            $awb = $this->awbRepository->getByOrderId($orderId);
            if ($awb && $awb->getAwbNumber()) {
                return $this->formatAwbBadgeHtml((string) $awb->getAwbNumber());
            }
        } catch (NoSuchEntityException $e) {
            // continue with bulk feedback
        }

        $bulk = $this->orderBulkAwbRepository->getByOrderId($orderId);
        if (!$bulk) {
            return '—';
        }

        $payload = [];
        if ($bulk->getFeedback()) {
            try {
                $payload = $this->serializer->unserialize($bulk->getFeedback());
            } catch (Exception $e) {
                $payload = [];
            }
        }

        if ((int) $bulk->getStatus() === OrderBulkAwbInterface::STATUS_ERROR) {
            $error = isset($payload['error']) ? (string) $payload['error'] : (string) __('Error');
            return '<span class="sameday-feedback-error">' .
                htmlspecialchars($error, ENT_QUOTES, 'UTF-8') .
                '</span>';
        }

        if (!empty($payload['awb_number'])) {
            return $this->formatAwbBadgeHtml((string) $payload['awb_number']);
        }

        if ((int) $bulk->getStatus() === OrderBulkAwbInterface::STATUS_PENDING) {
            return '<span class="sameday-feedback-pending">' .
                htmlspecialchars((string) __('Pending'), ENT_QUOTES, 'UTF-8') .
                '</span>';
        }

        return '—';
    }

    public function formatActionsHtml(int $orderId): string
    {
        $awb = null;
        try {
            $awb = $this->awbRepository->getByOrderId($orderId);
        } catch (NoSuchEntityException $e) {
            $awb = null;
        }

        $buttons = [];

        if ($awb && $awb->getAwbNumber()) {
            $buttons[] = sprintf(
                '<a class="action-default scalable sameday-grid-action" href="%s" onclick="event.stopPropagation();">%s</a>',
                htmlspecialchars(
                    $this->getAdminUrl('samedaycourier_shipping/awb_parcel/form', ['order_id' => $orderId]),
                    ENT_QUOTES,
                    'UTF-8'
                ),
                htmlspecialchars((string) __('Add Parcel'), ENT_QUOTES, 'UTF-8')
            );
            $buttons[] = sprintf(
                '<a class="action-default scalable sameday-grid-action" href="%s" target="_blank" rel="noopener" onclick="event.stopPropagation();">%s</a>',
                htmlspecialchars(
                    $this->getAdminUrl('samedaycourier_shipping/order/showpdf', ['order_id' => $orderId]),
                    ENT_QUOTES,
                    'UTF-8'
                ),
                htmlspecialchars((string) __('Show PDF'), ENT_QUOTES, 'UTF-8')
            );
            $buttons[] = sprintf(
                '<a class="action-default scalable sameday-grid-action sameday-grid-remove-awb" href="#" ' .
                'data-order-id="%d" data-awb-id="%d" data-awb-number="%s" onclick="event.preventDefault(); event.stopPropagation();">%s</a>',
                $orderId,
                (int) $awb->getId(),
                htmlspecialchars((string) $awb->getAwbNumber(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) __('Remove AWB'), ENT_QUOTES, 'UTF-8')
            );
        } else {
            $buttons[] = sprintf(
                '<a class="action-default scalable sameday-grid-action" href="%s" onclick="event.stopPropagation();">%s</a>',
                htmlspecialchars(
                    $this->getAdminUrl('samedaycourier_shipping/awb/form', ['order_id' => $orderId]),
                    ENT_QUOTES,
                    'UTF-8'
                ),
                htmlspecialchars((string) __('Generate AWB'), ENT_QUOTES, 'UTF-8')
            );
        }

        return '<div class="sameday-actions-buttons" onclick="event.stopPropagation();">' .
            implode('', $buttons) .
            '</div>';
    }

    private function formatAwbBadgeHtml(string $awbNumber): string
    {
        return '<span class="sameday-awb-badge" title="AWB">' .
            htmlspecialchars($awbNumber, ENT_QUOTES, 'UTF-8') .
            '</span>';
    }

    private function fail(int $orderId, string $error): array
    {
        $this->orderBulkAwbRepository->updateFeedback(
            $orderId,
            OrderBulkAwbInterface::STATUS_ERROR,
            ['error' => $error]
        );

        return $this->enrichResponse([
            'success' => false,
            'order_id' => $orderId,
            'error' => $error,
        ], $orderId);
    }

    private function enrichResponse(array $payload, int $orderId, bool $includeFeedback = true): array
    {
        $payload['feedback'] = $includeFeedback ? $this->formatFeedbackHtml($orderId) : '—';
        $payload['actions_html'] = $this->formatActionsHtml($orderId);

        return $payload;
    }

    private function resolveServiceCode(OrderInterface $order): ?string
    {
        $shippingMethod = (string) $order->getShippingMethod();
        $prefix = ShippingService::SHIPPING_METHOD_CODE . '_';
        if (strpos($shippingMethod, $prefix) !== 0) {
            return null;
        }

        $code = substr($shippingMethod, strlen($prefix));
        return $code !== '' ? $code : null;
    }

    private function formatBadRequest(SamedayBadRequestException $e): string
    {
        $allErrors = ['Bad Request'];
        foreach ($e->getErrors() as $error) {
            if (!is_array($error) || empty($error['errors']) || !is_array($error['errors'])) {
                continue;
            }
            $key = isset($error['key']) && is_array($error['key']) ? implode('.', $error['key']) : '';
            foreach ($error['errors'] as $message) {
                $allErrors[] = ($key !== '' ? $key . ': ' : '') . $message;
            }
        }

        return implode(' ', $allErrors);
    }

    private function unserializeArray($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        try {
            $data = $this->serializer->unserialize($value);
        } catch (Exception $e) {
            return [];
        }

        return is_array($data) ? $data : [];
    }

    private function isTestingMode(): bool
    {
        return filter_var($this->apiHelper->getEnvMode(), FILTER_VALIDATE_BOOLEAN);
    }

    private function getAdminUrl(string $path, array $params = []): string
    {
        return $this->urlBuilder->getUrl($path, $params);
    }
}
