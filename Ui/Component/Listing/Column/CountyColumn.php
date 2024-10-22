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

class CountyColumn extends Column implements OptionSourceInterface
{
    /**
     * @var ApiHelper $apiHelper
     */
    private $apiHelper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ApiHelper $apiHelper,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->apiHelper = $apiHelper;
    }

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return array_map(
            static function (CountyObject $county) {
                return [
                    'value' => $county->getId(),
                    'label' => $county->getName(),
                ];
            },
            $this->getCounties()
        );
    }

    /**
     * @return CountyObject[]
     */
    private function getCounties(): array
    {
        /** @var SamedayGetCountiesResponse $counties */
        $counties = $this->apiHelper->doRequest(
            new SamedayGetCountiesRequest(''),
            'getCounties',
            false
        );

        return $counties->getCounties() ?? [];
    }
}
