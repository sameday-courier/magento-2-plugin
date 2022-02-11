define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'lockersPluginSdk',
], function ($, ko, Component, quote, rateReg) {
    'use strict';

    const setCookie = (lockerId) => {
        document.cookie = "samedaycourier_locker_id=" + lockerId + "; Path=/; Expires=Tue, 19 Jan 2038 03:14:07 UTC;";
    }

    // Re-init Collect Rate after change the shipping method:
    $(document, 'select[name="region_id"]').on('change', function() {
        let address = quote.shippingAddress();

        rateReg.set(address.getKey(), null);
        rateReg.set(address.getCacheKey(), null);

        quote.shippingAddress(address);
    });

    $(document).on('click', '#showLockerMap', () => {
        window.LockerPlugin.init();
        let plugin = window.LockerPlugin.getInstance();
        plugin.open();

        plugin.subscribe((locker) => {
           setCookie(locker.lockerId);

           plugin.close();
        });
    });

    return Component.extend({
        defaults: {
            template: 'SamedayCourier_Shipping/checkout/shipping/lockers-map-template-block'
        },

        initObservable: function () {
            this.selectedMethod = ko.computed(() => {
                let method = quote.shippingMethod();

                return method != null ? method.carrier_code + '_' + method.method_code : null;
            }, this);

            return this;
        },
    });
});
