<?php

namespace SamedayCourier\Shipping\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Sameday\Objects\CountyObject;
use Sameday\Requests\SamedayGetCountiesRequest;
use Sameday\Responses\SamedayGetCountiesResponse;
use SamedayCourier\Shipping\Helper\ApiHelper;
use SamedayCourier\Shipping\Helper\SamedayCountiesHelper;

class CountyColumn extends Column implements OptionSourceInterface
{
    /**
     * @var SamedayCountiesHelper $samedayCountiesHelper
     */
    private $samedayCountiesHelper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SamedayCountiesHelper $samedayCountiesHelper,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->samedayCountiesHelper = $samedayCountiesHelper;
    }

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return array_merge(
            [
                [
                    'value' => '',
                    'label' => 'Select county'
                ]
            ],
            array_map(
                static function (CountyObject $county) {
                    return [
                        'value' => $county->getId(),
                        'label' => $county->getName(),
                    ];
                },
                $this->samedayCountiesHelper->getCounties()
            )
        );
    }
}
