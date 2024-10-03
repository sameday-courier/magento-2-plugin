<?php

namespace SamedayCourier\Shipping\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use SamedayCourier\Shipping\Api\Data\ServiceInterface;

/**
 * @api
 */
interface ServiceRepositoryInterface
{
    /**
     * @param ServiceInterface $service
     *
     * @return void
     */
    public function save(ServiceInterface $service): void;

    /**
     * Get service by ID.
     *
     * @param int $id
     *
     * @return ServiceInterface
     *
     * @throws NoSuchEntityException
     */
    public function get(int $id): ServiceInterface;

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
    public function getBySamedayId(int $samedayId, bool $isTesting): ServiceInterface;

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
     * Retrieve services which match a specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return ServiceInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria): array;

    /**
     * Retrieve services with testing flag.
     *
     * @param bool $isTesting
     *
     * @return ServiceInterface[]
     */
    public function getListByTesting(bool $isTesting): array;

    /**
     * @param bool $isTesting
     *
     * @return ServiceInterface[]
     */
    public function getAllActive(bool $isTesting): array;

    /**
     * Delete service.
     *
     * @param ServiceInterface $service
     *
     * @return bool
     */
    public function delete(ServiceInterface $service): bool;

    /**
     * Delete service by ID.
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteById(int $id): bool;
}
