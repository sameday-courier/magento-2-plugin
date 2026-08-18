<?php

namespace SamedayCourier\Shipping\Model\ResourceModel\OrderBulkAwb;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SamedayCourier\Shipping\Model\OrderBulkAwb;
use SamedayCourier\Shipping\Model\ResourceModel\OrderBulkAwb as OrderBulkAwbResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(OrderBulkAwb::class, OrderBulkAwbResource::class);
    }
}
