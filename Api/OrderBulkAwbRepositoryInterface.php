<?php

namespace SamedayCourier\Shipping\Api;

use SamedayCourier\Shipping\Api\Data\OrderBulkAwbInterface;

interface OrderBulkAwbRepositoryInterface
{
    /**
     * @param int $orderId
     * @return OrderBulkAwbInterface|null
     */
    public function getByOrderId(int $orderId);

    /**
     * @param int[] $orderIds
     * @return array<int, OrderBulkAwbInterface>
     */
    public function getByOrderIds(array $orderIds): array;

    /**
     * @param int $orderId
     * @param int $status
     * @param array $payload
     * @return void
     */
    public function updateFeedback(int $orderId, int $status, array $payload): void;

    /**
     * @param int $orderId
     * @return void
     */
    public function deleteByOrderId(int $orderId): void;

    /**
     * Clear feedback rows that have no generated AWB.
     *
     * @return int[] Cleared order IDs
     */
    public function clearWithoutGeneratedAwb(): array;
}
