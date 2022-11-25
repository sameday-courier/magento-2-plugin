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

        $displayLockerDetails = 'none';
        $serviceCode = explode('_', $order->getShippingMethod(), 2)[1];
        if ($serviceCode === ApiHelper::LOCKER_NEXT_DAY_SERVICE) {
            $displayLockerDetails = 'block';
        }

        return [
            'client_reference' => $order->getId(),
            'weight' => $order->getWeight(),
            'repayment' => $repayment,
            'serviceCode' => $serviceCode,
            'serviceCodeLockerNextDay' => ApiHelper::LOCKER_NEXT_DAY_SERVICE,
            'displayLockerDetails' => $displayLockerDetails,
            'samedaycourier_locker_id' => $lockerId,
            'samedaycourier_locker' => $samedaycourierLockerDetails,
            'country-code' => $this->storedDataHelper->getHostCountry(),
            'api-username' => $this->storedDataHelper->getApiUsername(),
            'changeLockerMethodUrl' => $this->getUrl('samedaycourier_shipping/order/changeLocker', [
                'order_id' => $order->getId()
            ])
        ];
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
