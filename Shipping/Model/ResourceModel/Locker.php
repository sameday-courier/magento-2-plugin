<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Locker extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('samedaycourier_shipping_locker', 'id');
    }
}
