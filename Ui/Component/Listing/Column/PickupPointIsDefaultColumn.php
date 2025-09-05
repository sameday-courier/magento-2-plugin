<?php

namespace SamedayCourier\Shipping\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class PickupPointIsDefaultColumn extends Column
{
    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$items) {
                $items['is_default'] = $items['is_default'] ? 'Yes' : 'No';
            }
        }

        return $dataSource;
    }
}
