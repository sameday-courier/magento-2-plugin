<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Awb extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('samedaycourier_shipping_awb', 'id');
    }
}
