<?php

namespace SamedayCourier\Shipping\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface ServiceSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return ServiceInterface[]
     */
    public function getItems();

    /**
     * @api
     * @param ServiceInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items);
}
