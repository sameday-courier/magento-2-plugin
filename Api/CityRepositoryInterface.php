<?php

namespace SamedayCourier\Shipping\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use SamedayCourier\Shipping\Api\Data\CityInterface;

interface CityRepositoryInterface
{
    public function save(CityInterface $city);
    public function get(int $id): CityInterface;
    public function getBySamedayId(int $samedayId): CityInterface;
    public function delete(CityInterface $city);
    public function getList(SearchCriteriaInterface $searchCriteria): array;
}
