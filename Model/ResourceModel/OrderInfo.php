<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OrderInfo extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('samedaycourier_order_info', 'id');
    }
}
