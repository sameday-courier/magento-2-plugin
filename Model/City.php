<?php

namespace SamedayCourier\Shipping\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Sameday\Objects\PickupPoint\ContactPersonObject;
use SamedayCourier\Shipping\Api\Data\CityInterface;
use SamedayCourier\Shipping\Api\Data\CityInterfaceFactory;
use SamedayCourier\Shipping\Api\Data\PickupPointInterface;
use SamedayCourier\Shipping\Model\ResourceModel\City as CityResource;

class City extends AbstractExtensibleModel
{
    /**
     * @var DataObjectProcessor
     */
    private $dataProcessor;

    /**
     * @var CityInterfaceFactory $cityDataFactory
     */
    private $cityDataFactory;

    public function _construct()
    {
        $this->_init(CityResource::class);
    }

    public function __construct(
       Context $context,
       Registry $registry,
       DataObjectProcessor $dataProcessor,
       CityInterfaceFactory $cityDataFactory,
       ExtensionAttributesFactory $extensionFactory,
       AttributeValueFactory $customAttributeFactory,
       AbstractResource $resource = null,
       AbstractDb $resourceCollection = null, array $data = []
   )
   {
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
       $this->cityDataFactory = $cityDataFactory;
   }

    /**
     * @return CityInterface
     */
    public function getDataModel(): CityInterface
    {
        return $this->cityDataFactory->create()
            ->setId($this->getData('id'))
            ->setName($this->getData('name'))
            ->setRegionId($this->getData('region_id'))
            ->setSamedayId($this->getData('sameday_id'))
        ;
    }

    /**
     * @param CityInterface $city
     *
     * @return $this
     */
    public function updateData(CityInterface $city): self
    {
        $attributes = $this->dataProcessor
            ->buildOutputDataArray($city, CityInterface::class);

        foreach ($attributes as $code => $data) {
            $this->setDataUsingMethod($code, $data);
        }

        return $this;
    }
}
