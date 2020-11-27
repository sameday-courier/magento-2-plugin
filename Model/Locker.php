<?php

namespace SamedayCourier\Shipping\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use SamedayCourier\Shipping\Api\Data\LockerInterface;

class Locker extends AbstractExtensibleModel
{
    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    private $dataProcessor;

    /**
     * @var \SamedayCourier\Shipping\Api\Data\LockerInterfaceFactory;
     */
    private $lockerDataFactory;

    public function __construct(
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \SamedayCourier\Shipping\Api\Data\LockerInterfaceFactory $lockerDataFactory,
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
        $this->lockerDataFactory = $lockerDataFactory;
    }

    protected function _construct()
    {
        $this->_init(\SamedayCourier\Shipping\Model\ResourceModel\Locker::class);
    }

    /**
     * @return LockerInterface
     */
    public function getDataModel()
    {
        $lockerDataObject = $this->lockerDataFactory->create()
            ->setId($this->getData('id'))
            ;

        return $lockerDataObject;
    }

    /**
     * @param LockerInterface $locker
     *
     * @return $this
     */
    public function updateData(LockerInterface $locker)
    {
        $attributes = $this->dataProcessor
            ->buildOutputDataArray($locker, LockerInterface::class);

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
     * @param LockerExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(LockerExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
