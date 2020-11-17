require([
    'jquery',
    'mage/url'
], function ($, url) {
    let lockers = null;
    let getLockers = function() {
        if (lockers) {
            return lockers;
        }

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

    let addLockers = function ($radio) {
        $radio.closest('tr').after(getLockers());
        console.log('ADD', $radio);
        document.cookie = "samedaycourier_locker_id=12283; path=/";
    };

    let removeLockers = function($radio) {
        $radio.closest('tr').next().remove();
        console.log('REMOVE', $radio);
    };

    $(function () {
        setInterval(function () {
            $('#checkout-step-shipping_method input[type=radio]').each(function () {
                let $this = $(this);

                if ($this.val().indexOf('samedaycourier_') === 0) {
                    // A sameday courier shipping method, check if locker method.
                    let $label = $this.closest('tr').find('[id^="label_method_"]');
                    let labelText = $label.text();
                    if (labelText.indexOf('*') === 0) {
                        // Shipping method is sameday locker, update name and mark as not rendered.
                        $label.text(labelText.substring(1));
                        $this.attr('samedaycourier_locker_rendered', 'false');
                    }
                }

                if ($this.is(':checked') && $this.attr('samedaycourier_locker_rendered') === 'false') {
                    addLockers($this);
                    $this.attr('samedaycourier_locker_rendered', 'true');
                } else if (!$this.is(':checked') && $this.attr('samedaycourier_locker_rendered') === 'true') {
                    removeLockers($this);
                    $this.attr('samedaycourier_locker_rendered', 'false');
                }
            });
        }, 500);

/*        $(document, '#checkout-step-shipping_method input[type=radio]').on('change', function (el) {
            let $this = $(el.target);

            let old = $('#checkout-step-shipping_method input[type=radio][samedaycourier_locker_rendered="true"]');
            if (old.length) {
                console.log('REMOVE', old);
                old.attr('samedaycourier_locker_rendered', "false");
            }

            if ($this.attr('samedaycourier_locker_rendered') === undefined || ($this.is(':checked') && $this.attr('samedaycourier_locker_rendered') !== "false")) {
                return;
            }

            $this.attr('samedaycourier_locker_rendered', "true");

            console.log('ADD', $this);
        });*/

        /*setInterval(function () {
            $('#checkout-step-shipping_method input[type=radio][value^="samedaycourier_"]').each(function () {
                let $this = $(this);
                let $label = $this.closest('tr').find('[id^="label_method_"]');
                let labelText = $label.text();
                if (labelText.indexOf('*') !== 0) {
                    return;
                }

                $label.text(labelText.substring(1));
                $this.attr('samedaycourier_locker_rendered', "false");
            });
        }, 500);

        $(document, '#checkout-step-shipping_method input[type=radio]').on('change', function (el) {
            let $this = $(el.target);

            let old = $('#checkout-step-shipping_method input[type=radio][samedaycourier_locker_rendered="true"]');
            if (old.length) {
                console.log('REMOVE', old);
                old.attr('samedaycourier_locker_rendered', "false");
            }

            if ($this.attr('samedaycourier_locker_rendered') === undefined || ($this.is(':checked') && $this.attr('samedaycourier_locker_rendered') !== "false")) {
                return;
            }

            $this.attr('samedaycourier_locker_rendered', "true");

            console.log('ADD', $this);
        });*/
    });
});
