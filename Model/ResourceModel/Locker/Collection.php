<?php

namespace SamedayCourier\Shipping\Model\ResourceModel\Locker;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SamedayCourier\Shipping\Model\Locker;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'samedaycourier_shipping_locker_collection';
    protected $_eventObject = 'locker_collection';

    protected function _construct()
    {
        $this->_init(Locker::class, \SamedayCourier\Shipping\Model\ResourceModel\Locker::class);
    }
}
