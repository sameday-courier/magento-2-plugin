<?php

namespace SamedayCourier\Shipping\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface LockerInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the getters in snake case
     */
    const ID = 'id';
    const LOCKER_ID = 'locker_id';
    const NAME = 'name';
    const COUNTY = 'county';
    const CITY = 'city';
    const ADDRESS = 'address';
    const POSTAL_CODE = 'postal_code';
    const LAT = 'lat';
    const LNG = 'lng';
    const IS_TESTING = 'is_testing';

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
    public function getLockerId();

    /**
     * @param int $lockerId
     * @return $this
     */
    public function setLockerId($lockerId);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

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
    public function getAddress();

    /**
     * @param string $address
     *
     * @return $this
     */
    public function setAddress($address);

    /**
     * @return string
     */
    public function getPostalCode();

    /**
     * @param string $postalCode
     *
     * @return $this
     */
    public function setPostalCode($postalCode);

    /**
     * @return string
     */
    public function getLat();

    /**
     * @param string $lat
     *
     * @return $this
     */
    public function setLat($lat);

    /**
     * @return string
     */
    public function getLng();

    /**
     * @param string $lng
     *
     * @return $this
     */
    public function setLng($lng);

    /**
     * @return bool
     */
    public function getIsTesting();

    /**
     * @param bool $isTesting
     * @return $this
     */
    public function setIsTesting($isTesting);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \SamedayCourier\Shipping\Api\Data\LockerExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \SamedayCourier\Shipping\Api\Data\LockerExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(\SamedayCourier\Shipping\Api\Data\LockerExtensionInterface $extensionAttributes);
}
