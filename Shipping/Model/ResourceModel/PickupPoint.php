<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PickupPoint extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('samedaycourier_shipping_pickuppoint', 'id');
    }
}
