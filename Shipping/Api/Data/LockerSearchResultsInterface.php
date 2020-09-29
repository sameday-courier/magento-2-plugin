<?php

namespace SamedayCourier\Shipping\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface LockerSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return LockerInterface[]
     */
    public function getItems();

    /**
     * @api
     * @param LockerInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items);
}
