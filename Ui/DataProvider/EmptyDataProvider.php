<?php

namespace SamedayCourier\Shipping\Ui\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;

class EmptyDataProvider extends AbstractDataProvider
{
    /**
     * @return array
     */
    public function getData(): array
    {
        return [];
    }
}
