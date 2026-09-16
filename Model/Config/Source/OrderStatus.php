<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;

class OrderStatus implements OptionSourceInterface
{
    /**
     * @var OrderConfig
     */
    private $orderConfig;

    public function __construct(OrderConfig $orderConfig)
    {
        $this->orderConfig = $orderConfig;
    }

    /**
     * @return array<int, array{value:string,label:string|\Magento\Framework\Phrase}>
     */
    public function toOptionArray(): array
    {
        $options = [
            [
                'value' => '',
                'label' => __('— Do not change —'),
            ],
        ];

        foreach ($this->orderConfig->getStatuses() as $code => $label) {
            $options[] = [
                'value' => (string) $code,
                'label' => (string) $label,
            ];
        }

        return $options;
    }
}
