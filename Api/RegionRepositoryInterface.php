<?php

namespace SamedayCourier\Shipping\Api;

use Magento\Framework\Api\ExtensibleDataInterface;
use SamedayCourier\Shipping\Api\Data\RegionInterface;

interface RegionRepositoryInterface extends ExtensibleDataInterface
{
    /**
     * @param string $regionCode
     * @param string $countryCode
     *
     * @return RegionInterface
     */
    public function getByCodeAndCountryCode(string $regionCode, string $countryCode): ?RegionInterface;

    /**
     * @param RegionInterface $region
     *
     * @return void
     */
    public function save(RegionInterface $region): void;
}
