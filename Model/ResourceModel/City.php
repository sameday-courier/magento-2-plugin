<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class City extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('samedaycourier_shipping_city', 'id');
    }
}
