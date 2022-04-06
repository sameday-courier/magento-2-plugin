define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'mage/url',
    'lockersPluginSdk',
], function ($, ko, Component, quote, rateReg, url) {
    'use strict';

    const easyBoxService = 'samedaycourier_15';

    const showLockersMode = {
        'as_map': 'map',
        'as_drop_down': 'drop-down',
    }

    // Get current selected locker from cookie.
    const getCookie = () => {
        let lockerId = '';
        document.cookie.split(';').forEach(function (value) {
            if (value.indexOf('samedaycourier_locker_id') > 0) {
                lockerId = value.split('=')[1];
            }
        });

        return lockerId;
    }

    // Store lockerId into cookie.
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

        const lockerMapElement = $('#showLockerMap');
        const lockerPluginInit = {
            clientId: 'b8cb2ee3-41b9-4c3d-aafe-1527b453d65e',
            countryCode: lockerMapElement.data('country_code').toUpperCase(),
            langCode: lockerMapElement.data('country_code'),
        }

        window.LockerPlugin.init(lockerPluginInit);
        let plugin = window.LockerPlugin.getInstance();
        plugin.open();

        plugin.subscribe((locker) => {
           setCookie(locker.lockerId);

           plugin.close();
        });
    });

    // get the list of imported lockers:
    const getLockerList = () => {
        let lockers = null;

        url.setBaseUrl(BASE_URL);
        $.ajax({
            showLoader: true,
            url: url.build('samedaycourier_shipping/frontend/lockers'),
            type: "GET",
            async: false,
        }).done(function (data) {
            lockers = data;
        });

        return lockers;
    }

    let viewModel = {}

    viewModel.lockersList = ko.observableArray(getLockerList());
    viewModel.selectedLocker = ko.observable(getCookie()); // Put default value here

    return Component.extend({
        defaults: {
            template: 'SamedayCourier_Shipping/checkout/shipping/lockers-map-template-block'
        },

        initObservable: function () {
            this.showLockersAs = ko.computed(() => {
                let method = quote.shippingMethod();

                if (null !== method) {
                    let methodName = method.carrier_code + '_' + method.method_code;

                    if (methodName === easyBoxService && method.extension_attributes.show_lockers_map === true) {

                        return showLockersMode.as_map;
                    } else if (methodName === easyBoxService && method.extension_attributes.show_lockers_map === false) {

                        return showLockersMode.as_drop_down;
                    }
                }

                return null;
            }, this);

            this.getCountryCode = ko.computed(() => {
                let method = quote.shippingMethod();
                if (null !== method) {
                    return method.extension_attributes.country_code;
                }

                return 'ro'; // default value always will be ro
            }, this);

            this.lockersList = viewModel.lockersList;
            this.selectedLocker = viewModel.selectedLocker;

            this.onLockerChange = (object, event) => {
                if (event.originalEvent) {

                    setCookie(object.selectedLocker._latestValue);
                }
            };

            return this;
        },


    });
});
