<?php

namespace SamedayCourier\Shipping\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface ServiceInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the getters in snake case
     */
    const ID = 'id';
    const SAMEDAY_ID = 'sameday_id';
    const SAMEDAY_NAME = 'sameday_name';
    const IS_TESTING = 'is_testing';
    const NAME = 'name';
    const CODE = 'code';
    const PRICE = 'price';
    const IS_PRICE_FREE = 'is_price_free';
    const PRICE_FREE = 'price_free';
    const STATUS = 'status';
    const USE_ESTIMATED_COST = 'use_estimated_cost';
    const LOCKER_MAX_ITEMS = 'locker_max_items';
    /**#@-*/

    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;
    const STATUS_INTERVAL = 2;

    const SERVICES_WITH_LOCKERS = ['LN'];

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
    public function getSamedayName();

    /**
     * @param string $samedayName
     *
     * @return $this
     */
    public function setSamedayName($samedayName);

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
    public function getName();

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * Get service code
     *
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     *
     * @return $this
     */
    public function setCode($code);

    /**
     * @return float
     */
    public function getPrice();

    /**
     * @param float $price
     *
     * @return $this
     */
    public function setPrice($price);

    /**
     * @return bool
     */
    public function getIsPriceFree();

    /**
     * @param bool $isPriceFree
     *
     * @return $this
     */
    public function setIsPriceFree($isPriceFree);

    /**
     * @return float
     */
    public function getPriceFree();

    /**
     * @param float $priceFree
     *
     * @return $this
     */
    public function setPriceFree($priceFree);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return bool
     */
    public function getUseEstimatedCost();

    /**
     * @param bool $useEstimatedCost
     *
     * @return $this
     */
    public function setUseEstimatedCost($useEstimatedCost);

    /**
     * @return int
     */
    public function getLockerMaxItems();

    /**
     * @param int $lockerMaxItems
     *
     * @return $this
     */
    public function setLockerMaxItems(int $lockerMaxItems);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \SamedayCourier\Shipping\Api\Data\ServiceExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \SamedayCourier\Shipping\Api\Data\ServiceExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(\SamedayCourier\Shipping\Api\Data\ServiceExtensionInterface $extensionAttributes);
}
