<?php

namespace SamedayCourier\Shipping\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use SamedayCourier\Shipping\Api\Data\PickupPointInterface;

/**
 * @api
 */
interface PickupPointRepositoryInterface
{
    /**
     * Create or update a pickup point.
     *
     * @param PickupPointInterface $pickupPoint
     *
     * @return void
     */
    public function save(PickupPointInterface $pickupPoint): void;

    /**
     * Get pickup point by ID.
     *
     * @param int $id
     *
     * @return PickupPointInterface
     *
     * @throws NoSuchEntityException
     */
    public function get(int $id): PickupPointInterface;

    /**
     * Retrieve pickup point by samedayId.
     *
     * @param int $samedayId
     * @param bool $isTesting
     *
     * @return PickupPointInterface
     *
     * @throws NoSuchEntityException
     */
    public function getBySamedayId(int $samedayId, bool $isTesting): PickupPointInterface;

    /**
     * Retrieve pickup points with testing flag.
     *
     * @param bool $isTesting
     *
     * @return PickupPointInterface[]
     */
    public function getListByTesting(bool $isTesting): array;

    /**
     * Retrieve pickup points which match a specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return PickupPointInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria): array;

    /**
     * Get the default pickup point
     *
     * @return PickupPointInterface
     */
    public function getDefaultPickupPoint(): PickupPointInterface;

    /**
     * Delete pickup point.
     *
     * @param PickupPointInterface $pickupPoint
     *
     * @return bool
     */
    public function delete(PickupPointInterface $pickupPoint): bool;

    /**
     * Delete pickup point by ID.
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteById(int $id): bool;
}
