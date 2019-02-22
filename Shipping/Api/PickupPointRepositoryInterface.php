<?php

namespace SamedayCourier\Shipping\Api;

use SamedayCourier\Shipping\Api\Data\PickupPointInterface;
use SamedayCourier\Shipping\Api\Data\PickupPointSearchResultsInterface;

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
     * @return PickupPointInterface
     */
    public function save(PickupPointInterface $pickupPoint);

    /**
     * Get pickup point by ID.
     *
     * @param int $id
     *
     * @return PickupPointInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id);

    /**
     * Retrieve pickup point by samedayId.
     *
     * @param int $samedayId
     * @param bool $isTesting
     *
     * @return PickupPointInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBySamedayId($samedayId, $isTesting);

    /**
     * Retrieve pickup points with testing flag.
     *
     * @param $isTesting
     *
     * @return PickupPointInterface[]
     */
    public function getListByTesting($isTesting);

    /**
     * Retrieve pickup points which match a specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return PickupPointSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete pickup point.
     *
     * @param PickupPointInterface $pickupPoint
     *
     * @return bool
     */
    public function delete(PickupPointInterface $pickupPoint);

    /**
     * Delete pickup point by ID.
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteById($id);
}
