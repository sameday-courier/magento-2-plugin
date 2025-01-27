<?php

namespace SamedayCourier\Shipping\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface CityInterface extends ExtensibleDataInterface
{
    const ID = 'id';
    const NAME = 'name';
    const SAMEDAY_ID = 'sameday_id';
    const REGION_ID = 'region_id';

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param $id
     *
     * @return self
     */
    public function setId($id): self;

    /**
     * @return mixed
     */
    public function getName();

    /**
     * @param $name
     *
     * @return self
     */
    public function setName($name): self;

    /**
     * @return mixed
     */
    public function getSamedayId();

    /**
     * @param int $samedayId
     *
     * @return self
     */
    public function setSamedayId(int $samedayId): self;

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
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \SamedayCourier\Shipping\Api\Data\CityExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \SamedayCourier\Shipping\Api\Data\CityExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(\SamedayCourier\Shipping\Api\Data\CityExtensionInterface $extensionAttributes);
}
