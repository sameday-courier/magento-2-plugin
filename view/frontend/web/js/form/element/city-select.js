define([
    'Magento_Ui/js/form/element/select',
    'uiRegistry',
], function (Select, registry) {
    'use strict';

    return Select.extend({
        defaults: {
            samedayCities: {},
            fallbackToText: true,
            mode: 'dropdown',
            noOptionsMessage: 'No cities available',
            regionId: null,
            listens: {
                'regionId': 'onRegionChanged'
            }
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();

            console.log(this);

            this.setOptions(
                [
                    {value: '123', 'label': 'Sector 777'},
                    {value: '123', 'label': 'Sector 2'},
                    {value: '1234', 'label': 'Sector 55'}
                ]
            );

            registry.async('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id')(function (regionComponent) {
                if (regionComponent) {
                    regionComponent.value.subscribe(function (value) {
                        console.log(registry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.country_id').value);
                        this.onRegionChanged(value);
                    }.bind(this));
                }
            }.bind(this));

            this.checkForFallback();

            return this;
        },

        initObservable: function () {
            this._super().observe(['regionId']);

            return this;
        },

        onRegionChanged: function (newRegionId) {
            console.log(newRegionId);
        },

        checkForFallback: function () {
            let cities = this.samedayCities;

            if (cities.length <= 1 && this.fallbackToText) {
                this.switchToTextInput();
            }
        },

        switchToTextInput: function () {
            this.elementTmpl = 'ui/form/element/input';
            this.mode = 'text';
            this.placeholder = this.noOptionsMessage;
            this.template = 'ui/form/field';
        },
    });
});
