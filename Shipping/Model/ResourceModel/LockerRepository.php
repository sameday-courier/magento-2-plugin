<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use SamedayCourier\Shipping\Api\Data\LockerInterface;
use SamedayCourier\Shipping\Api\Data\LockerSearchResultsInterface;
use SamedayCourier\Shipping\Api\Data\LockerSearchResultsInterfaceFactory;
use SamedayCourier\Shipping\Api\LockerRepositoryInterface;
use SamedayCourier\Shipping\Model\LockerFactory;
use SamedayCourier\Shipping\Model\ResourceModel\Locker\Collection;
use SamedayCourier\Shipping\Model\ResourceModel\Locker\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LockerRepository implements LockerRepositoryInterface
{
    /**
     * @var LockerFactory
     */
    private $lockerFactory;

    /**
     * @var Locker
     */
    private $lockerResourceModel;

    /**
     * @var CollectionFactory
     */
    private $lockerCollectionFactory;

    /**
     * @var LockerSearchResultsInterfaceFactory
     */
    private $lockerSearchResultsFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * LockerRepository constructor.
     *
     * @param LockerFactory $lockerFactory
     * @param Locker $lockerResourceModel
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        LockerFactory $lockerFactory,
        Locker $lockerResourceModel,
        CollectionFactory $lockerCollectionFactory,
        LockerSearchResultsInterfaceFactory $lockerSearchResultsFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->lockerFactory = $lockerFactory;
        $this->lockerResourceModel = $lockerResourceModel;
        $this->lockerCollectionFactory = $lockerCollectionFactory;
        $this->lockerSearchResultsFactory = $lockerSearchResultsFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function getByLockerId($id)
    {
        $lockerModel = $this->lockerFactory->create();
        $this->lockerResourceModel->load($lockerModel, $id, LockerInterface::LOCKER_ID);

        if (!$lockerModel->getId()) {
            throw NoSuchEntityException::singleField(LockerInterface::LOCKER_ID, $id);
        }

        return $lockerModel->getDataModel();
    }

    /**
     * @inheritdoc
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(LockerInterface $locker)
    {
        $lockerModel = null;
        if ($locker->getId()) {
            $lockerModel = $this->lockerFactory->create();
            $this->lockerResourceModel->load($lockerModel, $locker->getId());
        } else {
            $lockerModel = $this->lockerFactory->create();
        }

        $lockerModel->updateData($locker);

        $this->lockerResourceModel->save($lockerModel);
    }

    /**
     * @inheritdoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->lockerCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection, LockerInterface::class);

        // Add filters from root filter group to the collection.
        /** @var FilterGroup $group */
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $fields = [];
            $conditions = [];

            foreach ($group->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }

            if ($fields) {
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

        /** @var LockerInterface[] $lockers */
        $lockers = [];
        /** @var \SamedayCourier\Shipping\Model\Locker $locker */
        foreach ($collection->getItems() as $locker) {
            $this->lockerResourceModel->load($locker, $locker->getId());
            $lockers[] = $locker->getStoredData();
        }

        /** @var LockerSearchResultsInterface $searchResults */
        $searchResults = $this->lockerSearchResultsFactory->create()
            ->setItems($lockers)
            ->setSearchCriteria($searchCriteria)
            ->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function getListByTesting($isTesting)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(LockerInterface::IS_TESTING, $isTesting)
            ->create();

        return $this->getList($searchCriteria);
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function deleteById($id)
    {
        $lockerModel = $this->lockerFactory->create();
        $this->lockerResourceModel->load($lockerModel, $id);

        if (!$lockerModel->getId()) {
            return false;
        }

        $this->lockerResourceModel->delete($lockerModel);

        return true;
    }
}
