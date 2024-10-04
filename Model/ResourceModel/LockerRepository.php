<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Exception;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use SamedayCourier\Shipping\Api\Data\LockerInterface;
use SamedayCourier\Shipping\Api\Data\LockerSearchResultsInterfaceFactory;
use SamedayCourier\Shipping\Api\LockerRepositoryInterface;
use SamedayCourier\Shipping\Helper\SearchResultHelper;
use SamedayCourier\Shipping\Model\LockerFactory;
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

    public function __construct(
        LockerFactory $lockerFactory,
        Locker $lockerResourceModel,
        CollectionFactory $lockerCollectionFactory,
        LockerSearchResultsInterfaceFactory $lockerSearchResultsFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchResultHelper $searchResultHelper
    ) {
        $this->lockerFactory = $lockerFactory;
        $this->lockerResourceModel = $lockerResourceModel;
        $this->lockerCollectionFactory = $lockerCollectionFactory;
        $this->lockerSearchResultsFactory = $lockerSearchResultsFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultHelper = $searchResultHelper;
    }

    /**
     * @param $id
     *
     * @return LockerInterface
     *
     * @throws NoSuchEntityException
     */
    public function getLockerBySamedayId($id): LockerInterface
    {
        $lockerModel = $this->lockerFactory->create();
        $this->lockerResourceModel->load($lockerModel, $id, LockerInterface::LOCKER_ID);

        if (!$lockerModel->getId()) {
            throw NoSuchEntityException::singleField(LockerInterface::LOCKER_ID, $id);
        }

        return $lockerModel->getDataModel();
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getLockerById($id): LockerInterface
    {
        $lockerModel = $this->lockerFactory->create();
        $this->lockerResourceModel->load($lockerModel, $id, LockerInterface::ID);

        if (!$lockerModel->getId()) {
            throw NoSuchEntityException::singleField(LockerInterface::ID, $id);
        }

        return $lockerModel->getDataModel();
    }

    /**
     * @param LockerInterface $locker
     *
     * @return void
     *
     * @throws AlreadyExistsException
     */
    public function save(LockerInterface $locker): void
    {
        $lockerModel = $this->lockerFactory->create();
        if ($locker->getId()) {
            $this->lockerResourceModel->load($lockerModel, $locker->getId());
        }

        $lockerModel->updateData($locker);

        $this->lockerResourceModel->save($lockerModel);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return LockerInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria): array
    {
        $collection = $this->lockerCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection, LockerInterface::class);

        $this->searchResultHelper->buildSearchCollection($searchCriteria, $collection);

        /** @var LockerInterface[] $lockers */
        $lockers = [];
        /** @var \SamedayCourier\Shipping\Model\Locker $locker */
        foreach ($collection->getItems() as $locker) {
            $this->lockerResourceModel->load($locker, $locker->getId());
            $lockers[] = $locker->getStoredData();
        }

        return $this->lockerSearchResultsFactory->create()
            ->setItems($lockers)
            ->setSearchCriteria($searchCriteria)
            ->setTotalCount($collection->getSize())
            ->getItems()
        ;
    }

    /**
     * @param $isTesting
     *
     * @return LockerInterface[]
     */
    public function getListByTesting($isTesting): array
    {
        return $this->getList($this->searchCriteriaBuilder
            ->addFilter(LockerInterface::IS_TESTING, $isTesting)
            ->create()
        );
    }

    /**
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function deleteById(int $id): bool
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
