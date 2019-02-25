<?php

namespace SamedayCourier\Shipping\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\AbstractExtensibleModel;
use Sameday\Objects\PickupPoint\ContactPersonObject;
use SamedayCourier\Shipping\Api\Data\PickupPointExtensionInterface;
use SamedayCourier\Shipping\Api\Data\PickupPointInterface;

class PickupPoint extends AbstractExtensibleModel
{
    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    private $dataProcessor;

    /**
     * @var \SamedayCourier\Shipping\Api\Data\PickupPointInterfaceFactory;
     */
    private $pickupPointDataFactory;

    /**
     * PickupPoint constructor.
     *
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     * @param \SamedayCourier\Shipping\Api\Data\PickupPointInterfaceFactory $pickupPointDataFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \SamedayCourier\Shipping\Api\Data\PickupPointInterfaceFactory $pickupPointDataFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $resource, $resourceCollection, $data);

        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataProcessor = $dataProcessor;
        $this->pickupPointDataFactory = $pickupPointDataFactory;
    }

    protected function _construct()
    {
        $this->_init(\SamedayCourier\Shipping\Model\ResourceModel\PickupPoint::class);
    }

    /**
     * @return PickupPointInterface
     */
    public function getDataModel()
    {
        $contactPersons = $this->getData('contact_persons');
        if (!is_array($contactPersons) && !empty($contactPersons)) {
            $contactPersons = json_decode($contactPersons, true);
        }

        $pickupPointDataObject = $this->pickupPointDataFactory->create()
            ->setId($this->getData('id'))
            ->setSamedayId($this->getData('sameday_id'))
            ->setSamedayAlias($this->getData('sameday_alias'))
            ->setIsDefault($this->getData('is_testing'))
            ->setCity($this->getData('city'))
            ->setCounty($this->getData('county'))
            ->setAddress($this->getData('address'))
            ->setContactPersons(array_map(
                function (array $data) {
                    return new ContactPersonObject($data['id'], $data['name'], $data['phone'], $data['default']);
                },
                $contactPersons
            ))
            ->setIsDefault($this->getData('is_default'));

        return $pickupPointDataObject;
    }

    /**
     * @param PickupPointInterface $pickupPoint
     *
     * @return $this
     */
    public function updateData(PickupPointInterface $pickupPoint)
    {
        $attributes = $this->dataProcessor
            ->buildOutputDataArray($pickupPoint, PickupPointInterface::class);

        foreach ($attributes as $code => $data) {
            $this->setDataUsingMethod($code, $data);
        }

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return PickupPointExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     *
     * @param PickupPointExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(PickupPointExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
