define(
    [
        'SamedayCourier_Shipping/js/view/checkout/summary/fee',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, totals) {
        'use strict';
        return Component.extend({

            isDisplayed: () => {
                return totals.getSegment('fee').value !== 0;
            }
        });
    }
);
