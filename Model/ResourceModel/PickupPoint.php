<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use SamedayCourier\Shipping\Api\Data\PickupPointInterface;

class PickupPoint extends AbstractDb
{
    protected $_serializableFields = [
        PickupPointInterface::CONTACT_PERSONS => [[], []]
    ];

    protected function _construct()
    {
        $this->_init('samedaycourier_shipping_pickuppoint', 'id');
    }
}
