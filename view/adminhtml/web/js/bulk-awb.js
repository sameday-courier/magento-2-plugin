define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/modal/confirm'
], function ($, registry, confirm) {
    'use strict';

    return function (config) {
        if (window.samedayBulkAwbInitialized) {
            return;
        }
        window.samedayBulkAwbInitialized = true;

        var pendingBulkOrderIds = [];
        var bulkProcessRunning = false;
        var bulkRunResults = {
            generate: [],
            remove: []
        };

        function getLabels() {
            return config.labels || {};
        }

        function getSelectedOrderIds() {
            var ids = [];
            var seen = {};

            function pushId(id) {
                var value = parseInt(id, 10);
                if (value > 0 && !seen[value]) {
                    seen[value] = true;
                    ids.push(value);
                }
            }

            function collectFromComponent(idsComponent) {
                var selected;
                var excluded;
                var pageIds;
                var i;

                if (!idsComponent) {
                    return;
                }

                if (typeof idsComponent.selected === 'function') {
                    selected = idsComponent.selected() || [];
                } else if ($.isArray(idsComponent.selected)) {
                    selected = idsComponent.selected;
                } else {
                    selected = [];
                }

                for (i = 0; i < selected.length; i++) {
                    pushId(selected[i]);
                }

                // Select-all across pages: selected is empty, excludeMode is true.
                if (!ids.length &&
                    typeof idsComponent.excludeMode === 'function' &&
                    idsComponent.excludeMode()
                ) {
                    excluded = typeof idsComponent.excluded === 'function'
                        ? (idsComponent.excluded() || [])
                        : [];
                    pageIds = typeof idsComponent.getIds === 'function'
                        ? (idsComponent.getIds() || [])
                        : [];

                    for (i = 0; i < pageIds.length; i++) {
                        if (excluded.indexOf(pageIds[i]) === -1 &&
                            excluded.indexOf(String(pageIds[i])) === -1
                        ) {
                            pushId(pageIds[i]);
                        }
                    }
                }
            }

            try {
                collectFromComponent(
                    registry.get('sales_order_grid.sales_order_grid.sales_order_columns.ids')
                );
            } catch (e) {
                // continue
            }

            if (!ids.length) {
                try {
                    (registry.filter('ns = sales_order_grid, index = ids') || [])
                        .forEach(collectFromComponent);
                } catch (e2) {
                    // continue
                }
            }

            if (ids.length) {
                return ids;
            }

            // DOM fallback.
            $('td.data-grid-checkbox-cell input.admin__control-checkbox').each(function () {
                var input = this;
                var value;
                var idAttr;
                var match;

                if (!input.checked) {
                    return;
                }

                value = input.value;
                if (!value || value === 'on') {
                    idAttr = input.id || '';
                    match = idAttr.match(/check(\d+)$/);
                    if (match) {
                        value = match[1];
                    }
                }
                pushId(value);
            });

            return ids;
        }

        function setBulkButtonsDisabled(disabled) {
            ['#samedayBulkGenerateBtn', '#samedayBulkRemoveBtn'].forEach(function (selector) {
                var btn = document.querySelector(selector);
                if (!btn) {
                    return;
                }
                btn.disabled = !!disabled;
                if (disabled) {
                    btn.setAttribute('disabled', 'disabled');
                } else {
                    btn.removeAttribute('disabled');
                }
            });
        }

        function updateToolbarState() {
            setBulkButtonsDisabled(getSelectedOrderIds().length === 0);
        }

        function bindSelectionListeners() {
            registry.get(
                'sales_order_grid.sales_order_grid.sales_order_columns.ids',
                function (idsComponent) {
                    if (!idsComponent || idsComponent._samedayBulkBound) {
                        return;
                    }

                    idsComponent._samedayBulkBound = true;

                    if (idsComponent.selected && typeof idsComponent.selected.subscribe === 'function') {
                        idsComponent.selected.subscribe(updateToolbarState);
                    }
                    if (idsComponent.excluded && typeof idsComponent.excluded.subscribe === 'function') {
                        idsComponent.excluded.subscribe(updateToolbarState);
                    }
                    if (idsComponent.excludeMode && typeof idsComponent.excludeMode.subscribe === 'function') {
                        idsComponent.excludeMode.subscribe(updateToolbarState);
                    }
                    if (idsComponent.totalSelected &&
                        typeof idsComponent.totalSelected.subscribe === 'function'
                    ) {
                        idsComponent.totalSelected.subscribe(updateToolbarState);
                    }

                    updateToolbarState();
                }
            );
        }

        function showModal(modalId) {
            var $modal = $('#' + modalId);
            $modal.addClass('is-open').attr('aria-hidden', 'false').css('display', '');
            if (!$('.sameday-bulk-modal-backdrop').length) {
                $('body').append('<div class="sameday-bulk-modal-backdrop"></div>');
            }
            $('body').addClass('sameday-bulk-modal-open');
        }

        function hideModal(modalId) {
            $('#' + modalId).removeClass('is-open').attr('aria-hidden', 'true').hide();
            if (!$('.sameday-bulk-modal.is-open').length) {
                $('.sameday-bulk-modal-backdrop').remove();
                $('body').removeClass('sameday-bulk-modal-open');
            }
        }

        function fillOrderList(listEl, orderIds) {
            listEl.empty();
            orderIds.forEach(function (orderId) {
                listEl.append($('<li/>').text('#' + orderId));
            });
        }

        function appendLog(logEl, orderId, message, type) {
            logEl.append(
                $('<div/>', {
                    'class': 'sameday-bulk-log-row is-' + type,
                    text: '#' + orderId + ' — ' + message
                })
            );
            logEl.scrollTop(logEl[0].scrollHeight);
        }

        function updateGridCell(orderId, columnClass, html) {
            var $checkbox = $(
                '.data-grid-checkbox-cell input.admin__control-checkbox[data-action="select-row"][value="' + orderId + '"],' +
                '.data-grid-checkbox-cell input.admin__control-checkbox[value="' + orderId + '"]'
            );
            if (!$checkbox.length) {
                return;
            }

            var $row = $checkbox.closest('tr');
            var $cell = $row.find('td.' + columnClass);

            if (!$cell.length) {
                var headerIndex = $('table.data-grid thead th.' + columnClass).index();
                if (headerIndex >= 0) {
                    $cell = $row.children('td').eq(headerIndex);
                }
            }

            if ($cell.length) {
                var content = html || '—';
                if ($cell.find('.data-grid-cell-content').length) {
                    $cell.find('.data-grid-cell-content').html(content);
                } else {
                    $cell.html(content);
                }
            }
        }

        function updateOrderGridData(orderId, fields) {
            registry.get('sales_order_grid.sales_order_grid_data_source', function (source) {
                if (!source || !source.data || !source.data.items) {
                    return;
                }

                var items = source.data.items;
                for (var i = 0; i < items.length; i++) {
                    if (parseInt(items[i].entity_id, 10) !== parseInt(orderId, 10)) {
                        continue;
                    }

                    Object.keys(fields).forEach(function (key) {
                        source.set('data.items.' + i + '.' + key, fields[key]);
                        items[i][key] = fields[key];
                    });
                    break;
                }
            });
        }

        function reloadOrderGrid() {
            registry.get('sales_order_grid.sales_order_grid_data_source', function (source) {
                if (source && typeof source.reload === 'function') {
                    source.reload();
                }
            });
        }

        function updateOrderFeedback(orderId, feedback) {
            var html = feedback || '—';
            updateOrderGridData(orderId, { sameday_feedback: html });
            updateGridCell(orderId, 'col-sameday_feedback', html);
        }

        function updateOrderActions(orderId, actionsHtml) {
            var html = actionsHtml || '';
            updateOrderGridData(orderId, { sameday_actions: html });
            updateGridCell(orderId, 'col-sameday_actions', html);
        }

        function postAction(url, orderId) {
            var data = {
                form_key: config.formKey
            };
            if (orderId) {
                data.order_id = orderId;
            }

            return $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: data
            });
        }

        function buildResultEntry(orderId, data, requestFailed) {
            var labels = getLabels();

            if (requestFailed) {
                return {
                    orderId: orderId,
                    status: labels.statusFailed || 'Failed',
                    message: 'Request failed',
                    awbNumber: ''
                };
            }

            if (data.skipped) {
                return {
                    orderId: orderId,
                    status: labels.statusSkipped || 'Skipped',
                    message: data.message || 'Skipped',
                    awbNumber: data.awb_number || ''
                };
            }

            if (data.success) {
                return {
                    orderId: orderId,
                    status: labels.statusSuccess || 'Success',
                    message: data.message || 'OK',
                    awbNumber: data.awb_number || ''
                };
            }

            return {
                orderId: orderId,
                status: labels.statusFailed || 'Failed',
                message: data.error || 'Error',
                awbNumber: ''
            };
        }

        function isSuccessfulResult(entry, labels) {
            return entry.status === labels.statusSuccess || entry.status === labels.statusSkipped;
        }

        function updateResultsSummary(modalConfig, results) {
            var labels = getLabels();
            var successful = 0;
            var failed = 0;

            results.forEach(function (entry) {
                if (isSuccessfulResult(entry, labels)) {
                    successful += 1;
                } else {
                    failed += 1;
                }
            });

            modalConfig.processedEl.text(String(results.length));
            modalConfig.successEl.text(String(successful));
            modalConfig.failedEl.text(String(failed));
            modalConfig.resultsEl.show();
        }

        function escapeCsvValue(value) {
            var text = String(value == null ? '' : value);
            if (/[",\n\r]/.test(text)) {
                return '"' + text.replace(/"/g, '""') + '"';
            }
            return text;
        }

        function downloadResultsCsv(resultsKey, actionPrefix) {
            var results = bulkRunResults[resultsKey] || [];
            if (!results.length) {
                return;
            }

            var labels = getLabels();
            var lines = [[
                labels.csvOrderId || 'Order ID',
                labels.csvStatus || 'Status',
                labels.csvMessage || 'Message',
                labels.csvAwb || 'AWB Number'
            ].map(escapeCsvValue).join(',')];

            results.forEach(function (entry) {
                lines.push([
                    entry.orderId,
                    entry.status,
                    entry.message,
                    entry.awbNumber
                ].map(escapeCsvValue).join(','));
            });

            var blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
            var link = document.createElement('a');
            var timestamp = new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-');
            link.href = URL.createObjectURL(blob);
            link.download = 'sameday-bulk-' + actionPrefix + '-' + timestamp + '.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        }

        function runSequential(orderIds, url, modalConfig, options) {
            if (bulkProcessRunning) {
                return;
            }

            var total = orderIds.length;
            var processed = 0;
            var resultsKey = options.resultsKey;
            var updateFeedback = !!options.updateFeedback;

            bulkProcessRunning = true;
            bulkRunResults[resultsKey] = [];
            modalConfig.confirmEl.hide();
            modalConfig.progressEl.show();
            modalConfig.footerConfirmEl.hide();
            modalConfig.logEl.empty();
            modalConfig.resultsEl.hide();
            modalConfig.processedEl.text('0');
            modalConfig.successEl.text('0');
            modalConfig.failedEl.text('0');
            modalConfig.percentEl.text('0%');
            modalConfig.barEl.css('width', '0%');
            modalConfig.footerDoneEl.hide();
            $('#samedayBulkGenerateProcess, #samedayBulkRemoveProcess').prop('disabled', true);

            function processNext(index) {
                if (index >= total) {
                    bulkProcessRunning = false;
                    updateResultsSummary(modalConfig, bulkRunResults[resultsKey]);
                    modalConfig.footerDoneEl.show();

                    if (updateFeedback) {
                        // Ensure Feedback/Actions columns show persisted AWB data from the server.
                        reloadOrderGrid();
                    }

                    return;
                }

                var orderId = orderIds[index];
                postAction(url, orderId)
                    .done(function (data) {
                        var entry = buildResultEntry(orderId, data || {}, false);
                        bulkRunResults[resultsKey].push(entry);

                        var type = data && data.success ? 'success' : (data && data.skipped ? 'info' : 'error');
                        var message = entry.message;
                        if (entry.awbNumber) {
                            message += ' (' + entry.awbNumber + ')';
                        }
                        appendLog(modalConfig.logEl, orderId, message, type);

                        if (updateFeedback) {
                            var feedbackHtml = (data && data.feedback)
                                ? data.feedback
                                : (entry.awbNumber
                                    ? '<span class="sameday-awb-badge">' + entry.awbNumber + '</span>'
                                    : '<span class="sameday-feedback-error">' + (entry.message || 'Error') + '</span>');
                            updateOrderFeedback(orderId, feedbackHtml);
                        }

                        if (data && typeof data.actions_html !== 'undefined') {
                            updateOrderActions(orderId, data.actions_html);
                        } else if (updateFeedback && entry.awbNumber) {
                            // Actions will be corrected by grid reload at the end.
                        }
                    })
                    .fail(function () {
                        var entry = buildResultEntry(orderId, {}, true);
                        bulkRunResults[resultsKey].push(entry);
                        appendLog(modalConfig.logEl, orderId, entry.message, 'error');
                        if (updateFeedback) {
                            updateOrderFeedback(
                                orderId,
                                '<span class="sameday-feedback-error">' + entry.message + '</span>'
                            );
                        }
                    })
                    .always(function () {
                        processed += 1;
                        var percent = Math.round((processed / total) * 100);
                        modalConfig.percentEl.text(percent + '%');
                        modalConfig.barEl.css('width', percent + '%');
                        updateResultsSummary(modalConfig, bulkRunResults[resultsKey]);
                        processNext(index + 1);
                    });
            }

            processNext(0);
        }

        function resetGenerateModal() {
            $('#samedayBulkGenerateAgree').prop('checked', false);
            $('#samedayBulkGenerateProcess').prop('disabled', true);
            $('#samedayBulkGenerateConfirm').show();
            $('#samedayBulkGenerateProgress').hide();
            $('#samedayBulkGenerateFooterConfirm').show();
            $('#samedayBulkGenerateFooterDone').hide();
            $('#samedayBulkGenerateLog').empty();
            $('#samedayBulkGenerateResults').hide();
            $('#samedayBulkGenerateBar').css('width', '0%');
            $('#samedayBulkGeneratePercent').text('0%');
        }

        function resetRemoveModal() {
            $('#samedayBulkRemoveAgree').prop('checked', false);
            $('#samedayBulkRemoveProcess').prop('disabled', true);
            $('#samedayBulkRemoveConfirm').show();
            $('#samedayBulkRemoveProgress').hide();
            $('#samedayBulkRemoveFooterConfirm').show();
            $('#samedayBulkRemoveFooterDone').hide();
            $('#samedayBulkRemoveLog').empty();
            $('#samedayBulkRemoveResults').hide();
            $('#samedayBulkRemoveBar').css('width', '0%');
            $('#samedayBulkRemovePercent').text('0%');
        }

        function mountToolbar() {
            // Toolbar is already rendered above the orders grid via layout.
            $('#sameday-bulk-awb-toolbar').data('mounted', 1);
        }

        $(document).on('click', '.sameday-grid-remove-awb', function (event) {
            event.preventDefault();
            event.stopPropagation();

            var $link = $(this);
            var orderId = parseInt($link.data('order-id'), 10);

            confirm({
                title: $.mage.__('Remove awb confirmation'),
                content: $.mage.__('Are you sure you want to remove this awb?'),
                actions: {
                    confirm: function () {
                        postAction(config.removeUrl, orderId)
                            .done(function (data) {
                                if (data && data.success) {
                                    if (typeof data.feedback !== 'undefined') {
                                        updateOrderFeedback(orderId, data.feedback);
                                    } else {
                                        updateOrderFeedback(orderId, '—');
                                    }
                                    if (data.actions_html) {
                                        updateOrderActions(orderId, data.actions_html);
                                    } else {
                                        reloadOrderGrid();
                                    }
                                } else {
                                    alert((data && data.error) || 'Could not remove AWB.');
                                }
                            })
                            .fail(function () {
                                alert('Request failed');
                            });
                    }
                }
            });
        });

        $(document).on(
            'change click',
            'input.admin__control-checkbox[data-action="select-row"],' +
            '.data-grid-checkbox-cell input.admin__control-checkbox,' +
            '.data-grid-multicheck-cell input.admin__control-checkbox,' +
            '.admin__data-grid-action-multicheck-toggle,' +
            '.admin__data-grid-action-multicheck-menu .action-menu-item',
            function () {
                setTimeout(updateToolbarState, 0);
                setTimeout(updateToolbarState, 50);
                setTimeout(updateToolbarState, 200);
            }
        );

        // Capture-phase: Magento KO may stopPropagation before bubble handlers run.
        document.addEventListener('click', function (event) {
            var target = event.target;
            if (!target || !target.closest) {
                return;
            }
            if (target.closest('.data-grid-checkbox-cell') ||
                target.closest('.data-grid-multicheck-cell') ||
                target.closest('.admin__data-grid-action-multicheck')
            ) {
                setTimeout(updateToolbarState, 0);
                setTimeout(updateToolbarState, 100);
            }
        }, true);

        $(document).on('click', '[data-sameday-close]', function () {
            hideModal($(this).data('sameday-close'));
        });

        $(document).on('click', '#samedayBulkGenerateBtn', function () {
            if ($(this).prop('disabled')) {
                return;
            }
            pendingBulkOrderIds = getSelectedOrderIds();
            if (!pendingBulkOrderIds.length) {
                alert(getLabels().noSelection || 'Please select at least one order.');
                return;
            }
            resetGenerateModal();
            fillOrderList($('#samedayBulkGenerateOrderList'), pendingBulkOrderIds);
            showModal('samedayBulkGenerateModal');
        });

        $(document).on('click', '#samedayBulkRemoveBtn', function () {
            if ($(this).prop('disabled')) {
                return;
            }
            pendingBulkOrderIds = getSelectedOrderIds();
            if (!pendingBulkOrderIds.length) {
                alert(getLabels().noSelection || 'Please select at least one order.');
                return;
            }
            resetRemoveModal();
            fillOrderList($('#samedayBulkRemoveOrderList'), pendingBulkOrderIds);
            showModal('samedayBulkRemoveModal');
        });

        $(document).on('change', '#samedayBulkGenerateAgree', function () {
            $('#samedayBulkGenerateProcess').prop('disabled', !this.checked);
        });

        $(document).on('change', '#samedayBulkRemoveAgree', function () {
            $('#samedayBulkRemoveProcess').prop('disabled', !this.checked);
        });

        $(document).on('click', '#samedayBulkGenerateProcess', function () {
            if (bulkProcessRunning || !pendingBulkOrderIds.length) {
                return;
            }
            runSequential(pendingBulkOrderIds.slice(), config.generateUrl, {
                confirmEl: $('#samedayBulkGenerateConfirm'),
                progressEl: $('#samedayBulkGenerateProgress'),
                footerConfirmEl: $('#samedayBulkGenerateFooterConfirm'),
                footerDoneEl: $('#samedayBulkGenerateFooterDone'),
                logEl: $('#samedayBulkGenerateLog'),
                resultsEl: $('#samedayBulkGenerateResults'),
                processedEl: $('#samedayBulkGenerateCountProcessed'),
                successEl: $('#samedayBulkGenerateCountSuccess'),
                failedEl: $('#samedayBulkGenerateCountFailed'),
                percentEl: $('#samedayBulkGeneratePercent'),
                barEl: $('#samedayBulkGenerateBar')
            }, {
                resultsKey: 'generate',
                updateFeedback: true
            });
        });

        $(document).on('click', '#samedayBulkRemoveProcess', function () {
            if (bulkProcessRunning || !pendingBulkOrderIds.length) {
                return;
            }
            runSequential(pendingBulkOrderIds.slice(), config.removeUrl, {
                confirmEl: $('#samedayBulkRemoveConfirm'),
                progressEl: $('#samedayBulkRemoveProgress'),
                footerConfirmEl: $('#samedayBulkRemoveFooterConfirm'),
                footerDoneEl: $('#samedayBulkRemoveFooterDone'),
                logEl: $('#samedayBulkRemoveLog'),
                resultsEl: $('#samedayBulkRemoveResults'),
                processedEl: $('#samedayBulkRemoveCountProcessed'),
                successEl: $('#samedayBulkRemoveCountSuccess'),
                failedEl: $('#samedayBulkRemoveCountFailed'),
                percentEl: $('#samedayBulkRemovePercent'),
                barEl: $('#samedayBulkRemoveBar')
            }, {
                resultsKey: 'remove',
                updateFeedback: true
            });
        });

        $(document).on('click', '#samedayBulkGenerateDownloadCsv', function () {
            downloadResultsCsv('generate', 'generate');
        });

        $(document).on('click', '#samedayBulkClearErrorsBtn', function () {
            if (!window.confirm(getLabels().clearConfirm || 'Clear errors?')) {
                return;
            }

            postAction(config.clearErrorsUrl)
                .done(function (data) {
                    if (!data || !data.success) {
                        alert((data && data.error) || 'Failed');
                        return;
                    }
                    (data.order_ids || []).forEach(function (orderId) {
                        updateOrderFeedback(orderId, '—');
                    });
                })
                .fail(function () {
                    alert('Request failed');
                });
        });

        var attempts = 0;
        var mountTimer = window.setInterval(function () {
            attempts += 1;
            mountToolbar();
            bindSelectionListeners();
            updateToolbarState();
            if (($('#sameday-bulk-awb-toolbar').data('mounted') === 1 && attempts > 8) || attempts >= 80) {
                window.clearInterval(mountTimer);
            }
        }, 250);

        // Keep buttons in sync even if KO events are missed.
        window.setInterval(updateToolbarState, 300);

        $('#sameday-bulk-awb-toolbar').attr('data-sameday-ready', '1');

        // Keep modals out of the content flow; always start hidden.
        $('#samedayBulkGenerateModal, #samedayBulkRemoveModal')
            .removeClass('is-open')
            .attr('aria-hidden', 'true')
            .hide()
            .appendTo('body');

        bindSelectionListeners();
        updateToolbarState();
        mountToolbar();
    };
});
