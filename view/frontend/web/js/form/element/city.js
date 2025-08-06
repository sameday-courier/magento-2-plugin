define([
    'Magento_Ui/js/form/element/select',
    'uiRegistry',
], function (Select, registry) {
    'use strict';

    return Select.extend({
        defaults: {
            samedayCities: {},
            fallbackToText: true,
            elementTmpl: 'ui/form/element/select',
            mode: 'dropdown',
            noOptionsMessage: 'No cities available',
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();

            console.log(this.samedayCities);

            registry.async(`${this.parentName}.region_id`)(function (regionComponent) {
                if (regionComponent) {
                    // After component is fully loaded
                    this.onRegionChanged(
                        registry.get(`${this.parentName}.country_id`).value(),
                        registry.get(`${this.parentName}.region_id`).value(),
                    );

                    // After RegionID is changed
                    regionComponent.value.subscribe(function (newRegion) {
                        this.onRegionChanged(
                            registry.get(`${this.parentName}.country_id`).value(),
                            newRegion,
                        );

                        registry.get(this.name, function (component) {
                            if (component) {
                                console.log('component: ', component);
                                component.reload();
                            }
                        });
                    }.bind(this));
                }
            }.bind(this));

            this.checkForFallback();

            return this;
        },

        onRegionChanged: function (newCountryId, newRegionId) {
            let cities = this.samedayCities?.[newCountryId]?.[newRegionId] ?? [];

            if (cities && cities.length > 0) {
                this.elementTmpl = 'ui/form/element/select';
                this.mode = 'dropdown';
                this.template = 'ui/form/field';

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
