<?php

namespace SamedayCourier\Shipping\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface RegionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return RegionInterface[]
     */
    public function getItems(): array;

    /**
     * @api
     * @param RegionInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items): self;
}
