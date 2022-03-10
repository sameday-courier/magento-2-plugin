<?php

namespace SamedayCourier\Shipping\Block\Adminhtml\Order;

use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Block\Template;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Interceptor as Order;
use Magento\Sales\Model\Order\Payment\Interceptor as Payment;
use SamedayCourier\Shipping\Exception\NotAnOrderMatchedException;
use SamedayCourier\Shipping\Helper\StoredDataHelper;

class SamedayModal extends Template
{
    private $storedDataHelper;

    /**
     * @param Context $context
     * @param StoredDataHelper $storedDataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoredDataHelper $storedDataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->storedDataHelper = $storedDataHelper;
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
     * @throws NotAnOrderMatchedException
     * @throws LocalizedException
     */
    public function getOrderDetails(): array
    {
        if (!$this->hasData('order')) {
            throw new NotAnOrderMatchedException();
        }

        /** @var Order $order */
        $order = $this->getOrder();

        $repayment = 0;
        $payment = $order->getPayment();
        if ($payment instanceof Payment) {
            $paymentCode = $payment->getMethodInstance()->getCode();

            if (null === $paymentCode || $this->storedDataHelper::CASH_ON_DELIVERY_CODE === $paymentCode) {
                $repayment = $order->getGrandTotal();
            }
        }

        return [
            'client_reference' => $order->getId(),
            'weight' => $order->getWeight(),
            'repayment' => $repayment,
            'serviceId' => explode('_', $order->getShippingMethod(), 2)[1],
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
