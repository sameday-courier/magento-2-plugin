<?php

namespace SamedayCourier\Shipping\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use SamedayCourier\Shipping\Api\Data\LockerInterface;

class Locker extends AbstractExtensibleObject implements LockerInterface
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
     * @return int
     */
    public function getLockerId()
    {
        return $this->_get(self::LOCKER_ID);
    }

    /**
     * @param int $lockerId
     *
     * @return $this
     */
    public function setLockerId($lockerId)
    {
        return $this->setData(self::LOCKER_ID, $lockerId);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @return string
     */
    public function getCounty()
    {
        return $this->_get(self::COUNTY);
    }

    /**
     * @param string $county
     *
     * @return $this
     */
    public function setCounty($county)
    {
        return $this->setData(self::COUNTY, $county);
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->_get(self::CITY);
    }

    /**
     * @param string $city
     *
     * @return $this
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->_get(self::ADDRESS);
    }

    /**
     * @param string $address
     *
     * @return $this
     */
    public function setAddress($address)
    {
        return $this->setData(self::ADDRESS, $address);
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->_get(self::POSTAL_CODE);
    }

    /**
     * @param string $postalCode
     *
     * @return $this
     */
    public function setPostalCode($postalCode)
    {
        return $this->setData(self::POSTAL_CODE, $postalCode);
    }

    /**
     * @return string
     */
    public function getLat()
    {
        return $this->_get(self::LAT);
    }

    /**
     * @param string $lat
     *
     * @return $this
     */
    public function setLat($lat)
    {
        return $this->setData(self::LAT, $lat);
    }

    /**
     * @return string
     */
    public function getLng()
    {
            return $this->_get(self::LAT);
    }

    /**
     * @param string $lng
     *
     * @return $this
     */
    public function setLng($lng)
    {
        return $this->setData(self::LNG, $lng);
    }

    /**
     * @return bool
     */
    public function getIsTesting()
    {
        return $this->_get(self::IS_TESTING);
    }

    /**
     * @param bool $isTesting
     * @return $this
     */
    public function setIsTesting($isTesting)
    {
        return $this->setData(self::IS_TESTING, $isTesting);
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
    public function setExtensionAttributes(\SamedayCourier\Shipping\Api\Data\LockerExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
