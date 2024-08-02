<?php

namespace SamedayCourier\Shipping\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use SamedayCourier\Shipping\Api\Data\ServiceInterface;
use SamedayCourier\Shipping\Api\Data\ServiceSearchResultsInterface;

/**
 * @api
 */
interface ServiceRepositoryInterface
{
    /**
     * Create or update a service.
     *
     * @param ServiceInterface $service
     *
     * @return ServiceInterface
     */
    public function save(ServiceInterface $service);

    /**
     * Get service by ID.
     *
     * @param int $id
     *
     * @return ServiceInterface
     *
     * @throws NoSuchEntityException
     */
    public function get($id);

    /**
     * Retrieve service by samedayId.
     *
     * @param int $samedayId
     * @param bool $isTesting
     *
     * @return ServiceInterface
     *
     * @throws NoSuchEntityException
     */
    public function getBySamedayId($samedayId, $isTesting);

    /**
     * @param string $code
     * @param bool $isTesting
     *
     * @return ServiceInterface
     *
     * @throws NoSuchEntityException
     */
    public function getBySamedayCode(string $code, bool $isTesting): ServiceInterface;

    /**
     * Retrieve services with testing flag.
     *
     * @param $isTesting
     *
     * @return ServiceInterface[]
     */
    public function getListByTesting($isTesting);

    /**
     * Retrieve services which match a specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return ServiceSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param bool $isTesting
     *
     * @return ServiceSearchResultsInterface
     */
    public function getAllActive($isTesting);

    /**
     * Delete service.
     *
     * @param ServiceInterface $service
     *
     * @return bool
     */
    public function delete(ServiceInterface $service);

    /**
     * Delete service by ID.
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteById($id);
}
