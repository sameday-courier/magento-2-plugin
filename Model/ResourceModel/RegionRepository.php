<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use SamedayCourier\Shipping\Api\Data\RegionInterface;
use SamedayCourier\Shipping\Helper\SearchResultHelper;
use SamedayCourier\Shipping\Model\ResourceModel\Region as RegionResource;
use SamedayCourier\Shipping\Model\RegionFactory;
use SamedayCourier\Shipping\Api\RegionRepositoryInterface;
use SamedayCourier\Shipping\Api\Data\RegionSearchResultsInterfaceFactory;
use SamedayCourier\Shipping\Model\ResourceModel\Region\CollectionFactory;

class RegionRepository implements RegionRepositoryInterface
{
    /**
     * @var RegionFactory $regionFactory
     */
    private $regionFactory;

    /**
     * @var RegionResource $regionResourceModel
     */
    private $regionResourceModel;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var RegionSearchResultsInterfaceFactory
     */
    private $regionSearchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private $regionCollectionFactory;

    /**
     * @var SearchResultHelper $searchResultHelper
     */
    private $searchResultHelper;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @param RegionFactory $regionFactory
     * @param Region $regionResourceModel
     * @param RegionSearchResultsInterfaceFactory $regionSearchResultsInterfaceFactory
     * @param CollectionFactory $regionCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SearchResultHelper $searchResultHelper
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        RegionFactory $regionFactory,
        Region $regionResourceModel,
        RegionSearchResultsInterfaceFactory $regionSearchResultsInterfaceFactory,
        CollectionFactory $regionCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchResultHelper $searchResultHelper,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    )
    {
        $this->regionFactory = $regionFactory;
        $this->regionResourceModel = $regionResourceModel;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->regionSearchResultsFactory = $regionSearchResultsInterfaceFactory;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->searchResultHelper = $searchResultHelper;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * @param $countryId
     *
     * @return array []RegionInterface
     *
     * @throws InputException
     */
    public function getByCountryId($countryId) : array
    {
        return $this->getList(
            $this->searchCriteriaBuilder
                ->addFilter(RegionInterface::COUNTRY_ID, $countryId)
                ->addSortOrder((new SortOrder())->setField('region_id')->setDirection(SortOrder::SORT_ASC))
                ->create()
            )
        ;
    }

    /**
     * @param string $regionCode
     * @param string $countryCode
     *
     * @return RegionInterface|null
     *
     * @throws InputException
     */
    public function getByCodeAndCountryCode(string $regionCode, string $countryCode): ?RegionInterface
    {
        $items = $this->getList(
            $this->searchCriteriaBuilder
                ->addFilter(RegionInterface::CODE, $regionCode)
                ->addFilter(RegionInterface::COUNTRY_ID, $countryCode)
                ->setPageSize(1)
                ->addSortOrder((new SortOrder())->setField('region_id')->setDirection(SortOrder::SORT_ASC))
                ->create()
            )
        ;

        if (!$items) {
            return null;
        }

        return $items[0];
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return RegionInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria): array
    {
        $collection = $this->regionCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection, RegionInterface::class);

        $collection = $this->searchResultHelper->buildSearchCollection($searchCriteria, $collection);

        /** @var RegionInterface[] $regions */
        $regions = [];
        /** @var \SamedayCourier\Shipping\Model\Region $region */
        foreach ($collection->getItems() as $region) {
            $this->regionResourceModel->load($region, $region->getId());
            $regions[] = $region->getDataModel();
        }

        return $this->regionSearchResultsFactory->create()
            ->setItems($regions)
            ->setSearchCriteria($searchCriteria)
            ->setTotalCount($collection->getSize())
            ->getItems()
        ;
    }

    /**
     * @param RegionInterface $region
     *
     * @return void
     *
     * @throws AlreadyExistsException
     */
    public function save(RegionInterface $region): void
    {
        $regionModel = $this->regionFactory->create();
        if ($region->getRegionId()) {
            $this->regionResourceModel->load($regionModel, $region->getRegionId());
        }

        $regionModel->updateData($region);

        $this->regionResourceModel->save($regionModel);
    }
}
