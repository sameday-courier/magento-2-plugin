<?php

namespace SamedayCourier\Shipping\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class PickupPoint extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'samedaycourier_shipping_pickuppoint';
    protected $_cacheTag = 'samedaycourier_shipping_pickuppoint';
    protected $_eventPrefix = 'samedaycourier_shipping_pickuppoint';
    protected $_eventObject = 'samedaycourier_shipping_pickuppoint';

    protected function _construct()
    {
        $this->_init(\SamedayCourier\Shipping\Model\ResourceModel\PickupPoint::class);
    }

    /**
     * @inheritdoc
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
