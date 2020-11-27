<?php

namespace SamedayCourier\Shipping\Model\ResourceModel\Awb;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SamedayCourier\Shipping\Model\Awb;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'samedaycourier_shipping_awb_collection';
    protected $_eventObject = 'awb_collection';

    protected function _construct()
    {
        $this->_init(Awb::class, \SamedayCourier\Shipping\Model\ResourceModel\Awb::class);
    }
}
