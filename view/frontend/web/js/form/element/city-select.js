define([
    'jquery',
    'Magento_Ui/js/form/element/select',
], function ($, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            samedayCities: {},
            fallbackToText: true,
            mode: 'dropdown',
            noOptionsMessage: 'No cities available',
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();

            this.checkForFallback();

            return this;
        },

        checkForFallback: function () {
            let cities = this.samedayCities;

            if (cities.length <= 1 && this.fallbackToText) {
                this.switchToTextInput();
            }
        },

        /**
         * @param {String} regionId
         */
        onRegionValueUpdate: function(regionId) {
            console.log('🔄 Region changed to:', regionId);
        },

        switchToTextInput: function () {
            this.elementTmpl = 'ui/form/element/input';
            this.mode = 'text';
            this.placeholder = this.noOptionsMessage;
            this.template = 'ui/form/field';
        },
    });
});
