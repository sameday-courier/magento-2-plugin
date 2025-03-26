require(
    [
        'jquery',
        'lockersPluginSdk',
        'select2'
    ],
    ($) => {
        $('#pickup_point').select2({
            selectOnClose: true
        });

        document.getElementById('changeLocker').addEventListener('click', (el) => {
            const lockerPluginInit = {
                clientId: 'b8cb2ee3-41b9-4c3d-aafe-1527b453d65e',
                countryCode: el.target.getAttribute('data-country_code').toUpperCase(),
                langCode: el.target.getAttribute('data-country_code'),
                apiUsername: el.target.getAttribute('data-api_username'),
                city: el.target.getAttribute('data-city'),
            }

            window['LockerPlugin'].init(lockerPluginInit);
            let plugin = window['LockerPlugin'].getInstance();

            plugin.open();

            plugin.subscribe((locker) => {
                $.ajax({
                    showLoader: true,
                    url: document.getElementById('changeLockerUrl').getAttribute('data-change_locker_url'),
                    data: {
                        'locker': locker,
                    },
                    type: "POST",
                    dataType: 'json'
                }).done(function (data) {
                    if (data.success) {
                        $('#lockerId_details').val(`${locker.name} ${locker.address}`);
                    }

                    plugin.close();
                });
            });
        });

        // Change service:
        $(document).on('change', '#service', (element) => {
            const _target = element.target;
            const currentService = _target.options[_target.selectedIndex];
            const showLockerFirstMile = document.getElementById('showLockerFirstMile');
            const showLockerDetails = document.getElementById('showLockerDetails');
            const lockerFirstMileElem = document.getElementById('lockerFirstMile');

            /* Uncheck Locker FirstMile Element */
            lockerFirstMileElem.checked = false;

            /* Toggle Element */
            showLockerDetails.style.display = currentService.getAttribute('data-service_locker_to_show');
            showLockerFirstMile.style.display = currentService.getAttribute('data-service_eligible_locker_first_mile');
        });

        // Add new many parcels
        $(document).on('change', '#packages', (element) => {
            let nrOfPackages = parseInt(element.target.value);
            let nrOfFields = document.getElementsByClassName('package_dimensions_fields').length;

            if (nrOfPackages > nrOfFields) {
                $(".package_dimensions_fields").last().clone().appendTo("#package_dimensions");
            } else {
                $(".package_dimensions_fields").last().remove();
            }
        });

        $('#sameday-add-awb-form').append($('<input>', {
            'name': 'form_key',
            'value': window.FORM_KEY,
            'type': 'hidden'
        }));
        $(document).on('click', '#sameday-add-awb-form-submit', (e) => {
            e.preventDefault();

            $('#sameday-add-awb-form').submit();
        });
    }
);
