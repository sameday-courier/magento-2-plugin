require(
    [
        'jquery',
    ],
    ($) => {
        $(document).ready(() => {
            const addParcelForm = $('#add_parcel_form');

            addParcelForm.append($('<input>', {
                'name': 'form_key',
                'value': window.FORM_KEY,
                'type': 'hidden',
            }));

            $("#addNewParcel").on("click", () => {
                addParcelForm.submit();
            });
        });
    }
);
