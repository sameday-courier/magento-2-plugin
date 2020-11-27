<?php

namespace SamedayCourier\Shipping\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use SamedayCourier\Shipping\Api\Data\AwbInterface;

class Awb extends AbstractExtensibleModel
{
    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    private $dataProcessor;

    /**
     * @var \SamedayCourier\Shipping\Api\Data\AwbInterfaceFactory;
     */
    private $awbDataFactory;

    public function __construct(
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \SamedayCourier\Shipping\Api\Data\AwbInterfaceFactory $awbDataFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $resource, $resourceCollection, $data);

        $this->dataProcessor = $dataProcessor;
        $this->awbDataFactory = $awbDataFactory;
    }

    protected function _construct()
    {
        $this->_init(\SamedayCourier\Shipping\Model\ResourceModel\Awb::class);
    }

    /**
     * @return AwbInterface
     */
    public function getDataModel()
    {
        $awbDataObject = $this->awbDataFactory->create()
            ->setId($this->getData(AwbInterface::ID))
            ->setOrderId($this->getData(AwbInterface::ORDER_ID))
            ->setAwbNumber($this->getData(AwbInterface::AWB_NUMBER))
            ->setParcels($this->getData(AwbInterface::PARCELS))
            ->setAwbCost($this->getData(AwbInterface::AWB_COST))
            ;

        return $awbDataObject;
    }

    /**
     * @param AwbInterface $awb
     *
     * @return $this
     */
    public function updateData(AwbInterface $awb)
    {
        $attributes = $this->dataProcessor
            ->buildOutputDataArray($awb, AwbInterface::class);

        foreach ($attributes as $code => $data) {
            $this->setDataUsingMethod($code, $data);
        }

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return ExtensionAttributesInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     *
     * @param AwbExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(AwbExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
