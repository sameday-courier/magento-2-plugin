<?php

namespace SamedayCourier\Shipping\Block\Adminhtml\Order;

use Exception;
use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Block\Template;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order\Interceptor as Order;
use Magento\Sales\Model\Order\Payment\Interceptor as Payment;
use SamedayCourier\Shipping\Exception\NotAnOrderMatchedException;
use SamedayCourier\Shipping\Helper\ApiHelper;
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

    public function getHostCountry()
    {
        return $this->storedDataHelper->getHostCountry();
    }

    public function getPickupPoints()
    {
        return $this->storedDataHelper->getPickupPoints()->getItems();
    }

    public function getServices()
    {
        return $this->storedDataHelper->getServices()->getItems();
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

        $samedaycourierLocker = trim($order->getData('samedaycourier_locker'));
        $samedaycourierLockerDetails = null;
        $lockerId = null;
        if ('' !== $samedaycourierLocker) {
            $samedaycourierLocker = $this->json->unserialize($samedaycourierLocker);

            if (is_string($samedaycourierLocker)) {
                $locker = $this->storedDataHelper->getLocker((int) $samedaycourierLocker);
                if (null !== $locker) {
                    $lockerId = $locker->getLockerId();
                    $samedaycourierLockerDetails = sprintf('%s %s', $locker->getName(), $locker->getAddress());
                }
            }

            if (is_array($samedaycourierLocker)) {
                $lockerId = $samedaycourierLocker['lockerId'];
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

        $serviceCode = explode('_', $order->getShippingMethod(), 2)[1];

        $displayLockerFirstMile = $this->storedDataHelper::DISPLAY_HTML_ELEM['hide'];
        if ($this->isServiceEligibleToLockerFirstMile($serviceCode)) {
            $displayLockerFirstMile = $this->storedDataHelper::DISPLAY_HTML_ELEM['show'];
        }

        $displayLockerDetails = $this->storedDataHelper::DISPLAY_HTML_ELEM['hide'];
        if ($serviceCode === ApiHelper::LOCKER_NEXT_DAY_SERVICE) {
            $displayLockerDetails = $this->storedDataHelper::DISPLAY_HTML_ELEM['show'];
        }

        return [
            'client_reference' => $order->getId(),
            'weight' => $order->getWeight(),
            'repayment' => $repayment,
            'serviceCode' => $serviceCode,
            'serviceTaxCodePDO' => $this->storedDataHelper::SERVICE_OPTIONAL_TAX_PDO,
            'serviceCodeLockerNextDay' => ApiHelper::LOCKER_NEXT_DAY_SERVICE,
            'displayLockerDetails' => $displayLockerDetails,
            'displayLockerFirstMile' => $displayLockerFirstMile,
            'samedaycourier_locker_id' => $lockerId,
            'samedaycourier_locker' => $samedaycourierLockerDetails,
            'country-code' => $this->storedDataHelper->getHostCountry(),
            'api-username' => $this->storedDataHelper->getApiUsername(),
            'changeLockerMethodUrl' => $this->getUrl('samedaycourier_shipping/order/changeLocker', [
                'order_id' => $order->getId()
            ])
        ];
    }

    public function toggleHtmlElement($toShow)
    {
        return $toShow === true
            ? $this->storedDataHelper::DISPLAY_HTML_ELEM['show']
            : $this->storedDataHelper::DISPLAY_HTML_ELEM['hide']
        ;
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
