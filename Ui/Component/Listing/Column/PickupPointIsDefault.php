<?php

namespace SamedayCourier\Shipping\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class PickupPointIsDefault extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$items) {
                $items['is_default'] = $items['is_default'] ? 'Default' : '';
            }
        }

        return $dataSource;
    }
}