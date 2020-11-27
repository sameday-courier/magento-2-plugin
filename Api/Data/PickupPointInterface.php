<?php

namespace SamedayCourier\Shipping\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface PickupPointInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the getters in snake case
     */
    const ID = 'id';
    const SAMEDAY_ID = 'sameday_id';
    const SAMEDAY_ALIAS = 'sameday_alias';
    const IS_TESTING = 'is_testing';
    const CITY = 'city';
    const COUNTY = 'county';
    const ADDRESS = 'address';
    const CONTACT_PERSONS = 'contact_persons';
    const IS_DEFAULT = 'is_default';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getSamedayId();

    /**
     * @param int $samedayId
     *
     * @return $this
     */
    public function setSamedayId($samedayId);

    /**
     * @return string
     */
    public function getSamedayAlias();

    /**
     * @param string $samedayAlias
     *
     * @return $this
     */
    public function setSamedayAlias($samedayAlias);

    /**
     * @return bool
     */
    public function getIsTesting();

    /**
     * @param bool $isTesting
     *
     * @return $this
     */
    public function setIsTesting($isTesting);

    /**
     * @return string
     */
    public function getCity();

    /**
     * @param string $city
     *
     * @return $this
     */
    public function setCity($city);

    /**
     * @return string
     */
    public function getCounty();

    /**
     * @param string $county
     *
     * @return $this
     */
    public function setCounty($county);

    /**
     * @return string
     */
    public function getAddress();

    /**
     * @param string $address
     *
     * @return $this
     */
    public function setAddress($address);

    /**
     * @return \Sameday\Objects\PickupPoint\ContactPersonObject[]
     */
    public function getContactPersons();

    /**
     * @param \Sameday\Objects\PickupPoint\ContactPersonObject[] $contactPersons
     *
     * @return $this
     */
    public function setContactPersons($contactPersons);

    /**
     * @return bool
     */
    public function getIsDefault();

    /**
     * @param bool $isDefault
     *
     * @return $this
     */
    public function setIsDefault($isDefault);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \SamedayCourier\Shipping\Api\Data\PickupPointExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \SamedayCourier\Shipping\Api\Data\PickupPointExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(\SamedayCourier\Shipping\Api\Data\PickupPointExtensionInterface $extensionAttributes);
}
