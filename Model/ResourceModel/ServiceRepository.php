<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Exception;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use SamedayCourier\Shipping\Api\Data\ServiceInterface;
use SamedayCourier\Shipping\Api\Data\ServiceSearchResultsInterfaceFactory;
use SamedayCourier\Shipping\Api\ServiceRepositoryInterface;
use SamedayCourier\Shipping\Helper\SearchResultHelper;
use SamedayCourier\Shipping\Model\ServiceFactory;
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
     * @param ServiceFactory $serviceFactory
     * @param Service $serviceResourceModel
     * @param CollectionFactory $serviceCollectionFactory
     * @param ServiceSearchResultsInterfaceFactory $serviceSearchResultsFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SearchResultHelper $searchResultHelper
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        Service $serviceResourceModel,
        CollectionFactory $serviceCollectionFactory,
        ServiceSearchResultsInterfaceFactory $serviceSearchResultsFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchResultHelper $searchResultHelper
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->serviceResourceModel = $serviceResourceModel;
        $this->serviceCollectionFactory = $serviceCollectionFactory;
        $this->serviceSearchResultsFactory = $serviceSearchResultsFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultHelper = $searchResultHelper;
    }

    /**
     * @param ServiceInterface $service
     *
     * @return void
     *
     * @throws AlreadyExistsException
     */
    public function save(ServiceInterface $service): void
    {
        $serviceModel = $this->serviceFactory->create();
        if ($service->getId()) {
            $this->serviceResourceModel->load($serviceModel, $service->getId());
        }

        $serviceModel->updateData($service);

        $this->serviceResourceModel->save($serviceModel);
    }

    /**
     * @param int $id
     *
     * @return ServiceInterface
     *
     * @throws NoSuchEntityException
     */
    public function get(int $id): ServiceInterface
    {
        $serviceModel = $this->serviceFactory->create();
        $this->serviceResourceModel->load($serviceModel, $id);

        if (!$serviceModel->getId()) {
            throw NoSuchEntityException::singleField(ServiceInterface::ID, $id);
        }

        return $serviceModel->getDataModel();
    }

    /**
     * @param int $samedayId
     * @param bool $isTesting
     *
     * @return ServiceInterface
     *
     * @throws NoSuchEntityException
     */
    public function getBySamedayId(int $samedayId, bool $isTesting): ServiceInterface
    {
        $items = $this->getList(
            $this->searchCriteriaBuilder
            ->addFilter(ServiceInterface::SAMEDAY_ID, $samedayId)
            ->addFilter(ServiceInterface::IS_TESTING, $isTesting)
            ->setPageSize(1)
            ->create()
        );

        if (!$items) {
            throw NoSuchEntityException::doubleField(
                ServiceInterface::SAMEDAY_ID,
                $samedayId,
                ServiceInterface::IS_TESTING,
                $isTesting
            );
        }

        return $items[0];
    }

    /**
     * @param string $code
     * @param bool $isTesting
     *
     * @return ServiceInterface
     *
     * @throws NoSuchEntityException
     */
    public function getBySamedayCode(string $code, bool $isTesting): ServiceInterface
    {
        $items = $this->getList(
            $this->searchCriteriaBuilder
            ->addFilter(ServiceInterface::CODE, $code)
            ->addFilter(ServiceInterface::IS_TESTING, $isTesting)
            ->setPageSize(1)
            ->create()
        );

        if (!$items) {
            throw NoSuchEntityException::doubleField(
                ServiceInterface::SAMEDAY_ID, ServiceInterface::CODE,
                ServiceInterface::IS_TESTING, $isTesting
            );
        }

        return $items[0];
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return ServiceInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria): array
    {
        $collection = $this->serviceCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection, ServiceInterface::class);

        $collection = $this->searchResultHelper->buildSearchCollection($searchCriteria, $collection);

        /** @var ServiceInterface[] $services */
        $services = [];
        /** @var \SamedayCourier\Shipping\Model\Service $service */
        foreach ($collection->getItems() as $service) {
            $this->serviceResourceModel->load($service, $service->getId());
            $services[] = $service->getDataModel();
        }

        return $this->serviceSearchResultsFactory->create()
            ->setItems($services)
            ->setSearchCriteria($searchCriteria)
            ->setTotalCount($collection->getSize())
            ->getItems()
        ;
    }

    /**
     * @param bool $isTesting
     *
     * @return ServiceInterface[]
     */
    public function getListByTesting(bool $isTesting): array
    {
        return $this->getList(
            $this->searchCriteriaBuilder
            ->addFilter(ServiceInterface::IS_TESTING, $isTesting)
            ->create()
        );
    }

    /**
     * @param bool $isTesting
     *
     * @return ServiceInterface[]
     */
    public function getAllActive(bool $isTesting): array
    {
        return $this->getList(
            $this->searchCriteriaBuilder
            ->addFilter(ServiceInterface::STATUS, true)
            ->addFilter(ServiceInterface::IS_TESTING, $isTesting)
            ->create()
        );
    }

    /**
     * @param $isTesting
     *
     * @return ServiceInterface[]
     */
    public function getAllActiveByTesting($isTesting): array
    {
        return $this->getList(
            $this->searchCriteriaBuilder
            ->addFilter(ServiceInterface::STATUS, true)
            ->addFilter(ServiceInterface::IS_TESTING, $isTesting)
            ->create()
        );
    }

    /**
     * @param ServiceInterface $service
     *
     * @return bool
     *
     * @throws Exception
     */
    public function delete(ServiceInterface $service): bool
    {
        return $this->deleteById($service->getId());
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
        $serviceModel = $this->serviceFactory->create();
        $this->serviceResourceModel->load($serviceModel, $id);

        if (!$serviceModel->getId()) {
            return false;
        }

        $this->serviceResourceModel->delete($serviceModel);

        return true;
    }
}
