<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use SamedayCourier\Shipping\Api\Data\ServiceInterface;
use SamedayCourier\Shipping\Api\Data\ServiceSearchResultsInterface;
use SamedayCourier\Shipping\Api\Data\ServiceSearchResultsInterfaceFactory;
use SamedayCourier\Shipping\Api\ServiceRepositoryInterface;
use SamedayCourier\Shipping\Model\ServiceFactory;
use SamedayCourier\Shipping\Model\ResourceModel\Service\Collection;
use SamedayCourier\Shipping\Model\ResourceModel\Service\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ServiceRepository implements ServiceRepositoryInterface
{
    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var Service
     */
    private $serviceResourceModel;

    /**
     * @var CollectionFactory
     */
    private $serviceCollectionFactory;

    /**
     * @var ServiceSearchResultsInterfaceFactory
     */
    private $serviceSearchResultsFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * ServiceRepository constructor.
     *
     * @param ServiceFactory $serviceFactory
     * @param Service $serviceResourceModel
     * @param CollectionFactory $serviceCollectionFactory
     * @param ServiceSearchResultsInterfaceFactory $serviceSearchResultsFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        Service $serviceResourceModel,
        CollectionFactory $serviceCollectionFactory,
        ServiceSearchResultsInterfaceFactory $serviceSearchResultsFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->serviceResourceModel = $serviceResourceModel;
        $this->serviceCollectionFactory = $serviceCollectionFactory;
        $this->serviceSearchResultsFactory = $serviceSearchResultsFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(ServiceInterface $service)
    {
        $serviceModel = null;
        if ($service->getId()) {
            $serviceModel = $this->serviceFactory->create();
            $this->serviceResourceModel->load($serviceModel, $service->getId());
        } else {
            $serviceModel = $this->serviceFactory->create();
        }

        $serviceModel->updateData($service);

        $this->serviceResourceModel->save($serviceModel);
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $serviceModel = $this->serviceFactory->create();
        $this->serviceResourceModel->load($serviceModel, $id);

        if (!$serviceModel->getId()) {
            throw NoSuchEntityException::singleField(ServiceInterface::ID, $id);
        }

        return $serviceModel->getDataModel();
    }

    /**
     * @inheritdoc
     */
    public function getBySamedayId($samedayId, $isTesting)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ServiceInterface::SAMEDAY_ID, $samedayId)
            ->addFilter(ServiceInterface::IS_TESTING, $isTesting)
            ->setPageSize(1)
            ->create();

        $items = $this->getList($searchCriteria)->getItems();

        if (!$items) {
            throw NoSuchEntityException::doubleField(ServiceInterface::SAMEDAY_ID, $samedayId, ServiceInterface::IS_TESTING, $isTesting);
        }

        return $items[0];
    }

    /**
     * @inheritdoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->serviceCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection, ServiceInterface::class);

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

        /** @var ServiceInterface[] $services */
        $services = [];
        /** @var \SamedayCourier\Shipping\Model\Service $service */
        foreach ($collection->getItems() as $service) {
            $this->serviceResourceModel->load($service, $service->getId());
            $services[] = $service->getDataModel();
        }

        /** @var ServiceSearchResultsInterface $searchResults */
        $searchResults = $this->serviceSearchResultsFactory->create()
            ->setItems($services)
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
            ->addFilter(ServiceInterface::IS_TESTING, $isTesting)
            ->create();

        return $this->getList($searchCriteria);
    }

    /**
     * @inheritdoc
     */
    public function getAllActive()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ServiceInterface::STATUS, true)
            ->create();

        return $this->getList($searchCriteria);
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function delete(ServiceInterface $service)
    {
        return $this->deleteById($service->getId());
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function deleteById($id)
    {
        $serviceModel = $this->serviceFactory->create();
        $this->serviceResourceModel->load($serviceModel, $id);

        if (!$serviceModel->getId()) {
            return false;
        }

        $this->serviceResourceModel->delete($serviceModel);

        return true;
    }
}
