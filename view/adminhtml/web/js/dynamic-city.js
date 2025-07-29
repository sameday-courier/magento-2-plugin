require([
        'jquery',
        'mage/url',
    ],
    ($, mageUrl) => {
        $(document).ready(() => {
            setTimeout(() => {
                let countySelect = $('[name="pickuppoint\\[countyId\\]"]');
                countySelect.on('change', (e) => {
                    console.log(countySelect);
                    //refreshCity(e.target.value);
                });
            }, 1000);

            // SamedayCities[RO][idJudet]

            const refreshCity = (countyId) => {
                const citySelect = $('[name="pickuppoint\\[cityId\\]"]');

                $.ajax({
                    showLoader: true,
                    url: $('[name="pickuppoint\\[url\\]"]').val(),
                    data: {
                        form_key: window.FORM_KEY,
                        countyId: countyId,
                    },
                    type: "POST",
                    dataType: 'json',
                    success: (response) => {
                        citySelect.empty();
                        citySelect.append($('<option>', { value: '', text: 'Select City' }));
                        for (let city of response) {
                            citySelect.append($('<option>', { value: city.value, text: city.label }));
                            citySelect.focus();
                        }
                    }
                });
            }
        });
    }
);
