<?php

namespace SamedayCourier\Shipping\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class CityColumn extends Column implements OptionSourceInterface
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => '',
                'label' => 'Select City'
            ]
        ];
    }
}
