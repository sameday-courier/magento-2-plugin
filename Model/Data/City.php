<?php

namespace SamedayCourier\Shipping\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use SamedayCourier\Shipping\Api\Data\CityInterface;

class City extends AbstractExtensibleObject implements CityInterface
{
    /**
     * @return mixed|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * @param $id
     *
     * @return CityInterface
     */
    public function setId($id): CityInterface
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @return mixed|null
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * @param $name
     *
     * @return CityInterface
     */
    public function setName($name): CityInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @return mixed|null
     */
    public function getSamedayId()
    {
        return $this->_get(self::SAMEDAY_ID);
    }

    /**
     * @param $samedayId
     *
     * @return CityInterface
     */
    public function setSamedayId($samedayId): CityInterface
    {
        return $this->setData(self::SAMEDAY_ID, $samedayId);
    }

    /**
     * @return mixed|null
     */
    public function getRegionId()
    {
        return $this->_get(self::REGION_ID);
    }

    /**
     * @param $regionId
     *
     * @return CityInterface
     */
    public function setRegionId($regionId): CityInterface
    {
        return $this->setData(self::REGION_ID, $regionId);
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
    public function setExtensionAttributes(\SamedayCourier\Shipping\Api\Data\CityExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
