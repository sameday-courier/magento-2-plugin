<?php

namespace SamedayCourier\Shipping\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class EasyboxMethods implements OptionSourceInterface
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

