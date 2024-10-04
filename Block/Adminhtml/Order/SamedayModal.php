<?php

namespace SamedayCourier\Shipping\Block\Adminhtml\Order;

use Exception;
use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Block\Template;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order\Interceptor as Order;
use Magento\Sales\Model\Order\Payment\Interceptor as Payment;
use SamedayCourier\Shipping\Exception\NotAnOrderMatchedException;
use SamedayCourier\Shipping\Helper\GeneralHelper;
use SamedayCourier\Shipping\Helper\StoredDataHelper;
use SamedayCourier\Shipping\Model\Data\Service;

class SamedayModal extends Template
{
    private $storedDataHelper;

    private $json;

    /**
     * @param Context $context
     * @param StoredDataHelper $storedDataHelper
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoredDataHelper $storedDataHelper,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->storedDataHelper = $storedDataHelper;
        $this->json = $json;
    }

    /**
     * @return string
     */
    public function getHostCountry(): string
    {
        return $this->storedDataHelper->getHostCountry();
    }

    public function getPickupPoints()
    {
        return $this->storedDataHelper->getPickupPoints();
    }

    public function getServices()
    {
        return $this->storedDataHelper->getServices();
    }

    private function filterServiceByCode($code)
    {
        $filteredService = null;
        foreach ($this->getServices() as $service) {
            if ($service->getCode() === $code) {
                $filteredService = $service;
            }

            if (null !== $filteredService) {
                break;
            }
        }

        return $filteredService;
    }

    /**
     * @return array|null
     */
    public function getOrderDetails(): ?array
    {
        if (!$this->hasData('order')) {
            return null;
        }

        /** @var Order $order */
        $order = $this->getOrder();

        $samedaycourierLocker = trim((string) $order->getData('samedaycourier_locker'));
        $samedaycourierLockerDetails = null;
        if ('' !== $samedaycourierLocker) {
            $samedaycourierLocker = $this->json->unserialize($samedaycourierLocker);

            if (is_string($samedaycourierLocker)) {
                $locker = $this->storedDataHelper->getLocker((int) $samedaycourierLocker);
                if (null !== $locker) {
                    $samedaycourierLockerDetails = sprintf('%s %s', $locker->getName(), $locker->getAddress());
                }
            }

            if (is_array($samedaycourierLocker)) {
                $samedaycourierLockerDetails = sprintf('%s %s', $samedaycourierLocker['name'], $samedaycourierLocker['address']);
            }
        }

        $repayment = 0;
        $payment = $order->getPayment();
        if ($payment instanceof Payment) {
            $paymentCode = null;
            try {
                if (null !== $payment->getMethodInstance()) {
                    $paymentCode = $payment->getMethodInstance()->getCode();
                }
            } catch (Exception $exception) { return null; }

            if (null === $paymentCode || $this->storedDataHelper::CASH_ON_DELIVERY_CODE === $paymentCode) {
                $repayment = $order->getGrandTotal();
            }
        }

        $serviceCode = explode('_', $order->getShippingMethod(), 2)[1] ?? null;

        $displayLockerFirstMile = $this->storedDataHelper::DISPLAY_HTML_ELEM['hide'];
        if ($this->isServiceEligibleToLockerFirstMile($serviceCode)) {
            $displayLockerFirstMile = $this->storedDataHelper::DISPLAY_HTML_ELEM['show'];
        }

        $displayLockerDetails = $this->storedDataHelper::DISPLAY_HTML_ELEM['hide'];
        if ($this->storedDataHelper->isEligibleToLocker($serviceCode)) {
            $displayLockerDetails = $this->storedDataHelper::DISPLAY_HTML_ELEM['show'];

            if (isset($samedaycourierLocker['oohType'])) {
                $serviceCode = GeneralHelper::OOH_SERVICES[$samedaycourierLocker['oohType']];
            }
        }

        $city = null;
        if (null !== $shippingAddress = $order->getShippingAddress()) {
            $city = $shippingAddress->getCity();
        }

        $orderCurrency = $order->getOrderCurrencyCode();
        $destCurrency = $this->storedDataHelper->buildDestCurrency($shippingAddress->getCountryId());

        $currencyWarningMessage = null;
        if ($destCurrency !== $orderCurrency
            && $repayment > 0
        ) {
            $currencyWarningMessage = sprintf(
                "Be aware that the intended currency is %s but the Repayment value is expressed in %s.
                Please consider a conversion !!",
                $destCurrency,
                $orderCurrency
            );
        }

        return [
            'client_reference' => $order->getId(),
            'weight' => $order->getWeight() > 0 ? $order->getWeight() : 1.0,
            'repayment' => $repayment,
            'currency' => $orderCurrency,
            'currencyWarningMessage' => $currencyWarningMessage,
            'serviceCode' => $serviceCode,
            'serviceTaxCodePDO' => $this->storedDataHelper::SERVICE_OPTIONAL_TAX_PDO,
            'displayLockerDetails' => $displayLockerDetails,
            'displayLockerFirstMile' => $displayLockerFirstMile,
            'samedaycourier_locker' => $samedaycourierLockerDetails,
            'country-code' => $shippingAddress->getCountryId(),
            'api-username' => $this->storedDataHelper->getApiUsername(),
            'city' => $city,
            'changeLockerMethodUrl' => $this->getUrl('samedaycourier_shipping/order/changeLocker', [
                'order_id' => $order->getId()
            ])
        ];
    }

    public function toggleHtmlElement($toShow): string
    {
        return $toShow === true
            ? $this->storedDataHelper::DISPLAY_HTML_ELEM['show']
            : $this->storedDataHelper::DISPLAY_HTML_ELEM['hide']
        ;
    }

    public function isEligibleToLocker(string $serviceCode): bool
    {
        return $this->storedDataHelper->isEligibleToLocker($serviceCode);
    }

    public function isServiceEligibleToLockerFirstMile($serviceCode): bool
    {
        /** @var Service $defaultService */
        $defaultService = $this->filterServiceByCode($serviceCode);
        if (null !== $defaultService) {
            $defaultServiceTaxes = $this->storedDataHelper->deserializeServiceOptionalTaxes($defaultService->getServiceOptionalTaxes());
            if (null !== $defaultServiceTaxes) {
                foreach ($defaultServiceTaxes as $tax) {
                    if ($tax['code'] === $this->storedDataHelper::SERVICE_OPTIONAL_TAX_PDO) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function getRouteAddAwb()
    {
        if(!$this->hasData('order')){
            throw new NotAnOrderMatchedException();
        }

        $orderId = $this->getOrder()->getId();

        return $this->getUrl('samedaycourier_shipping/order/addawb', [
            'order_id' => $orderId
        ]);
    }

    public function getRouteAddParcel()
    {
        if(!$this->hasData('order')){
            throw new NotAnOrderMatchedException();
        }

        $orderId = $this->getOrder()->getId();
        return $this->getUrl('samedaycourier_shipping/order/addparcel', [
            'order_id' => $orderId
        ]);
    }

    public function getRouteRemoveAwb()
    {
        return $this->getUrl('samedaycourier_shipping/order/removeawb');
    }

    public function getRouteAwbAsPdf()
    {
        if(!$this->hasData('order')){
            throw new NotAnOrderMatchedException();
        }

        $orderId = $this->getOrder()->getId();

        return $this->getUrl('samedaycourier_shipping/order/showpdf', [
            'order_id' => $orderId
        ]);
    }
}
