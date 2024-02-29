<?php

namespace SamedayCourier\Shipping\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ImportData extends Field
{
    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $element->setData('value', __("Import data"));
        $element->setData('class', "action-default");
        $element->setData('data-action', $this->getActionUrl());

        return parent::_getElementHtml($element);
    }

    /**
     * @return string
     */
    public function getActionUrl(): string
    {
        return $this->_urlBuilder->getUrl('samedaycourier_shipping/importer/index');
    }
}
