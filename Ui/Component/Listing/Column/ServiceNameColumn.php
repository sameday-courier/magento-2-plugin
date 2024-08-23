<?php

namespace SamedayCourier\Shipping\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use SamedayCourier\Shipping\Helper\GeneralHelper;

class ServiceNameColumn extends Column
{
    /**
     * @var GeneralHelper $generalHelper
     */
    private $generalHelper;

    /**
     * @param GeneralHelper $generalHelper
     * @param ContextInterface $context
     * @param UiComponentFactory $factory
     */
    public function __construct(
        GeneralHelper $generalHelper,
        ContextInterface $context,
        UiComponentFactory $factory
    )
    {
        parent::__construct(
            $context,
            $factory
        );

        $this->generalHelper = $generalHelper;
    }

    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (null !== $dataSource['data']['items'] ?? null) {
            foreach ($dataSource['data']['items'] as &$items) {
                if ($this->generalHelper->isOoHService($items['code'])) {
                    $items['name'] = $this->generalHelper::OOH_LABEL[$this->generalHelper->getHostCountry()];
                    $items['sameday_name'] = $this->generalHelper::OOH_SERVICE_LABEL;
                }
            }
        }

        return $dataSource;
    }
}
