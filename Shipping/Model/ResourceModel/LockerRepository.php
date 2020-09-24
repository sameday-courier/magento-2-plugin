<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use SamedayCourier\Shipping\Api\Data\LockerInterface;
use SamedayCourier\Shipping\Api\LockerRepositoryInterface;
use SamedayCourier\Shipping\Model\LockerFactory;

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
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->lockerFactory = $lockerFactory;
        $this->lockerResourceModel = $lockerResourceModel;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $serviceModel = $this->lockerFactory->create();
        $this->lockerResourceModel->load($serviceModel, $id);

        if (!$serviceModel->getId()) {
            throw NoSuchEntityException::singleField(LockerInterface::ID, $id);
        }

        return $serviceModel->getDataModel();
    }
}
