<?php

namespace SamedayCourier\Shipping\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use SamedayCourier\Shipping\Api\Data\OrderInfoInterface;
use SamedayCourier\Shipping\Api\Data\OrderInfoInterfaceFactory;

class OrderInfo extends AbstractExtensibleModel
{
    /**
     * @var DataObjectProcessor
     */
    private $dataProcessor;

    /**
     * @var OrderInfoInterfaceFactory;
     */
    private $orderInfoDataFactory;

    public function __construct(
        DataObjectProcessor $dataProcessor,
        OrderInfoInterfaceFactory $orderInfoDataFactory,
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );

        $this->dataProcessor = $dataProcessor;
        $this->orderInfoDataFactory = $orderInfoDataFactory;
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\OrderInfo::class);
    }

    /**
     * @return OrderInfoInterface
     */
    public function getDataModel(): OrderInfoInterface
    {
        return $this->orderInfoDataFactory->create()
            ->setId($this->getData(OrderInfoInterface::ID))
            ->setOrderId($this->getData(OrderInfoInterface::ORDER_ID))
            ->setSamedaycourierLocker($this->getData(OrderInfoInterface::SAMEDAYCOURIER_LOCKER))
            ->setSamedaycourierDestinationAddressHd(
                $this->getData(OrderInfoInterface::SAMEDAYCOURIER_DESTINATION_ADDRESS_HD)
            )
            ->setSamedaycourierFee(OrderInfoInterface::SAMEDAYCOURIER_FEE)
        ;
    }

    /**
     * @param OrderInfoInterface $orderInfo
     *
     * @return $this
     */
    public function updateData(OrderInfoInterface $orderInfo): self
    {
        $attributes = $this->dataProcessor->buildOutputDataArray($orderInfo, OrderInfoInterface::class);

        foreach ($attributes as $code => $data) {
            $this->setDataUsingMethod($code, $data);
        }

        return $this;
    }
}
