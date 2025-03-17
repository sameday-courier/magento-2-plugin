define(
[
    'jquery',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
],
($, uiRegistry, quote) => {
    'use strict';

    return function (target) {
        return target.extend({
            initialize: function () {
                this._super();

                const cityComponent = uiRegistry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.city');

                let regionId = quote.shippingAddress().regionId;
                if (undefined === regionId) {
                    cityComponent.setOptions([]);
                }

                quote.shippingAddress.subscribe(function (address) {
                    if (address.regionId && address.regionId !== regionId) {
                        this.loadCity(cityComponent, address.regionId);
                    }
                }.bind(this));

                return this;
            },

            loadCity: (cityComponent, regionId) => {
                console.log(regionId);
                cityComponent.setOptions([{value: '123', 'label': 'Sector 1'}, {value: '123', 'label': 'Sector 2'}]);
            }
        });
    };
});
