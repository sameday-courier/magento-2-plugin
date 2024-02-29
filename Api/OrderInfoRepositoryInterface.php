<?php

namespace SamedayCourier\Shipping\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use SamedayCourier\Shipping\Api\Data\OrderInfoInterface;

/**
 * @api
 */
interface OrderInfoRepositoryInterface
{
    /**
     * @param int $orderId
     *
     * @return OrderInfoInterface
     *
     * @throws NoSuchEntityException
     */
    public function getByOrderId(int $orderId): OrderInfoInterface;

    /**
     * @param OrderInfoInterface $orderInfo
     *
     * @return mixed
     */
    public function save(OrderInfoInterface $orderInfo);
}
