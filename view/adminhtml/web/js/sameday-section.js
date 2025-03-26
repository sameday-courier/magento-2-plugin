require(
    [
        'jquery',
        'Magento_Ui/js/modal/confirm'
    ],
    ($, confirm) => {
        const removeAwb = document.getElementById('remove_awb');
        if (null !== removeAwb) {
            removeAwb.addEventListener("click", () => {
                confirm({
                    title: $.mage.__('Remove awb confirmation'),
                    content: $.mage.__('Are you sure you want to remove this awb?'),
                    actions: {
                        confirm: function() {
                            let param = {
                                "form_key": window.FORM_KEY,
                                "awb_id": removeAwb.getAttribute("data-awb_id"),
                                "sameday_awb_number": removeAwb.getAttribute("data-sameday_awb_number")
                            }

                            $.ajax({
                                showLoader: true,
                                url: removeAwb.getAttribute("data-remove_awb_url"),
                                data: param,
                                type: "POST",
                                dataType: 'json'
                            }).done(() => {
                                location.reload();
                            });
                        }
                    }
                });
            });
        }
    }
);
