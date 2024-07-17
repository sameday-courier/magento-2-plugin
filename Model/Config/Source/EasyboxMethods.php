<?php

namespace SamedayCourier\Shipping\Model\Config\Source;

class EasyboxMethods implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array|void
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => "1", 'label' => __("Interactive map")],
            ['value' => "0", 'label' => __("Drop-down list")],
        ];
    }
}

