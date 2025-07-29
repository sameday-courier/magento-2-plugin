<?php

namespace SamedayCourier\Shipping\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface RegionInterface extends ExtensibleDataInterface
{
    public const ID = 'region_id';
    public const COUNTRY_ID = 'country_id';
    public const CODE = 'code';
    public const NAME = 'default_name';

    /**
     * @return mixed
     */
    public function getRegionId();

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
     * @param string $countryId
     *
     * @return self
     */
    public function setCountryId(string $countryId): self;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param string $code
     *
     * @return self
     */
    public function setCode(string $code): self;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param $name
     *
     * @return self
     */
    public function setName($name): self;

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
