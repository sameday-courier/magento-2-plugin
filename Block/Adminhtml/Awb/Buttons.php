<?php

namespace SamedayCourier\Shipping\Block\Adminhtml\Awb;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Buttons extends Template
{
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getAwbUrl(): string
    {
        return $this->getUrl('samedaycourier_shipping/awb/index');
    }
}
