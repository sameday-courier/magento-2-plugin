<?php

namespace SamedayCourier\Shipping\Model\DataProvider;

use Exception;
use Magento\Ui\DataProvider\AbstractDataProvider;
use SamedayCourier\Shipping\Helper\GeneralHelper;
use SamedayCourier\Shipping\Model\ResourceModel\Service\CollectionFactory;
use SamedayCourier\Shipping\Model\ResourceModel\ServiceRepository;
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
     * @var ServiceRepository $samedayServiceRepository
     */
    private $samedayServiceRepository;

    /**
     * @param CollectionFactory $collectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param GeneralHelper $generalHelper
     * @param ServiceRepository $samedayServiceRepository
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        GeneralHelper $generalHelper,
        ServiceRepository $samedayServiceRepository,
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collectionFactory->create();
        $this->generalHelper = $generalHelper;
        $this->samedayServiceRepository = $samedayServiceRepository;
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

        $fieldName = $this->getRequestFieldName();

        $meta['service']['children']['name']['arguments']['data']['config'] = [
            'label' => __('Display name'),
            'componentType' => 'field',
            'formElement' => 'input',
            'dataScope' => 'name',
            'disabled' => false
        ];

        // Get ID of Service from URI string
        if (null !== $id = explode(sprintf('%s/', $fieldName), $_SERVER['REQUEST_URI'], 2)[1] ?? null) {
            $id = (int) (explode('/', $id)[0] ?? null);
            if ($id > 0) {
                try {
                    $service = $this->samedayServiceRepository->get($id);
                } catch (Exception $exception) {
                    $service = null;
                }

                if ((null !== $service) && $this->generalHelper->isOoHService($service->getCode())) {
                    $meta['service']['children']['name']['arguments']['data']['config'] = [
                        'disabled' => true,
                        'tooltip' => [
                            'description' => __($this->generalHelper::OOH_LABEL_INFO[
                                $this->generalHelper->getHostCountry()
                            ])
                        ],
                    ];
                }
            }
        }

        return $meta;
    }
}
