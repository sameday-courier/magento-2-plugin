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
    ) {
        $this->arrayManager = $arrayManager;
        $this->generalHelper = $generalHelper;
        $this->samedayCitiesHelper = $samedayCitiesHelper;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     *
     * @return array
     * @throws InputException
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ): array {
        if (false === $this->generalHelper->useSamedayNomenclature()) {
            return $jsLayout;
        }

        return $this->processCityField($jsLayout);
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     * @throws InputException
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

        $cityField = $this->configureCityField($cityField);

        return $this->arrayManager->set($path, $jsLayout, $cityField);
    }

    /**
     * @throws InputException
     */
    private function configureCityField($cityField): array
    {
        return array_merge(
            $cityField,
            [
                'component' => 'SamedayCourier_Shipping/js/form/element/city',
                'config' => array_merge(
                    $cityField['config'] ?? [],
                    [
                        'template' => 'SamedayCourier_Shipping/checkout/city/city',
                        'samedayCities' => $this->samedayCitiesHelper->getCachedCities()
                    ]
                ),
                'placeholder' => 'Select a city',
                'sortOrder' => '105',
            ]
        );
    }
}
