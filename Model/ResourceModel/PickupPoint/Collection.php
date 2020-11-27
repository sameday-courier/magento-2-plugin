<?php

namespace SamedayCourier\Shipping\Model\ResourceModel\PickupPoint;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SamedayCourier\Shipping\Model\PickupPoint;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'samedaycourier_shipping_pickuppoint_collection';
    protected $_eventObject = 'pickuppoint_collection';

    protected function _construct()
    {
        $this->_init(PickupPoint::class, \SamedayCourier\Shipping\Model\ResourceModel\PickupPoint::class);
    }
}
