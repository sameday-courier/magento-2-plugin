<?php

namespace SamedayCourier\Shipping\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use SamedayCourier\Shipping\Api\Data\PickupPointExtensionInterface;
use SamedayCourier\Shipping\Api\Data\PickupPointInterface;

class PickupPoint extends AbstractExtensibleObject implements PickupPointInterface
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
    public function getSamedayAlias()
    {
        return $this->_get(self::SAMEDAY_ALIAS);
    }

    /**
     * @inheritdoc
     */
    public function setSamedayAlias($samedayAlias)
    {
        return $this->setData(self::SAMEDAY_ALIAS, $samedayAlias);
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
    public function getCity()
    {
        return $this->_get(self::CITY);
    }

    /**
     * @inheritdoc
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * @inheritdoc
     */
    public function getCounty()
    {
        return $this->_get(self::COUNTY);
    }

    /**
     * @inheritdoc
     */
    public function setCounty($county)
    {
        return $this->setData(self::COUNTY, $county);
    }

    /**
     * @inheritdoc
     */
    public function getAddress()
    {
        return $this->_get(self::ADDRESS);
    }

    /**
     * @inheritdoc
     */
    public function setAddress($address)
    {
        return $this->setData(self::ADDRESS, $address);
    }

    /**
     * @inheritdoc
     */
    public function getContactPersons()
    {
        return $this->_get(self::CONTACT_PERSONS);
    }

    /**
     * @inheritdoc
     */
    public function setContactPersons($contactPersons)
    {
        return $this->setData(self::CONTACT_PERSONS, $contactPersons ? $contactPersons : []);
    }

    /**
     * @inheritdoc
     */
    public function getIsDefault()
    {
        return $this->_get(self::IS_DEFAULT);
    }

    /**
     * @inheritdoc
     */
    public function setIsDefault($isDefault)
    {
        return $this->setData(self::IS_DEFAULT, $isDefault);
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
    public function setExtensionAttributes(PickupPointExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
