<?php

namespace SamedayCourier\Shipping\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Serialize\Serializer\Json;

class PickupPointContactPersonColumn extends Column
{
    /**
     * @var Json $json
     */
    private $json;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Json $json
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Json $json,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->json = $json;
    }

    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$items) {
                if ('' !== $items['contact_persons']) {
                    $contactPersons = array_map(
                        static function (array $contactPerson) {
                            return $contactPerson['name'];
                        },
                        $this->json->unserialize($items['contact_persons'])
                    );

                    $items['contact_persons'] = implode(', ', $contactPersons);
                }
            }
        }

        return $dataSource;
    }
}
