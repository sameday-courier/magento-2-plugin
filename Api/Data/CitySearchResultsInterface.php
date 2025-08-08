<?php

namespace SamedayCourier\Shipping\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface CitySearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return CityInterface[]
     */
    public function getItems() :array;

    /**
     * @api
     * @param CityInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items) :self;
}
