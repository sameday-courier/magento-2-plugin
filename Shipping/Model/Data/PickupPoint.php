<?php

namespace SamedayCourier\Shipping\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Sameday\Objects\PickupPoint\ContactPersonObject;
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
     * @param int $id
     *
     * @return $this
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
     * @param int $samedayId
     *
     * @return $this
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
     * @param string $samedayAlias
     *
     * @return $this
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
     * @param bool $isTesting
     *
     * @return $this
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
     * @param string $city
     *
     * @return $this
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
     * @param string $county
     *
     * @return $this
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
     * @param string $address
     *
     * @return $this
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
     * @param ContactPersonObject[] $contactPersons
     *
     * @return $this
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
     * @param bool $isDefault
     *
     * @return $this
     */
    public function setIsDefault($isDefault)
    {
        return $this->setData(self::IS_DEFAULT, $isDefault);
    }

    /**
     * @inheritdoc
     *
     * @return \SamedayCourier\Shipping\Api\Data\PickupPointExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     *
     * @param \SamedayCourier\Shipping\Api\Data\PickupPointExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(\SamedayCourier\Shipping\Api\Data\PickupPointExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
