<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Region extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('directory_country_region', 'region_id');
    }
}
