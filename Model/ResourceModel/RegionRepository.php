<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;
use SamedayCourier\Shipping\Api\RegionRepositoryInterface;

class RegionRepository implements RegionRepositoryInterface
{
    private $regionFactory;

    public function __construct(RegionFactory $regionFactory)
    {
        $this->regionFactory = $regionFactory;
    }

    /**
     * @param string $regionCode
     * @param string $countryCode
     *
     * @return Region
     */
    public function getByCodeAndCountryCode(string $regionCode, string $countryCode): Region
    {
        return $this->regionFactory->create()->loadByCode($regionCode, $countryCode);
    }
}
