<?php

namespace SamedayCourier\Shipping\Api;

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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByLockerId($id);

    /**
     * @param LockerInterface $locker
     * @return mixed
     */
    public function save(LockerInterface $locker);
}
