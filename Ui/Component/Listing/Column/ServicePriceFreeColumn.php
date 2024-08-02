<?php

namespace SamedayCourier\Shipping\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class ServicePriceFreeColumn extends Column
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
                $items['price_free'] = $items['is_price_free'] ? $items['price_free'] : '';
            }
        }

        return $dataSource;
    }
}
