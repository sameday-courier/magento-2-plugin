<?php

namespace SamedayCourier\Shipping\Model\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use SamedayCourier\Shipping\Helper\GeneralHelper;
use SamedayCourier\Shipping\Model\ResourceModel\Service\CollectionFactory;
use SamedayCourier\Shipping\Model\Service;

class ServiceDataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var GeneralHelper $generalHelper
     */
    private $generalHelper;

    /**
     * @param CollectionFactory $collectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param GeneralHelper $generalHelper
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        GeneralHelper $generalHelper,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $collectionFactory->create();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->generalHelper = $generalHelper;
    }

    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        /** @var Service $item */
        foreach ($this->collection->getItems() as $item) {
            $data = $item->getData();
            if ($this->generalHelper->isOoHService($data['code'])) {
                $data['name'] = $this->generalHelper::OOH_LABEL[$this->generalHelper->getHostCountry()];
            }
            $this->loadedData[$item->getId()]['service'] = $data;
        }

        return $this->loadedData;
    }

    public function getMeta(): array
    {
        $meta = parent::getMeta();

        $meta['service']['children']['name']['arguments']['data']['config'] = [
            'label' => __('Display name'),
            'componentType' => 'field',
            'formElement' => 'input',
            'dataScope' => 'name',
            'disabled' => true,
            'tooltip' => [
                'description' => __($this->generalHelper::OOH_LABEL_INFO)
            ],
        ];

        return $meta;
    }
}
