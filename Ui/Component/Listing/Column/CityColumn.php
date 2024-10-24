<?php

namespace SamedayCourier\Shipping\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Sameday\Objects\CityObject;
use Sameday\Requests\SamedayGetCitiesRequest;
use Sameday\Responses\SamedayGetCitiesResponse;
use SamedayCourier\Shipping\Helper\ApiHelper;

class CityColumn extends Column implements OptionSourceInterface
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
            static function (CityObject $city) {
                return [
                    'value' => $city->getId(),
                    'label' => $city->getName(),
                ];
            },
            $this->getCities()
        );
    }

    /**
     * @return CityObject[]
     */
    private function getCities(): array
    {
        /** @var SamedayGetCitiesResponse|false $cities */
        $cities = $this->apiHelper->doRequest(
            new SamedayGetCitiesRequest('1'),
            'getCities',
            false
        );

        if (false !== $cities) {
            return $cities->getCities();
        }

        return [];
    }
}
