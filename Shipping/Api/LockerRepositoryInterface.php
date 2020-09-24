<?php

namespace SamedayCourier\Shipping\Api;

use SamedayCourier\Shipping\Api\Data\LockerInterface;

/**
 * @api
 */
interface LockerRepositoryInterface
{
    /**
     * Get locker by ID.
     *
     * @param int $id
     *
     * @return LockerInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id);
}
