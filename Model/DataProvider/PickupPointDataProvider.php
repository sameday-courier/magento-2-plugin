<?php

namespace SamedayCourier\Shipping\Model\DataProvider;

use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class PickupPointDataProvider extends AbstractDataProvider
{
    /**
     * @var UrlInterface $urlBuilder
     */
    private $urlBuilder;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param UrlInterface $urlBuilder
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        UrlInterface $urlBuilder,
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );

        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        $meta = parent::getMeta();

        $meta['pickuppoint']['children']['url']['arguments']['data']['config'] = [
            'label' => __('URL'),
            'componentType' => 'field',
            'formElement' => 'input',
            'dataScope' => 'url',
            'default' => $this->urlBuilder->getUrl('samedaycourier_shipping/city/refresh'),
            'disabled' => true,
            'visible' => true,
        ];

        return $meta;
    }

    /**
     * @param $filter
     * @param $alias
     *
     * @return void
     */
    public function addFilter($filter, $alias = null): void
    {
        // Nothing to do, just override parent
    }
}
