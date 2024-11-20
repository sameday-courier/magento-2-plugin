<?php

namespace SamedayCourier\Shipping\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use SamedayCourier\Shipping\Helper\ApiHelper;

class CountryColumn extends Column implements OptionSourceInterface
{
    /**
     * @var ApiHelper $apiHelper
     */
    protected $apiHelper;

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
        return [$this->apiHelper::DEFAULT_COUNTRIES[$this->apiHelper->getHostCountry()]];
    }
}
