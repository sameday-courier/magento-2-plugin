<?php

namespace SamedayCourier\Shipping\Model\Plugin\Checkout;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\ArrayManager;
use SamedayCourier\Shipping\Helper\CacheHelper;
use SamedayCourier\Shipping\Helper\GeneralHelper;
use SamedayCourier\Shipping\Helper\SamedayCitiesHelper;

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
     * @var SamedayCitiesHelper $samedayCitiesHelper
     */
    private $samedayCitiesHelper;

    /**
     * @param ArrayManager $arrayManager
     * @param GeneralHelper $generalHelper
     * @param SamedayCitiesHelper $samedayCitiesHelper
     */
    public function __construct(
        ArrayManager $arrayManager,
        GeneralHelper $generalHelper,
        SamedayCitiesHelper $samedayCitiesHelper
    )
    {
        $this->arrayManager = $arrayManager;
        $this->generalHelper = $generalHelper;
        $this->samedayCitiesHelper = $samedayCitiesHelper;
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

    /**
     * @throws InputException
     */
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
                        'customScope' => 'shippingAddress',
                        'template' => 'ui/form/field',
                        'mode' => 'dropdown',
                        'fallbackToText' => true,
                        'noOptionsMessage' => __('Please select a city.'),
                        'enableTypeAhead' => true,
                        'samedayCities' => $this->samedayCitiesHelper->getCachedCities()
                    ]
                ),
                'dataScope' => 'shippingAddress.city',
                'provider' => 'checkoutProvider',
                'options' => [],
                'validation' => [
                    'required-entry' => true
                ],
                'sortOrder' => '105',
                'imports' => [
                    'regionId' => 'shippingAddress.region_id',
                ],
                'visible' => true,
            ]
        );
    }

    private function configureCityFieldAsInputText(array $jsLayout): array
    {
        return $jsLayout;
    }
}
