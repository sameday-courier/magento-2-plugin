<?php

namespace SamedayCourier\Shipping\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * @api
 */
class ServicesButton extends Field
{
    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setData('value', __("View list"));
        $element->setData('class', "action-default");
        $element->setData('onclick', "window.location.assign('{$this->getActionUrl()}')");

        return parent::_getElementHtml($element);
    }

    /**
     * @return string
     */
    public function getActionUrl()
    {
        return $this->_urlBuilder->getUrl('samedaycourier_shipping/service/index');
    }
}
