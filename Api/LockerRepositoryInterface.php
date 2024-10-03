<?php

namespace SamedayCourier\Shipping\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use SamedayCourier\Shipping\Api\Data\LockerInterface;

/**
 * @api
 */
interface LockerRepositoryInterface
{
    /**
     * Get locker by locker ID.
     *
     * @param int $id
     *
     * @return LockerInterface
     *
     * @throws NoSuchEntityException
     */
    public function getLockerBySamedayId(int $id): LockerInterface;

    /**
     * @param int $id
     * @return LockerInterface
     */
    public function getLockerById(int $id): LockerInterface;

    /**
     * Retrieve pickup points which match a specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return LockerInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria): array;

    /**
     * @param LockerInterface $locker
     *
     * @return void
     */
    public function save(LockerInterface $locker): void;
}
