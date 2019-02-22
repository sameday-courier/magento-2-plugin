<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use SamedayCourier\Shipping\Api\Data\PickupPointInterface;
use SamedayCourier\Shipping\Api\Data\PickupPointSearchResultsInterface;
use SamedayCourier\Shipping\Api\Data\PickupPointSearchResultsInterfaceFactory;
use SamedayCourier\Shipping\Api\PickupPointRepositoryInterface;
use SamedayCourier\Shipping\Model\PickupPointFactory;
use SamedayCourier\Shipping\Model\ResourceModel\PickupPoint\Collection;
use SamedayCourier\Shipping\Model\ResourceModel\PickupPoint\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PickupPointRepository implements PickupPointRepositoryInterface
{
    /**
     * @var PickupPointFactory
     */
    private $pickupPointFactory;

    /**
     * @var PickupPoint
     */
    private $pickupPointResourceModel;

    /**
     * @var CollectionFactory
     */
    private $pickupPointCollectionFactory;

    /**
     * @var PickupPointSearchResultsInterfaceFactory $pickupPointSearchResultsFactory
     */
    private $pickupPointSearchResultsFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * PickupPointRepository constructor.
     *
     * @param PickupPointFactory $pickupPointFactory
     * @param PickupPoint $pickupPointResourceModel
     * @param CollectionFactory $pickupPointCollectionFactory
     * @param PickupPointSearchResultsInterfaceFactory $pickupPointSearchResultsFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        PickupPointFactory $pickupPointFactory,
        PickupPoint $pickupPointResourceModel,
        CollectionFactory $pickupPointCollectionFactory,
        PickupPointSearchResultsInterfaceFactory $pickupPointSearchResultsFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->pickupPointFactory = $pickupPointFactory;
        $this->pickupPointResourceModel = $pickupPointResourceModel;
        $this->pickupPointCollectionFactory = $pickupPointCollectionFactory;
        $this->pickupPointSearchResultsFactory = $pickupPointSearchResultsFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * @inheritdoc
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(PickupPointInterface $pickupPoint)
    {
        $pickupPointModel = null;
        if ($pickupPoint->getId()) {
            $pickupPointModel = $this->pickupPointFactory->create();
            $this->pickupPointResourceModel->load($pickupPointModel, $pickupPoint->getId());
        } else {
            $pickupPointModel = $this->pickupPointFactory->create();
        }

        $pickupPointModel->updateData($pickupPoint);

        $this->pickupPointResourceModel->save($pickupPointModel);
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $pickupPointModel = $this->pickupPointFactory->create();
        $this->pickupPointResourceModel->load($pickupPointModel, $id);

        if (!$pickupPointModel->getId()) {
            throw NoSuchEntityException::singleField(PickupPointInterface::ID, $id);
        }

        return $pickupPointModel->getDataModel();
    }

    /**
     * @inheritdoc
     */
    public function getBySamedayId($samedayId, $isTesting)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(PickupPointInterface::SAMEDAY_ID, $samedayId)
            ->addFilter(PickupPointInterface::IS_TESTING, $isTesting)
            ->setPageSize(1)
            ->create();

        $items = $this->getList($searchCriteria)->getItems();

        if (!$items) {
            throw NoSuchEntityException::doubleField(PickupPointInterface::SAMEDAY_ID, $samedayId, PickupPointInterface::IS_TESTING, $isTesting);
        }

        return $items[0];
    }

    /**
     * @inheritdoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->pickupPointCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection, PickupPointInterface::class);
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var PickupPointInterface[] $pickupPoints */
        $pickupPoints = [];
        /** @var \SamedayCourier\Shipping\Model\PickupPoint $pickupPoint */
        foreach ($collection->getItems() as $pickupPoint) {
            $pickupPoints[] = $pickupPoint->getDataModel();
        }

        /** @var PickupPointSearchResultsInterface $searchResults */
        $searchResults = $this->pickupPointSearchResultsFactory->create()
            ->setItems($pickupPoints)
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
            ->addFilter(PickupPointInterface::IS_TESTING, $isTesting)
            ->create();

        return $this->getList($searchCriteria);
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function delete(PickupPointInterface $pickupPoint)
    {
        return $this->deleteById($pickupPoint->getId());
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function deleteById($id)
    {
        $pickupPointModel = $this->pickupPointFactory->create();
        $this->pickupPointResourceModel->load($pickupPointModel, $id);

        if (!$pickupPointModel->getId()) {
            return false;
        }

        $this->pickupPointResourceModel->delete($pickupPointModel);

        return true;
    }

    /**
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get('SamedayCourier\Shipping\Api\SearchCriteria\PickupPointCollectionProcessor');
        }

        return $this->collectionProcessor;
    }
}
