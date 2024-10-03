<?php

namespace SamedayCourier\Shipping\Helper;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SearchResultHelper
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param AbstractCollection $collection
     *
     * @return AbstractCollection
     */
    public function buildSearchCollection(
        SearchCriteriaInterface $searchCriteria,
        AbstractCollection $collection
    ): AbstractCollection
    {
        // Add filters from root filter group to the collection.
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $fields = null;
            $conditions = null;
            foreach ($group->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }

            if (null !== $fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }

        $sortOrders = $searchCriteria->getSortOrders();
        /** @var SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        } else {
            // Set a default sorting order.
            $collection->addOrder('id', 'ASC');
        }

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        return $collection;
    }
}
