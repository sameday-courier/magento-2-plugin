<?php

namespace SamedayCourier\Shipping\Model\ResourceModel;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use SamedayCourier\Shipping\Api\Data\OrderInfoInterface;
use SamedayCourier\Shipping\Api\OrderInfoRepositoryInterface;
use SamedayCourier\Shipping\Model\OrderInfoFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderInfoRepository implements OrderInfoRepositoryInterface
{
    /**
     * @var OrderInfoFactory
     */
    private $orderInfoFactory;

    /**
     * @var OrderInfo
     */
    private $orderInfoResourceModel;

    /**
     * OrderInfoRepository constructor.
     *
     * @param OrderInfoFactory $orderInfoFactory
     * @param OrderInfo $orderInfoResourceModel
     */
    public function __construct(
        OrderInfoFactory $orderInfoFactory,
        OrderInfo $orderInfoResourceModel
    ) {
        $this->orderInfoFactory = $orderInfoFactory;
        $this->orderInfoResourceModel = $orderInfoResourceModel;
    }

    /**
     * @param int $orderId
     *
     * @return OrderInfoInterface
     *
     * @throws NoSuchEntityException
     */
    public function getByOrderId(int $orderId): OrderInfoInterface
    {
        $orderInfoModel = $this->orderInfoFactory->create();
        $this->orderInfoResourceModel->load($orderInfoModel, $orderId, OrderInfoInterface::ORDER_ID);

        if (!$orderInfoModel->getId()) {
            throw NoSuchEntityException::singleField(OrderInfoInterface::ORDER_ID, $orderId);
        }

        return $orderInfoModel->getDataModel();
    }

    /**
     * @param OrderInfoInterface $orderInfo
     *
     * @return void
     *
     * @throws AlreadyExistsException
     */
    public function save(OrderInfoInterface $orderInfo): void
    {
        $orderInfoModel = $this->orderInfoFactory->create();
        if ($orderInfo->getId()) {
            $this->orderInfoResourceModel->load($orderInfoModel, $orderInfo->getId());
        }

        $orderInfoModel->updateData($orderInfo);

        $this->orderInfoResourceModel->save($orderInfoModel);
    }
}
