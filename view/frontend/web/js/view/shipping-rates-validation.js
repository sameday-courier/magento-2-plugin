/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    'SamedayCourier_Shipping/js/model/shipping-rates-validator',
    'SamedayCourier_Shipping/js/model/shipping-rates-validation-rules'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    samedayCourierShippingRatesValidator,
    samedayCourierShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('samedaycourier', samedayCourierShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('samedaycourier', samedayCourierShippingRatesValidationRules);

    return Component;
});
