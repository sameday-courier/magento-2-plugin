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
            region_id: null,
            country_id: null,
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();

            registry.async(`${this.parentName}.region_id`)(function (regionComponent) {
                if (regionComponent) {
                    regionComponent.value.subscribe(function (newRegion) {
                        this.onRegionChanged(
                            registry.get(`${this.parentName}.country_id`).value(),
                            newRegion,
                        );
                    }.bind(this));
                }
            }.bind(this));

            this.checkForFallback();

            return this;
        },

        onRegionChanged: function (newCountryId, newRegionId) {
            let cities = this.samedayCities?.[newCountryId]?.[newRegionId] ?? [];

            if (cities && cities.length > 0) {
                this.setOptions(cities);
                console.log('Schimba in Drop-down !');
            } else {
                console.log('Schimba in Text !');
                this.switchToTextInput();
            }
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
