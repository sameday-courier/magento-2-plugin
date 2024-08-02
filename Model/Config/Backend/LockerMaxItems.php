<?php

namespace SamedayCourier\Shipping\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use SamedayCourier\Shipping\Helper\StoredDataHelper;

class LockerMaxItems extends Value
{
    protected function _afterLoad()
    {
        if ($this->getValue() === null) {
            $this->setValue(StoredDataHelper::DEFAULT_VALUE_LOCKER_MAX_ITEMS);
        }
    }
}
