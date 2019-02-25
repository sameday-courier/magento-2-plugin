<?php

namespace SamedayCourier\Shipping\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class ServicePriceFree extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$items) {
                $items['price_free'] = $items['is_price_free'] ? $items['price_free'] : '';
            }
        }

        return $dataSource;
    }
}