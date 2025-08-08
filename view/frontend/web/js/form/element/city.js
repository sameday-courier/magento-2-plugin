define([
    'Magento_Ui/js/form/element/abstract',
    'uiRegistry',
    'knockout',
], function (Abstract, registry, ko) {
    'use strict';

    return Abstract.extend({
        defaults: {
            samedayCities: {},
            optionsList: ko.observableArray([]),
            inputType: ko.observable('input'),
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();

            registry.async(`${this.parentName}.region_id`)(function (regionComponent) {
                if (regionComponent) {
                    this.onRegionChanged(
                        registry.get(`${this.parentName}.country_id`).value(),
                        registry.get(`${this.parentName}.region_id`).value(),
                    );

                    regionComponent.value.subscribe(function (newRegion) {
                        this.onRegionChanged(
                            registry.get(`${this.parentName}.country_id`).value(),
                            newRegion,
                        );
                    }.bind(this));
                }
            }.bind(this));

            return this;
        },

        onRegionChanged: function (newCountryId, newRegionId) {
            let cities = this.samedayCities?.[newCountryId]?.[newRegionId] ?? [];

            if (cities && cities.length > 0) {
                this.inputType('select');
                this.optionsList([{'label': this.placeholder, 'value': ''}].concat(cities));
            } else {
                this.inputType('input');
                this.value('');
            }
        },
    });
});
