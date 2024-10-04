<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Exception;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use SamedayCourier\Shipping\Api\Data\PickupPointInterface;
use SamedayCourier\Shipping\Api\Data\PickupPointSearchResultsInterfaceFactory;
use SamedayCourier\Shipping\Api\PickupPointRepositoryInterface;
use SamedayCourier\Shipping\Helper\SearchResultHelper;
use SamedayCourier\Shipping\Model\PickupPointFactory;
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
     * @var PickupPointSearchResultsInterfaceFactory
     */
    private $pickupPointSearchResultsFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SearchResultHelper $searchResultHelper
     */
    private $searchResultHelper;

    /**
     * @param PickupPointFactory $pickupPointFactory
     * @param PickupPoint $pickupPointResourceModel
     * @param CollectionFactory $pickupPointCollectionFactory
     * @param PickupPointSearchResultsInterfaceFactory $pickupPointSearchResultsFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SearchResultHelper $searchResultHelper
     */
    public function __construct(
        PickupPointFactory $pickupPointFactory,
        PickupPoint $pickupPointResourceModel,
        CollectionFactory $pickupPointCollectionFactory,
        PickupPointSearchResultsInterfaceFactory $pickupPointSearchResultsFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchResultHelper $searchResultHelper
    ) {
        $this->pickupPointFactory = $pickupPointFactory;
        $this->pickupPointResourceModel = $pickupPointResourceModel;
        $this->pickupPointCollectionFactory = $pickupPointCollectionFactory;
        $this->pickupPointSearchResultsFactory = $pickupPointSearchResultsFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultHelper = $searchResultHelper;
    }

    /**
     * @param PickupPointInterface $pickupPoint
     *
     * @return void
     *
     * @throws AlreadyExistsException
     */
    public function save(PickupPointInterface $pickupPoint): void
    {
        $pickupPointModel = $this->pickupPointFactory->create();
        if ($pickupPoint->getId()) {
            $this->pickupPointResourceModel->load($pickupPointModel, $pickupPoint->getId());
        }

        $pickupPointModel->updateData($pickupPoint);

        $this->pickupPointResourceModel->save($pickupPointModel);
    }

    /**
     * @param int $id
     *
     * @return PickupPointInterface
     *
     * @throws NoSuchEntityException
     */
    public function get(int $id): PickupPointInterface
    {
        $pickupPointModel = $this->pickupPointFactory->create();
        $this->pickupPointResourceModel->load($pickupPointModel, $id);

        if (!$pickupPointModel->getId()) {
            throw NoSuchEntityException::singleField(PickupPointInterface::ID, $id);
        }

        return $pickupPointModel->getDataModel();
    }

    /**
     * @param int $samedayId
     * @param bool $isTesting
     *
     * @return PickupPointInterface
     *
     * @throws NoSuchEntityException
     */
    public function getBySamedayId(int $samedayId, bool $isTesting): PickupPointInterface
    {
        $items = $this->getList(
            $this->searchCriteriaBuilder
            ->addFilter(PickupPointInterface::SAMEDAY_ID, $samedayId)
            ->addFilter(PickupPointInterface::IS_TESTING, $isTesting)
            ->setPageSize(1)
            ->create()
        );

        if (!$items) {
            throw NoSuchEntityException::doubleField(
                PickupPointInterface::SAMEDAY_ID,
                $samedayId,
                PickupPointInterface::IS_TESTING,
                $isTesting
            );
        }

        return $items[0];
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return PickupPointInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria): array
    {
        $collection = $this->pickupPointCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection, PickupPointInterface::class);

        $this->searchResultHelper->buildSearchCollection($searchCriteria, $collection);

        /** @var PickupPointInterface[] $pickupPoints */
        $pickupPoints = [];
        /** @var \SamedayCourier\Shipping\Model\PickupPoint $pickupPoint */
        foreach ($collection->getItems() as $pickupPoint) {
            $this->pickupPointResourceModel->load($pickupPoint, $pickupPoint->getId());
            $pickupPoints[] = $pickupPoint->getDataModel();
        }

        return $this->pickupPointSearchResultsFactory->create()
            ->setItems($pickupPoints)
            ->setSearchCriteria($searchCriteria)
            ->setTotalCount($collection->getSize())
            ->getItems()
        ;
    }

    /**
     * @return PickupPointInterface
     *
     * @throws NoSuchEntityException
     */
    public function getDefaultPickupPoint(): PickupPointInterface
    {
        $items = $this->getList(
            $this->searchCriteriaBuilder
            ->addFilter(PickupPointInterface::IS_DEFAULT, true)
            ->create()
        );

        if (!$items) {
            throw NoSuchEntityException::singleField(PickupPointInterface::IS_DEFAULT, true);
        }

        return $items[0];
    }

    /**
     * @param bool $isTesting
     *
     * @return PickupPointInterface[]
     */
    public function getListByTesting(bool $isTesting): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(PickupPointInterface::IS_TESTING, $isTesting)
            ->create();

        return $this->getList($searchCriteria);
    }

    /**
     * @param PickupPointInterface $pickupPoint
     *
     * @return bool
     *
     * @throws Exception
     */
    public function delete(PickupPointInterface $pickupPoint): bool
    {
        return $this->deleteById($pickupPoint->getId());
    }

    /**
     * @param int $id
     *
     * @return bool
     *
     * @throws Exception
     */
    public function deleteById(int $id): bool
    {
        $pickupPointModel = $this->pickupPointFactory->create();
        $this->pickupPointResourceModel->load($pickupPointModel, $id);

        if (!$pickupPointModel->getId()) {
            return false;
        }

        $this->pickupPointResourceModel->delete($pickupPointModel);

        return true;
    }
}
