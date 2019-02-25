<?php

namespace SamedayCourier\Shipping\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\AbstractExtensibleModel;
use SamedayCourier\Shipping\Api\Data\ServiceExtensionInterface;
use SamedayCourier\Shipping\Api\Data\ServiceInterface;

class Service extends AbstractExtensibleModel
{
    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    private $dataProcessor;

    /**
     * @var \SamedayCourier\Shipping\Api\Data\ServiceInterfaceFactory;
     */
    private $serviceDataFactory;

    /**
     * Service constructor.
     *
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     * @param \SamedayCourier\Shipping\Api\Data\ServiceInterfaceFactory $serviceDataFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \SamedayCourier\Shipping\Api\Data\ServiceInterfaceFactory $serviceDataFactory,
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
        $this->serviceDataFactory = $serviceDataFactory;
    }

    protected function _construct()
    {
        $this->_init(\SamedayCourier\Shipping\Model\ResourceModel\Service::class);
    }

    /**
     * @return ServiceInterface
     */
    public function getDataModel()
    {
        $serviceDataObject = $this->serviceDataFactory->create()
            ->setId($this->getData('id'))
            ->setSamedayId($this->getData('sameday_id'))
            ->setSamedayName($this->getData('sameday_name'))
            ->setIsTesting($this->getData('is_testing'))
            ->setName($this->getData('name'))
            ->setPrice($this->getData('price'))
            ->setIsPriceFree($this->getData('is_price_free'))
            ->setPriceFree($this->getData('price_free'))
            ->setStatus($this->getData('status'))
            ->setWorkingDays($this->getData('working_days'));

        return $serviceDataObject;
    }

    /**
     * @param ServiceInterface $service
     *
     * @return $this
     */
    public function updateData(ServiceInterface $service)
    {
        $attributes = $this->dataProcessor
            ->buildOutputDataArray($service, ServiceInterface::class);

        foreach ($attributes as $code => $data) {
            $this->setDataUsingMethod($code, $data);
        }

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return ServiceExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     *
     * @param ServiceExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(ServiceExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
