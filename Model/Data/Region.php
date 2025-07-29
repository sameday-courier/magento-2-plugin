<?php

namespace SamedayCourier\Shipping\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use SamedayCourier\Shipping\Api\Data\RegionInterface;

class Region extends AbstractExtensibleObject implements RegionInterface
{
    /**
     * @return string
     */
    public function getRegionId(): string
    {
        return $this->_get(self::REGION_ID);
    }

    /**
     * @param $regionId
     *
     * @return RegionInterface
     */
    public function setRegionId($regionId): RegionInterface
    {
        return $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * @return string
     */
    public function getCountryId(): string
    {
        return $this->_get(self::COUNTRY_ID);
    }

    /**
     * @param $countryId
     *
     * @return RegionInterface
     */
    public function setCountryId($countryId): RegionInterface
    {
        return $this->setData(self::COUNTRY_ID, $countryId);
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->_get(self::CODE);
    }

    /**
     * @param $code
     *
     * @return RegionInterface
     */
    public function setCode($code): RegionInterface
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * @return string
     */
    public function getDefaultName(): string
    {
        return $this->_get(self::DEFAULT_NAME);
    }

    /**
     * @param $defaultName
     *
     * @return RegionInterface
     */
    public function setDefaultName($defaultName): RegionInterface
    {
        return $this->setData(self::DEFAULT_NAME, $defaultName);
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
