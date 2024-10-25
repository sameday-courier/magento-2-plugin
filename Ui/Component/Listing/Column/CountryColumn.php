<?php

namespace SamedayCourier\Shipping\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class CountryColumn extends Column implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 187, 'label' => 'Romania'],
//            ['value' => 34, 'label' => 'Bulgaria'],
//            ['value' => 237, 'label' => 'Hungary'],
        ];
    }
}
