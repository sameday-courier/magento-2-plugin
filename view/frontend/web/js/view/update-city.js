define(
[
    'jquery',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'ko'
],
($, uiRegistry, quote, ko) => {
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

                this.regionIdObserver = ko.computed(function() {
                    let address = quote.shippingAddress();

                    return address ? address.regionId : null;
                }.bind(this));

                this.regionIdObserver.subscribe(function (regionId) {
                    if (regionId) {
                        this.loadCity(cityComponent, regionId);
                    }
                }.bind(this));

                return this;
            },

            loadCity: (cityComponent, regionId) => {
                console.log(regionId);
                //cityComponent.setOptions([{value: '123', 'label': 'Sector 1'}, {value: '123', 'label': 'Sector 2'}]);
            }
        });
    };
});
