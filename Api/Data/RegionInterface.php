<?php

namespace SamedayCourier\Shipping\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface RegionInterface extends ExtensibleDataInterface
{
    public const REGION_ID = 'region_id';
    public const COUNTRY_ID = 'country_id';
    public const CODE = 'code';
    public const DEFAULT_NAME = 'default_name';

    /**
     * @return string
     */
    public function getRegionId(): string;

    /**
     * @param $regionId
     *
     * @return self
     */
    public function setRegionId($regionId): self;

    /**
     * @return string
     */
    public function getCountryId(): string;

    /**
     * @param $countryId
     *
     * @return self
     */
    public function setCountryId($countryId): self;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param $code
     *
     * @return self
     */
    public function setCode($code): self;

    /**
     * @return string
     */
    public function getDefaultName(): string;

    /**
     * @param $defaultName
     *
     * @return self
     */
    public function setDefaultName($defaultName): self;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \SamedayCourier\Shipping\Api\Data\RegionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \SamedayCourier\Shipping\Api\Data\RegionExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(\SamedayCourier\Shipping\Api\Data\RegionExtensionInterface $extensionAttributes);
}
