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

    const isEasyBoxService = (code) => {
        return ['LN', 'XL'].includes(code);
    };

    const samedayCourierLocker = 'samedaycourier_locker';

    const showLockersMode = {
        'as_map': 'map',
        'as_drop_down': 'drop-down',
    }

    // Create custom isset() Helper
    const isset = (accessor) => {
        try {
            return accessor() !== undefined && accessor() !== null
        } catch (e) {
            return false;
        }
    }

    // Get current selected locker from cookie.
    const getCookie = (key) => {
        let cookie = '';
        document.cookie.split(';').forEach(function (value) {
            if (value.split('=')[0].trim() === key) {
                return cookie = value.split('=')[1];
            }
        });

        return cookie;
    }

    // Store lockerId into cookie.
    const setCookie = (key, value) => {
        document.cookie = `${key}=` + value + "; Path=/; Expires=Tue, 19 Jan 2038 03:14:07 UTC;";
    }

    // Re-init Collect Rate after change the shipping method:
    $(document, 'select[name="region_id"]').on('change', function() {
        let address = quote.shippingAddress();

        rateReg.set(address.getKey(), null);
        rateReg.set(address.getCacheKey(), null);

        quote.shippingAddress(address);
    });

    $(document).on('click', '#showLockerMap', (element) => {
        const LockerPlugin = window['LockerPlugin'];

        const lockerMapElement = element.target.dataset;

        let countryCode = lockerMapElement.country_code;
        let destCountry = countryCode.toUpperCase();
        let destCity = lockerMapElement.dest_city;
        let apiUsername = lockerMapElement.api_username;

        const lockerPluginInit = {
            'clientId': 'b8cb2ee3-41b9-4c3d-aafe-1527b453d65e',
            'countryCode': destCountry,
            'city': destCity,
            'langCode': countryCode,
            'apiUsername': apiUsername,
        }

        LockerPlugin.init(lockerPluginInit);

        if (
            LockerPlugin.options.countryCode !== destCountry
            || LockerPlugin.options.city !== destCity
        ) {
            lockerPluginInit.countryCode = destCountry;
            lockerPluginInit.city = destCity;

            LockerPlugin.reinitializePlugin(lockerPluginInit);
        }

        const pluginInstance = LockerPlugin.getInstance();
        pluginInstance.open();

        pluginInstance.subscribe((locker) => {
            setCookie(samedayCourierLocker, JSON.stringify(locker));
            $('#lockerDetails').html(showLockerDetails());

            pluginInstance.close();
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

    // if already exists a locker selected get details about it from cookie:
    const showLockerDetails = () => {
        if ('' !== getCookie(samedayCourierLocker)) {
            let locker = JSON.parse(getCookie(samedayCourierLocker));

            if (undefined !== locker.name) {
                return `${locker.name} ${locker.address} ${locker.city} (${locker.county})`;
            }
        }

        return null;
    }

    let viewModel = {}

    viewModel.lockersList = ko.observableArray(getLockerList());
    viewModel.selectedLocker = ko.observable(getCookie(samedayCourierLocker)); // Put default value here
    viewModel.lockerDetails = ko.observable(showLockerDetails());

    return Component.extend({
        defaults: {
            template: 'SamedayCourier_Shipping/checkout/shipping/lockers-map-template-block'
        },

        initObservable: function () {
            this.showLockersAs = ko.computed(() => {
                let method = quote.shippingMethod();

                if (null !== method) {
                    if (isEasyBoxService(method.method_code)) {
                        if (method.extension_attributes.show_lockers_map === true) {
                            return showLockersMode.as_map;
                        } else {
                            return showLockersMode.as_drop_down;
                        }
                    }
                }

                return null;
            }, this);

            this.getCountryCode = ko.computed(() => {
                let method = quote.shippingMethod();
                if (null !== method && isset(() => method.extension_attributes.country_code)) {
                    return method.extension_attributes.country_code;
                }

                return null;
            }, this);

            this.getCity = ko.computed(() => {
                let method = quote.shippingMethod();
                if (null !== method && isset(() => method.extension_attributes.dest_city)) {
                    return method.extension_attributes.dest_city;
                }

                return null;
            }, this);

            this.getApiUsername = ko.computed(() => {
                let method = quote.shippingMethod();
                if (null !== method && isset(() => method.extension_attributes.api_username)) {
                    return method.extension_attributes.api_username;
                }

                return null;
            }, this);

            this.lockersList = viewModel.lockersList;
            this.selectedLocker = viewModel.selectedLocker;
            this.lockerDetails = viewModel.lockerDetails;

            this.onLockerChange = (object, event) => {
                if (event.originalEvent) {

                    setCookie(samedayCourierLocker, object.selectedLocker._latestValue);
                }
            };

            return this;
        },
    });
});
