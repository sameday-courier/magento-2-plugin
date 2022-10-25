define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'SamedayCourier_Shipping/js/model/lockerId_validator'
    ],
    function (Component, additionalValidators, lockerId_validator) {
        'use strict';
        additionalValidators.registerValidator(lockerId_validator);
        return Component.extend({});
    }
);
