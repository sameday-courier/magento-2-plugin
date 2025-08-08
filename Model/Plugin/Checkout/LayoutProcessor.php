<?php

namespace SamedayCourier\Shipping\Model\Plugin\Checkout;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\ArrayManager;
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

        foreach ($this->prepareCitiesFieldsPaths() as $fieldsPath) {
            $jsLayout = $this->processCityField($fieldsPath, $jsLayout);
        }

        return $jsLayout;
    }

    /**
     * @param string $path
     * @param array $jsLayout
     *
     * @return array
     *
     * @throws InputException
     */
    private function processCityField(string $path, array $jsLayout): array
    {
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
                'placeholder' => __('Please select a city'),
                'sortOrder' => '105',
            ]
        );
    }

    /**
     * @return array
     */
    private function prepareCitiesFieldsPaths(): array
    {
        return array_map(
            static function (array $path) {
                return sprintf(
                    '%s/%s/%s',
                    'components/checkout/children/steps/children',
                    $path['step'],
                    $path['name'],
                );
            },
            [
                [
                    'step' => 'shipping-step/children',
                    'name' => 'shippingAddress/children/shipping-address-fieldset/children/city'
                ],
                [
                    'step' => 'billing-step/children',
                    'name' => 'payment/children/payments-list/children/checkmo-form/children/form-fields/children/city'
                ],
            ]
        );
    }
}
