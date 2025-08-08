<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Exception;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use SamedayCourier\Shipping\Api\Data\CityInterface;
use SamedayCourier\Shipping\Api\Data\CitySearchResultsInterfaceFactory;
use SamedayCourier\Shipping\Api\CityRepositoryInterface;
use SamedayCourier\Shipping\Helper\SearchResultHelper;
use SamedayCourier\Shipping\Model\CityFactory;
use SamedayCourier\Shipping\Model\ResourceModel\City\CollectionFactory;

class CityRepository implements CityRepositoryInterface
{
    /**
     * @var CityFactory
     */
    private $cityFactory;

    /**
     * @var City
     */
    private $cityResourceModel;

    /**
     * @var CollectionFactory
     */
    private $cityCollectionFactory;

    /**
     * @var CitySearchResultsInterfaceFactory
     */
    private $citySearchResultsFactory;

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
     * @param CityFactory $cityFactory
     * @param City $cityResourceModel
     * @param CollectionFactory $cityCollectionFactory
     * @param CitySearchResultsInterfaceFactory $citySearchResultsFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SearchResultHelper $searchResultHelper
     */
    public function __construct(
        CityFactory $cityFactory,
        City $cityResourceModel,
        CollectionFactory $cityCollectionFactory,
        CitySearchResultsInterfaceFactory $citySearchResultsFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchResultHelper $searchResultHelper
    ) {
        $this->cityFactory = $cityFactory;
        $this->cityResourceModel = $cityResourceModel;
        $this->cityCollectionFactory = $cityCollectionFactory;
        $this->citySearchResultsFactory = $citySearchResultsFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultHelper = $searchResultHelper;
    }

    /**
     * @param CityInterface $city
     *
     * @return void
     *
     * @throws AlreadyExistsException
     */
    public function save(CityInterface $city): void
    {
        $cityModel = $this->cityFactory->create();
        if ($city->getId()) {
            $this->cityResourceModel->load($cityModel, $city->getId());
        }

        $cityModel->updateData($city);

        $this->cityResourceModel->save($cityModel);
    }

    /**
     * @param int $id
     *
     * @return CityInterface
     *
     * @throws NoSuchEntityException
     */
    public function get(int $id): CityInterface
    {
        $cityModel = $this->cityFactory->create();
        $this->cityResourceModel->load($cityModel, $id);

        if (!$cityModel->getId()) {
            throw NoSuchEntityException::singleField(CityInterface::ID, $id);
        }

        return $cityModel->getDataModel();
    }

    /**
     * @param int $samedayId
     * @return CityInterface
     *
     * @throws NoSuchEntityException
     */
    public function getBySamedayId(int $samedayId): CityInterface
    {
        $items = $this->getList(
            $this->searchCriteriaBuilder
                ->addFilter(CityInterface::SAMEDAY_ID, $samedayId)
                ->setPageSize(1)
                ->create()
        );

        if (!$items) {
            throw NoSuchEntityException::singleField(CityInterface::SAMEDAY_ID, $samedayId);
        }

        return $items[0];
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return array
     */
    public function getList(SearchCriteriaInterface $searchCriteria): array
    {
        $collection = $this->cityCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection, CityInterface::class);

        $this->searchResultHelper->buildSearchCollection($searchCriteria, $collection);

        /** @var CityInterface[] $cities */
        $cities = [];
        /** @var \SamedayCourier\Shipping\Model\City $city */
        foreach ($collection->getItems() as $city) {
            $this->cityResourceModel->load($city, $city->getId());
            $cities[] = $city->getDataModel();
        }

        return $this->citySearchResultsFactory->create()
            ->setItems($cities)
            ->setTotalCount($collection->getSize())
            ->getItems()
        ;
    }

    /**
     * @param CityInterface $city
     *
     * @return bool
     *
     * @throws Exception
     */
    public function delete(CityInterface $city): bool
    {
        return $this->deleteById($city->getId());
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
        $cityModel = $this->cityFactory->create();
        $this->cityResourceModel->load($cityModel, $id);

        if (!$cityModel->getId()) {
            return false;
        }

        $this->cityResourceModel->delete($cityModel);

        return true;
    }
}
