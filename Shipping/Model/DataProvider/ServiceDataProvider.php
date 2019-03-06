<?php

namespace SamedayCourier\Shipping\Model\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use SamedayCourier\Shipping\Model\ResourceModel\Service\CollectionFactory;
use SamedayCourier\Shipping\Model\Service;

class ServiceDataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    private $loadedData;

    /**
     * ServiceDataProvider constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(CollectionFactory $collectionFactory, $name, $primaryFieldName, $requestFieldName, array $meta = [], array $data = [])
    {
        $this->collection = $collectionFactory->create();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        /** @var Service $item */
        foreach ($this->collection->getItems() as $item) {
            $this->loadedData[$item->getId()]['service'] = $item->getData();
        }

        return $this->loadedData;
    }
}
