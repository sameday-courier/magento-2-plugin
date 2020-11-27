<?php

namespace SamedayCourier\Shipping\Api;

use SamedayCourier\Shipping\Api\Data\AwbInterface;

/**
 * @api
 */
interface AwbRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return AwbInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByOrderId($id);

    /**
     * @param AwbInterface $locker
     *
     * @return mixed
     */
    public function save(AwbInterface $locker);
}
