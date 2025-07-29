<?php

namespace SamedayCourier\Shipping\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use SamedayCourier\Shipping\Api\Data\RegionInterface;

class Region extends AbstractExtensibleObject implements RegionInterface
{
    public function getRegionId()
    {
        $this->_get(self::ID);
    }

    public function setRegionId($regionId): RegionInterface
    {
        return $this->setData(self::ID, $regionId);
    }

    public function getCountryId(): string
    {
        return $this->_get(self::COUNTRY_ID);
    }

    public function setCountryId(string $countryId): RegionInterface
    {
        return $this->setData(self::COUNTRY_ID, $countryId);
    }

    public function getCode(): string
    {
        return $this->_get(self::CODE);
    }

    public function setCode($code): RegionInterface
    {
        return $this->setData(self::CODE, $code);
    }

    public function getName(): string
    {
        return $this->_get(self::NAME);
    }

    public function setName($name): RegionInterface
    {
        return $this->setData(self::NAME, $name);
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
    public function setExtensionAttributes(\SamedayCourier\Shipping\Api\Data\RegionExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
