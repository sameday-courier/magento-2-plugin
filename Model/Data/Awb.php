<?php

namespace SamedayCourier\Shipping\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use SamedayCourier\Shipping\Api\Data\AwbInterface;

class Awb extends AbstractExtensibleObject implements AwbInterface
{
    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getOrderId()
    {
        return $this->_get(self::ORDER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritdoc
     */
    public function getAwbNumber()
    {
        return $this->_get(self::AWB_NUMBER);
    }

    /**
     * @inheritdoc
     */
    public function setAwbNumber($awbNumber)
    {
        return $this->setData(self::AWB_NUMBER, $awbNumber);
    }

    /**
     * @inheritdoc
     */
    public function getParcels()
    {
        return $this->_get(self::PARCELS);
    }

    /**
     * @inheritdoc
     */
    public function setParcels($parcels)
    {
        return $this->setData(self::PARCELS, $parcels);
    }

    /**
     * @inheritdoc
     */
    public function getAwbCost()
    {
        return $this->_get(self::AWB_COST);
    }

    /**
     * @inheritdoc
     */
    public function setAwbCost($awbCost)
    {
        return $this->setData(self::AWB_COST, $awbCost);
    }
}
