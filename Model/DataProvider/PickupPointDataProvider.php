<?php

namespace SamedayCourier\Shipping\Model\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;

class PickupPointDataProvider extends AbstractDataProvider
{
    public function __construct($name, $primaryFieldName, $requestFieldName, array $meta = [], array $data = [])
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return [];
    }

    /**
     * @param $filter
     * @param $alias
     *
     * @return void
     */
    public function addFilter($filter, $alias = null): void
    {
        // Nothing to do, just override parent
    }
}
