<?php

namespace SamedayCourier\Shipping\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface PickupPointSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return PickupPointInterface[]
     */
    public function getItems() :array;

    /**
     * @api
     * @param PickupPointInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items) :self;
}
