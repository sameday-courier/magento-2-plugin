<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use SamedayCourier\Shipping\Api\Data\ServiceInterface;

class Service extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('samedaycourier_shipping_service', 'id');
    }
}
