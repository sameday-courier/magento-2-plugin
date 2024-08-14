<?php

namespace SamedayCourier\Shipping\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class ServiceStatusColumn extends Column implements OptionSourceInterface
{
    public const DISABLED = 0;
    public const ENABLED = 1;

    public function toOptionArray(): array
    {
        return [
            ['value' => self::DISABLED, 'label' => 'Disabled'],
            ['value' => self::ENABLED, 'label' => 'Enabled'],
        ];
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
                $items['status'] = $this->getStatus($items['status']);
            }
        }

        return $dataSource;
    }

    private function getStatus($status)
    {
        foreach ($this->toOptionArray() as $option) {
            if ($option['value'] === (int) $status) {
                return $option['label'];
            }
        }

        return '';
    }
}
