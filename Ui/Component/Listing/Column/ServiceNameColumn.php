<?php

namespace SamedayCourier\Shipping\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use SamedayCourier\Shipping\Helper\GeneralHelper;

class ServiceNameColumn extends Column
{
    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        $generalHelper = new GeneralHelper();

        if (null !== $dataSource['data']['items'] ?? null) {
            foreach ($dataSource['data']['items'] as &$items) {
                if ($generalHelper->isOoHService($items['code'])) {
                    $items['name'] = $generalHelper::OOH_LABEL[$generalHelper->getHostCountry()];
                    $items['sameday_name'] = $generalHelper::OOH_SERVICE_LABEL;
                }
            }
        }

        return $dataSource;
    }
}
