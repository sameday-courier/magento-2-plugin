<?php

namespace SamedayCourier\Shipping\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use SamedayCourier\Shipping\Api\Data\ServiceInterface;

class Service extends AbstractExtensibleObject implements ServiceInterface
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
    public function getSamedayId()
    {
        return $this->_get(self::SAMEDAY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSamedayId($samedayId)
    {
        return $this->setData(self::SAMEDAY_ID, $samedayId);
    }

    /**
     * @inheritdoc
     */
    public function getSamedayName()
    {
        return $this->_get(self::SAMEDAY_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setSamedayName($samedayName)
    {
        return $this->setData(self::SAMEDAY_NAME, $samedayName);
    }

    /**
     * @inheritdoc
     */
    public function getIsTesting()
    {
        return $this->_get(self::IS_TESTING);
    }

    /**
     * @inheritdoc
     */
    public function setIsTesting($isTesting)
    {
        return $this->setData(self::IS_TESTING, $isTesting);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getPrice()
    {
        return $this->_get(self::PRICE);
    }

    /**
     * @inheritdoc
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * @inheritdoc
     */
    public function getIsPriceFree()
    {
        return $this->_get(self::IS_PRICE_FREE);
    }

    /**
     * @inheritdoc
     */
    public function setIsPriceFree($isPriceFree)
    {
        return $this->setData(self::IS_PRICE_FREE, $isPriceFree);
    }

    /**
     * @inheritdoc
     */
    public function getPriceFree()
    {
        return $this->_get(self::PRICE_FREE);
    }

    /**
     * @inheritdoc
     */
    public function setPriceFree($priceFree)
    {
        return $this->setData(self::PRICE_FREE, $priceFree);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritdoc
     */
    public function getWorkingDays()
    {
        return $this->_get(self::WORKING_DAYS);
    }

    /**
     * @inheritdoc
     */
    public function setWorkingDays($workingDays)
    {
        return $this->setData(self::WORKING_DAYS, $workingDays);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(\SamedayCourier\Shipping\Api\Data\ServiceExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
