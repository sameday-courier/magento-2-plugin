<?php

namespace SamedayCourier\Shipping\Block\Adminhtml\Order;

use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Block\Template;

class SamedayModal extends Template
{
    /**
     * Constructor
     *
     * @param Context  $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getInfo()
    {
        //Your block cod
        return __('This is some data');
    }

    public function getFormUrl()
    {
        $orderId = false;
        if($this->hasData('order')){
            $orderId = $this->getOrder()->getId();
        }

        return $this->getUrl('samedaycourier_shipping/order/order',[
            'order_id' => $orderId
        ]);
    }
}
