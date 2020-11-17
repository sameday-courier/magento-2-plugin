require([
    'jquery',
    'mage/url',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rate-registry',
], function ($, url, mainQuote, rateReg) {
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
        let $select = $('.samedaycourier_locker_container select');

        // Get current selected locker from cookie.
        let lockerId = '';
        document.cookie.split(';').forEach(function (value) {
            if (value.indexOf('samedaycourier_locker_id') > 0) {
                lockerId = value.split('=')[1];
            }
        });

        // If got a current locker, then autoselect it.
        if (lockerId) {
            $select.val(lockerId);
        } else {
            // No current locker, use current default.
            document.cookie = "samedaycourier_locker_id=" + $select.val() + "; Path=/; Expires=Tue, 19 Jan 2038 03:14:07 UTC;";
        }

        $select.change(function () {
            let lockerId = $(this).val();
            if (lockerId) {
                document.cookie = "samedaycourier_locker_id=" + lockerId + "; Path=/; Expires=Tue, 19 Jan 2038 03:14:07 UTC;";
            }
        });
    };

    let removeLockers = function($radio) {
        $('.samedaycourier_locker_container').remove();
    };

    $(document, 'select[name="region_id"]').on('change', function() {
        let address = mainQuote.shippingAddress();

        rateReg.set(address.getKey(), null);
        rateReg.set(address.getCacheKey(), null);

        mainQuote.shippingAddress(address);
    });

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
    });
});
