<?php

namespace SamedayCourier\Shipping\Model\Plugin\Checkout;

use Magento\Framework\Stdlib\ArrayManager;
use SamedayCourier\Shipping\Helper\CacheHelper;
use SamedayCourier\Shipping\Helper\GeneralHelper;

class LayoutProcessor
{
    /**
     * @var ArrayManager $arrayManager
     */
    private $arrayManager;

    /**
     * @var GeneralHelper $generalHelper
     */
    private $generalHelper;

    /**
     * @var CacheHelper
     */
    private $cacheHelper;

    /**
     * @param ArrayManager $arrayManager
     * @param GeneralHelper $generalHelper
     * @param CacheHelper $cacheHelper
     */
    public function __construct(
        ArrayManager $arrayManager,
        GeneralHelper $generalHelper,
        CacheHelper $cacheHelper
    )
    {
        $this->arrayManager = $arrayManager;
        $this->generalHelper = $generalHelper;
        $this->cacheHelper = $cacheHelper;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     *
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ): array
    {
        if (false === $this->generalHelper->useSamedayNomenclature()) {
            return $jsLayout;
        }

        return $this->processCityField($jsLayout);
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    private function processCityField(array $jsLayout): array
    {
        $path = "components/checkout/children/steps/children/shipping-step/children/shippingAddress/children/shipping-address-fieldset/children/city";
        $cityField = $this->arrayManager->get(
            $path,
            $jsLayout
        );

        if (null === $cityField) {
            return $jsLayout;
        }

        $cityField = $this->configureCityFieldAsDropDown($cityField);

        return $this->arrayManager->set($path, $jsLayout, $cityField);
    }

    private function configureCityFieldAsDropDown($cityField): array
    {
        return array_merge(
            $cityField,
            [
                'component' => 'SamedayCourier_Shipping/js/form/element/city-select',
                'config' => array_merge(
                    $cityField['config'] ?? [],
                    [
                        'elementTmpl' => 'ui/form/element/select',
                        'template' => 'ui/form/field',
                        'mode' => 'dropdown',
                        'fallbackToText' => true,
                        'noOptionsMessage' => __('Please select a city.'),
                        'enableTypeAhead' => true,
                        'samedayCities' => $this->cacheHelper->loadData($this->generalHelper::CACHE_CITIES_DATA_KEY)
                    ]
                ),
                'options' => [],
                'validation' => [
                    'required-entry' => true
                ],
                'sortOrder' => '105',
                'imports' => [
                    'regionValue' => '${$.parentName}.region_id:value',
                ]
            ]
        );
    }

    private function configureCityFieldAsInputText(array $jsLayout): array
    {
        return $jsLayout;
    }
}
