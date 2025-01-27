<?php

namespace SamedayCourier\Shipping\Model\ResourceModel\City;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SamedayCourier\Shipping\Model\City as CityModel;
use SamedayCourier\Shipping\Model\ResourceModel\City as CityResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(
            CityModel::class,
            CityResource::class
        );
    }
}
