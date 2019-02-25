<?php

namespace SamedayCourier\Shipping\Model\ResourceModel\Service;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SamedayCourier\Shipping\Model\Service;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'samedaycourier_shipping_service_collection';
    protected $_eventObject = 'service_collection';

    protected function _construct()
    {
        $this->_init(Service::class, \SamedayCourier\Shipping\Model\ResourceModel\Service::class);
    }
}
