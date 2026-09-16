require(
    [
        'jquery',
        'Magento_Ui/js/modal/confirm'
    ],
    ($, confirm) => {
        'use strict';

        function updateOrderStatusLabel(label) {
            if (!label) {
                return;
            }

            var selectors = [
                '.order-status',
                '.page-title-wrapper .order-status',
                '.order-information .order-status',
                '[data-ui-id="order-status"]'
            ];

            selectors.forEach(function (selector) {
                $(selector).each(function () {
                    $(this).text(label);
                });
            });
        }

        function showGenerateButton() {
            var $section = $('#sameday-order-section');
            var generateUrl = $section.data('generate-url');
            var $content = $('#sameday-order-section-content');

            if (!$content.length || !generateUrl) {
                location.reload();
                return;
            }

            var label = $.mage.__('Generate Sameday AWB');
            $content.html(
                '<button class="edit sameday-primary" type="button" id="sameday_generate_awb">' +
                $('<div/>').text(label).html() +
                '</button>'
            );
            $('#sameday_generate_awb').on('click', function () {
                setLocation(generateUrl);
            });
        }

        function bindRemoveAwb() {
            var removeAwb = document.getElementById('remove_awb');
            if (null === removeAwb) {
                return;
            }

            removeAwb.addEventListener('click', function () {
                confirm({
                    title: $.mage.__('Remove awb confirmation'),
                    content: $.mage.__('Are you sure you want to remove this awb?'),
                    actions: {
                        confirm: function () {
                            var param = {
                                form_key: window.FORM_KEY,
                                awb_id: removeAwb.getAttribute('data-awb_id'),
                                sameday_awb_number: removeAwb.getAttribute('data-sameday_awb_number'),
                                isAjax: true
                            };

                            $.ajax({
                                showLoader: true,
                                url: removeAwb.getAttribute('data-remove_awb_url'),
                                data: param,
                                type: 'POST',
                                dataType: 'json'
                            }).done(function (data) {
                                if (data && data.order_status_label) {
                                    updateOrderStatusLabel(data.order_status_label);
                                }
                                if (data && data.success) {
                                    showGenerateButton();
                                } else {
                                    location.reload();
                                }
                            }).fail(function () {
                                location.reload();
                            });
                        }
                    }
                });
            });
        }

        bindRemoveAwb();
    }
);
