<?php

namespace SamedayCourier\Shipping\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class ServiceStatus extends Column implements OptionSourceInterface
{
    const DISABLED = 0;
    const ENABLED = 1;
    const INTERVAL = 2;

    public function toOptionArray()
    {
        return [
            ['value' => self::DISABLED, 'label' => 'Disabled'],
            ['value' => self::ENABLED, 'label' => 'Enabled'],
            ['value' => self::INTERVAL, 'label' => 'Interval'],
        ];
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$items) {
                $items['status'] = $this->getStatus($items['status']);
            }
        }

        return $dataSource;
    }

    private function getStatus($status)
    {
        foreach ($this->toOptionArray() as $option) {
            if ($option['value'] == $status) {
                return $option['label'];
            }
        }

        return '';
    }
}