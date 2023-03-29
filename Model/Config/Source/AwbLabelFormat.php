<?php

namespace SamedayCourier\Shipping\Model\Config\Source;

use Sameday\Objects\Types\AwbPdfType;

class AwbLabelFormat implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array|void
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => AwbPdfType::A4, 'label' => __(AwbPdfType::A4)],
            ['value' => AwbPdfType::A6, 'label' => __(AwbPdfType::A6)],
        ];
    }
}
