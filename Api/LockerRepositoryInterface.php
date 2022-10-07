<?php

namespace SamedayCourier\Shipping\Api;

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
     * @param LockerInterface $locker
     * @return mixed
     */
    public function save(LockerInterface $locker);
}
