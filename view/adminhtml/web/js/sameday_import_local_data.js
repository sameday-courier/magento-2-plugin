require([
        'jquery',
    ],
    ($, messageList) => {
        const _actions = ['importServices', 'importPickupPoints', 'importLockers'];
        const importDataButton = document.getElementById('carriers_samedaycourier_samedayImportLocalData');

        const _is_set = (accessor) => {
            try {
                return accessor() !== undefined && accessor() !== null
            } catch (e) {
                return false
            }
        }

        const _getAttrValues = (element, _attribute_name) => {
            return element.getAttribute(_attribute_name);
        }

        const importData = (_url, _actions) => {
            const _action = _actions.shift();

            if (typeof _action === "undefined") {
                return true;
            }

            doAjaxRequest(_url, _actions, _action);
        }

        const doAjaxRequest = (_url, _actions, _action) => {
            $.ajax({
                showLoader: true,
                url: _url,
                data: {
                    form_key: window.FORM_KEY,
                    action: _action,
                },
                type: "POST",
                dataType: 'json',
                success: () => {
                    importData(_url, _actions);
                },
            });
        }

        $(document).ready(() => {
            if (_is_set(() => importDataButton)) {
                importDataButton.addEventListener('click', (event) => {
                    importData(_getAttrValues(event.target, '0'), _actions);
                });
            }
        });
    }
);



