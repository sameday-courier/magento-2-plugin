<?php

namespace SamedayCourier\Shipping\Api;

use Magento\Directory\Model\Region;
use Magento\Framework\Api\ExtensibleDataInterface;

interface RegionRepositoryInterface extends ExtensibleDataInterface
{
    /**
     * @param string $regionCode
     * @param string $countryCode
     *
     * @return Region
     */
    public function getByCodeAndCountryCode(string $regionCode, string $countryCode): Region;
}
