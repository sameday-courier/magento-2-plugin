<?php

namespace SamedayCourier\Shipping\Model\ResourceModel\Region;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SamedayCourier\Shipping\Model\Region;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'region_id';
    protected $_eventPrefix = 'samedaycourier_shipping_region_collection';
    protected $_eventObject = 'region_collection';

    protected function _construct()
    {
        $this->_init(Region::class, \SamedayCourier\Shipping\Model\ResourceModel\Region::class);
    }
}
