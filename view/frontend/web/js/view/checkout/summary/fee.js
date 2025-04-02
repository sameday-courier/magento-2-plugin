/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'SamedayCourier_Shipping/checkout/summary/fee',
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,

            isDisplayed: () => {
                if (null === quote.paymentMethod()) {
                    return true;
                }

                return ['cashondelivery', 'checkmo'].includes(quote.paymentMethod().method);
            },

            getTitle: () => {
                return totals.getSegment('fee').title;
            },

            getValue: function() {
                let price = 0;
                if (this.totals()) {
                    price = totals.getSegment('fee').value;
                }

                return this.getFormattedPrice(price);
            },

            getBaseValue: function() {
                let price = 0;
                if (this.totals()) {
                    price = this.totals().base_fee;
                }

                return priceUtils.formatPrice(price, quote.getBasePriceFormat());
            }
        });
    }
);
