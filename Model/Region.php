<?php

namespace SamedayCourier\Shipping\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use SamedayCourier\Shipping\Api\Data\RegionInterface;
use SamedayCourier\Shipping\Api\Data\RegionInterfaceFactory;
use SamedayCourier\Shipping\Api\Data\ServiceExtensionInterface;

class Region extends AbstractExtensibleModel
{
    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    private $dataProcessor;

    /**
     * @var \SamedayCourier\Shipping\Api\Data\RegionInterfaceFactory;
     */
    private $regionDataFactory;

    /**
     * Region constructor.
     *
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     * @param RegionInterfaceFactory $regionDataFactory
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \SamedayCourier\Shipping\Api\Data\RegionInterfaceFactory $regionDataFactory,
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $resource, $resourceCollection, $data);

        $this->dataProcessor = $dataProcessor;
        $this->regionDataFactory = $regionDataFactory;
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\Region::class);
    }

    /**
     * @return RegionInterface
     */
    public function getDataModel(): RegionInterface
    {
        return $this->regionDataFactory->create()
            ->setRegionId($this->getData('region_id'))
            ->setCountryId($this->getData('country_id'))
            ->setCode($this->getData('code'))
            ->setName($this->getData('default_name'))
        ;
    }

    /**
     * @param RegionInterface $region
     *
     * @return $this
     */
    public function updateData(RegionInterface $region)
    {
        $attributes = $this->dataProcessor
            ->buildOutputDataArray($region, RegionInterface::class);

        foreach ($attributes as $code => $data) {
            $this->setDataUsingMethod($code, $data);
        }

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return RegionExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     *
     * @param RegionExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(RegionExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
