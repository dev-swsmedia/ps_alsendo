{*
* Alsendo - PrestaShop shipping module
*
* @author    Innovation Software
* @copyright 2026 Innovation Software
* @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
*}

<link rel="stylesheet" href="{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}alsendo/views/css/alsendo-admin.css">

<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0">
                    <i class="icon-truck"></i>
                    {l s='Bulk Order Sending' mod='alsendo'}
                </h1>
                <div class="header-buttons">
                    <button id="send-all-btn" type="button" class="btn btn-success">
                        <i class="icon-check"></i> {l s='Send All Orders' mod='alsendo'}
                    </button>
                    <button id="retry-failed-btn" type="button" class="btn btn-warning" disabled>
                        <i class="icon-refresh"></i> {l s='Retry Failed' mod='alsendo'}
                    </button>
                    <button id="download-all-btn" type="button" class="btn btn-info" disabled>
                        <i class="icon-download"></i> {l s='Download All Waybills (ZIP)' mod='alsendo'}
                    </button>
                    <button id="cancel-batch-btn" type="button" class="btn btn-warning">
                        <i class="icon-times"></i> {l s='Cancel Batch' mod='alsendo'}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="icon-list"></i>
                        {l s='Orders Status' mod='alsendo'}
                    </div>
                    <div class="card-body">
                        <div class="stats-row mb-4">
                            <div class="stat-box">
                                <span class="stat-label">{l s='Total Orders' mod='alsendo'}</span>
                                <span class="stat-value" id="total-count">{$total_orders|escape:'html':'UTF-8'}</span>
                            </div>
                            <div class="stat-box">
                                <span class="stat-label">{l s='Pending' mod='alsendo'}</span>
                                <span class="stat-value text-warning" id="pending-count">{$total_orders|escape:'html':'UTF-8'}</span>
                            </div>
                            <div class="stat-box">
                                <span class="stat-label">{l s='Successful' mod='alsendo'}</span>
                                <span class="stat-value text-success" id="success-count">0</span>
                            </div>
                            <div class="stat-box">
                                <span class="stat-label">{l s='Failed' mod='alsendo'}</span>
                                <span class="stat-value text-danger" id="failed-count">0</span>
                            </div>
                        </div>

                        <div class="bulk-actions-bar mb-3" style="display: flex; gap: 10px; align-items: center; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                            <label style="margin: 0; display: flex; align-items: center; gap: 5px;">
                                <input type="checkbox" id="select-all-orders" />
                                {l s='Select All' mod='alsendo'}
                            </label>
                            <span style="color: #666;">|</span>
                            <button type="button" id="remove-selected-btn" class="btn btn-danger btn-sm" disabled>
                                <i class="icon-trash"></i> {l s='Remove Selected' mod='alsendo'}
                            </button>
                            <button type="button" id="refresh-status-btn" class="btn btn-info btn-sm">
                                <i class="icon-refresh"></i> {l s='Refresh Status' mod='alsendo'}
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 40px;"><input type="checkbox" id="header-select-all" /></th>
                                    <th style="width: 100px;">{l s='Order' mod='alsendo'}</th>
                                    <th>{l s='Shipping Address' mod='alsendo'}</th>
                                    <th style="width: 180px;">{l s='Package Template' mod='alsendo'}</th>
                                    <th style="width: 160px;">{l s='Shipping Method' mod='alsendo'}</th>
                                    <th style="width: 140px;">{l s='Package Details' mod='alsendo'}</th>
                                    <th style="width: 120px; text-align: center;">{l s='Status' mod='alsendo'}</th>
                                    <th style="width: 300px;">{l s='Message / Action' mod='alsendo'}</th>
                                </tr>
                                </thead>
                                <tbody id="orders-body">
                                {foreach $orders as $order}
                                    <tr data-order-id="{$order.id_order|escape:'html':'UTF-8'}" class="status-{$order.status|escape:'html':'UTF-8'}" data-already-sent="{if $order.already_sent}1{else}0{/if}" data-template-name="{$order.template_name|escape:'htmlall':'UTF-8'}">
                                        <td style="vertical-align: middle; text-align: center;">
                                            <input type="checkbox" class="order-checkbox" data-order-id="{$order.id_order|escape:'html':'UTF-8'}" />
                                        </td>
                                        <td style="vertical-align: middle;">
                                            <strong>#{$order.reference|escape:'html':'UTF-8'}</strong><br>
                                            <small class="text-muted">ID: {$order.id_order|escape:'html':'UTF-8'}</small>
                                        </td>
                                        <td style="vertical-align: middle; font-size: 0.9em;">
                                            <strong>{$order.address.firstname|escape:'html':'UTF-8'} {$order.address.lastname|escape:'html':'UTF-8'}</strong><br>
                                            {if $order.address.company}{$order.address.company|escape:'html':'UTF-8'}<br>{/if}
                                            {$order.address.address|escape:'html':'UTF-8'}{if $order.address.address2}/ {$order.address.address2|escape:'html':'UTF-8'}{/if}<br>
                                            {$order.address.postcode|escape:'html':'UTF-8'} {$order.address.city|escape:'html':'UTF-8'}<br>
                                            <strong>{$order.address.country|escape:'html':'UTF-8'}</strong>
                                        </td>
                                        <td style="vertical-align: middle; font-size: 0.9em;">
                                            <select class="form-control form-control-sm order-template-select" data-order-id="{$order.id_order|escape:'html':'UTF-8'}" data-default-template-index="{$order.default_template_index|default:-1|escape:'html':'UTF-8'}" style="font-size: 12px;">
                                            </select>
                                        </td>
                                        <td style="vertical-align: middle; font-size: 0.9em;">
                                            {$order.shipping_method|escape:'html':'UTF-8'}
                                        </td>
                                        <td class="package-details-cell" style="vertical-align: middle; font-size: 0.85em;">
                                            W: <strong>{$order.package_details.width|escape:'html':'UTF-8'}</strong>cm | L: <strong>{$order.package_details.length|escape:'html':'UTF-8'}</strong>cm | H: <strong>{$order.package_details.height|escape:'html':'UTF-8'}</strong>cm | Wt: <strong>{$order.package_details.weight|escape:'html':'UTF-8'}</strong>kg
                                        </td>
                                        <td style="vertical-align: middle; text-align: center;">
                                            {if $order.status == 'already_sent'}
                                                <span class="badge bg-info status-badge" style="background-color: #17a2b8; padding: 5px 10px; border-radius: 3px; color: white; font-size: 11px; text-transform: uppercase; font-weight: 600;">
                                                    {l s='ALREADY SENT' mod='alsendo'}
                                                </span>
                                            {elseif $order.status == 'cancelled'}
                                                <span class="badge bg-secondary status-badge" style="background-color: #6c757d; padding: 5px 10px; border-radius: 3px; color: white; font-size: 11px; text-transform: uppercase; font-weight: 600;">
                                                    {l s='CANCELLED' mod='alsendo'}
                                                </span>
                                            {else}
                                                <span class="badge bg-secondary status-badge" style="background-color: #999; padding: 5px 10px; border-radius: 3px; color: white; font-size: 11px; text-transform: uppercase; font-weight: 600;">
                                                    {l s='Pending' mod='alsendo'}
                                                </span>
                                            {/if}
                                        </td>
                                        <td class="action-cell" style="vertical-align: middle; font-size: 0.9em;">
                                            {if $order.error}
                                                <div class="error-message" style="color: #d9534f; font-size: 11px; margin-bottom: 6px;"><strong>{$order.error|escape:'html':'UTF-8'}</strong></div>
                                            {/if}
                                            {if $order.status == 'pending' || $order.status == 'failed' || $order.status == 'cancelled'}
                                                <a href="{$alsendo_order_link|escape:'html':'UTF-8'}&action=showFullForm&id_order={$order.id_order|intval}" target="_blank" class="btn btn-sm btn-primary edit-order-btn" data-order-id="{$order.id_order|escape:'html':'UTF-8'}" title="{l s='Edit order details (opens in new tab)' mod='alsendo'}" style="margin-right: 5px;">
                                                    <i class="icon-pencil"></i> {l s='Edit' mod='alsendo'}
                                                </a>
                                                {if $order.status == 'failed'}
                                                <button type="button" class="btn btn-sm btn-warning retry-single-order" data-order-id="{$order.id_order|escape:'html':'UTF-8'}" title="{l s='Retry this order' mod='alsendo'}">
                                                    <i class="icon-refresh"></i> {l s='Retry' mod='alsendo'}
                                                </button>
                                                {/if}
                                            {/if}
                                            {if $order.has_custom_data}
                                                <span class="badge" style="background-color: #5bc0de; color: white; font-size: 10px; padding: 3px 6px;" title="{l s='This order has customized shipping data' mod='alsendo'}">
                                                    {l s='Customized' mod='alsendo'}
                                                </span>
                                            {/if}
                                        </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4" style="display: flex; gap: 10px;">
                            <a href="index.php?controller=AdminOrders" class="btn btn-default">
                                <i class="icon-arrow-left"></i> {l s='Back to Orders' mod='alsendo'}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}alsendo/views/js/admin/alsendo-modal.js?v={$smarty.now|escape:'html':'UTF-8'}"></script>
<script>
    {literal}
    (function() {
        'use strict';

        const batchId = {/literal}{$batch_id|intval}{literal};
        const ajaxUrl = '{/literal}{$ajax_url|escape:'javascript':'UTF-8'}{literal}&ajax=1&';
        const pollingInterval = 2000;
        let isProcessing = false;
        let pollTimer = null;
        let packageTemplates = [];

        // Reload page when returning from edit tab to reflect saved changes
        let editTabOpened = false;
        document.addEventListener('click', function(e) {
            if (e.target.closest('a[target="_blank"]')) {
                editTabOpened = true;
            }
        });
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden && editTabOpened && !isProcessing) {
                editTabOpened = false;

                // Check if any order was updated via localStorage signal
                const orderCheckboxes = document.querySelectorAll('.order-checkbox');
                let needsRefresh = false;
                orderCheckboxes.forEach(cb => {
                    const orderId = cb.dataset.orderId;
                    if (orderId && localStorage.getItem('alsendo_order_updated_' + orderId)) {
                        needsRefresh = true;
                        localStorage.removeItem('alsendo_order_updated_' + orderId);
                    }
                });

                // Always reload with cache-busting parameter
                window.location.href = '{/literal}{$ajax_url|escape:'javascript':'UTF-8'}{literal}&batch_id=' + batchId + '&_refresh=' + Date.now();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('send-all-btn').addEventListener('click', sendAllOrders);
            document.getElementById('retry-failed-btn').addEventListener('click', retryFailed);
            document.getElementById('download-all-btn').addEventListener('click', downloadAllWaybillsZip);
            document.getElementById('cancel-batch-btn').addEventListener('click', cancelBatch);

            document.getElementById('select-all-orders').addEventListener('change', toggleSelectAll);
            document.getElementById('header-select-all').addEventListener('change', toggleSelectAll);
            document.getElementById('remove-selected-btn').addEventListener('click', removeSelectedOrders);
            document.getElementById('refresh-status-btn').addEventListener('click', refreshBatchStatus);

            document.querySelectorAll('.order-checkbox').forEach(cb => {
                cb.addEventListener('change', updateBulkActionsState);
            });

            // Add event listeners for individual retry buttons
            document.querySelectorAll('.retry-single-order').forEach(btn => {
                btn.addEventListener('click', retrySingleOrder);
            });

            // Add event listeners for edit buttons - mark order as being edited
            document.querySelectorAll('.edit-order-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    const orderId = this.dataset.orderId;
                    // Store that we're opening edit tab for this order
                    sessionStorage.setItem('alsendo_editing_order_' + orderId, Date.now().toString());
                });
            });

            // Check for any orders that were edited and need refresh
            checkForEditedOrders();

            loadPackageTemplates();
        });

        function loadPackageTemplates() {
            fetch(ajaxUrl + '&action=get_package_templates')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.templates) {
                        packageTemplates = data.templates;
                        populateTemplateSelects();
                        initTemplateChangeHandlers();
                        // Sync Package Details cells with selected templates on load
                        document.querySelectorAll('.order-template-select').forEach(s => s.dispatchEvent(new Event('change')));
                    }
                });
        }

        function populateTemplateSelects() {
            const orderSelects = document.querySelectorAll('.order-template-select');

            // Find default template index (the one marked as main)
            let defaultIdx = 0;
            packageTemplates.forEach((tpl, idx) => {
                if (tpl.main) {
                    defaultIdx = idx;
                }
            });

            orderSelects.forEach(select => {
                select.innerHTML = '';

                packageTemplates.forEach((tpl, idx) => {
                    const name = tpl.alsendo_template_name || 'Szablon ' + (idx + 1);
                    const option = document.createElement('option');
                    option.value = idx;
                    option.textContent = tpl.main ? name + ' (domyślny)' : name;
                    select.appendChild(option);
                });

                // Match template by name saved in order data, fallback to default
                const row = select.closest('tr');
                const savedTemplateName = row ? row.dataset.templateName : '';
                let matchedIdx = -1;
                if (savedTemplateName && savedTemplateName !== 'No template') {
                    packageTemplates.forEach((tpl, idx) => {
                        if ((tpl.alsendo_template_name || '') === savedTemplateName) {
                            matchedIdx = idx;
                        }
                    });
                }
                select.value = matchedIdx >= 0 ? matchedIdx : defaultIdx;
            });
        }

        function initTemplateChangeHandlers() {
            document.querySelectorAll('.order-template-select').forEach(select => {
                select.addEventListener('change', function() {
                    const orderId = this.dataset.orderId;
                    const tplIdx = parseInt(this.value, 10);
                    const tpl = packageTemplates[tplIdx];
                    if (!tpl) return;

                    const row = document.querySelector('tr[data-order-id="' + orderId + '"]');
                    if (!row) return;

                    const cell = row.querySelector('.package-details-cell');
                    if (cell) {
                        const w = parseFloat(tpl.alsendo_width || 0);
                        const l = parseFloat(tpl.alsendo_length || 0);
                        const h = parseFloat(tpl.alsendo_height || 0);
                        const wt = parseFloat(tpl.alsendo_weight || 0);
                        cell.innerHTML = 'W: <strong>' + w + '</strong>cm | L: <strong>' + l + '</strong>cm | H: <strong>' + h + '</strong>cm | Wt: <strong>' + wt + '</strong>kg';
                    }
                });
            });
        }

        function toggleSelectAll(e) {
            const checked = e.target.checked;
            document.querySelectorAll('.order-checkbox').forEach(cb => {
                cb.checked = checked;
            });
            document.getElementById('select-all-orders').checked = checked;
            document.getElementById('header-select-all').checked = checked;
            updateBulkActionsState();
        }

        function updateBulkActionsState() {
            const selected = document.querySelectorAll('.order-checkbox:checked').length;
            document.getElementById('remove-selected-btn').disabled = selected === 0;
        }


        function removeSelectedOrders() {
            const selected = document.querySelectorAll('.order-checkbox:checked');
            if (selected.length === 0) return;

            if (!confirm('{/literal}{l s='Remove selected orders from this batch?' mod='alsendo'}{literal}')) return;

            selected.forEach(cb => {
                const orderId = cb.dataset.orderId;
                const row = document.querySelector('tr[data-order-id="' + orderId + '"]');
                if (row) {
                    row.remove();
                }
            });

            const totalCount = document.querySelectorAll('#orders-body tr').length;
            document.getElementById('total-count').textContent = totalCount;
            updateCounts();
        }

        function getTemplateOverridesParam() {
            const overrides = {};
            document.querySelectorAll('.order-template-select').forEach(select => {
                overrides[select.dataset.orderId] = select.value;
            });
            return '&template_overrides=' + encodeURIComponent(JSON.stringify(overrides));
        }

        function refreshBatchStatus() {
            // Full page reload with cache-busting to ensure fresh data from database
            window.location.href = '{/literal}{$ajax_url|escape:'javascript':'UTF-8'}{literal}&batch_id=' + batchId + '&_refresh=' + Date.now();
        }

        function sendAllOrders() {
            if (isProcessing) return;
            const btn = document.getElementById('send-all-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="icon-spinner icon-spin"></i> {/literal}{l s='Sending...' mod='alsendo'}{literal}';
            isProcessing = true;

            fetch(ajaxUrl + '&action=send_bulk', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'batch_id=' + batchId + '&ajax=1' + getTemplateOverridesParam()
            })
                .then(response => response.json())
                .then(data => {
                    updateUIFromResults(data);
                    pollBatchStatus();
                    btn.disabled = false;
                    btn.innerHTML = '<i class="icon-check"></i> {/literal}{l s='Send All Orders' mod='alsendo'}{literal}';
                    isProcessing = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorModal('{/literal}{l s='An error occurred while sending orders' mod='alsendo'}{literal}');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="icon-check"></i> {/literal}{l s='Send All Orders' mod='alsendo'}{literal}';
                    isProcessing = false;
                });
        }

        function retryFailed() {
            if (isProcessing) return;
            if (!confirm('{/literal}{l s='Are you sure you want to retry failed orders?' mod='alsendo'}{literal}')) return;

            const btn = document.getElementById('retry-failed-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="icon-spinner icon-spin"></i> {/literal}{l s='Retrying...' mod='alsendo'}{literal}';
            isProcessing = true;

            fetch(ajaxUrl + '&action=retry_failed', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'batch_id=' + batchId + '&ajax=1' + getTemplateOverridesParam()
            })
                .then(response => response.json())
                .then(data => {
                    updateUIFromResults(data);
                    pollBatchStatus();
                    btn.disabled = false;
                    btn.innerHTML = '<i class="icon-refresh"></i> {/literal}{l s='Retry Failed' mod='alsendo'}{literal}';
                    isProcessing = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorModal('{/literal}{l s='An error occurred while retrying' mod='alsendo'}{literal}');
                    btn.disabled = false;
                    isProcessing = false;
                });
        }

        function retrySingleOrder(e) {
            if (isProcessing) return;
            const btn = e.target.closest('.retry-single-order');
            const orderId = btn.dataset.orderId;

            btn.disabled = true;
            btn.innerHTML = '<i class="icon-spinner icon-spin"></i>';

            fetch(ajaxUrl + '&action=retry_single', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'batch_id=' + batchId + '&id_order=' + orderId + '&ajax=1'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the row status
                        if (data.item) {
                            updateRowStatus(data.item);
                        }
                        updateCounts();
                    } else {
                        // Show error and update error message
                        showErrorModal('{/literal}{l s='Retry failed:' mod='alsendo'}{literal} ' + (data.error || data.message || 'Unknown error'));
                        const row = document.querySelector('tr[data-order-id="' + orderId + '"]');
                        if (row && data.message) {
                            const errorDiv = row.querySelector('.action-cell div');
                            if (errorDiv) {
                                errorDiv.innerHTML = '';
                                var strong = document.createElement('strong');
                                strong.textContent = data.message;
                                errorDiv.appendChild(strong);
                            }
                        }
                    }
                    btn.disabled = false;
                    btn.innerHTML = '<i class="icon-refresh"></i> {/literal}{l s='Retry' mod='alsendo'}{literal}';
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorModal('{/literal}{l s='An error occurred while retrying' mod='alsendo'}{literal}');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="icon-refresh"></i> {/literal}{l s='Retry' mod='alsendo'}{literal}';
                });
        }

        function checkForEditedOrders() {
            // Check all order checkboxes to see which orders we have
            const orderCheckboxes = document.querySelectorAll('.order-checkbox');
            let needsFullRefresh = false;

            orderCheckboxes.forEach(cb => {
                const orderId = cb.dataset.orderId;
                // Check if this order was edited (localStorage signal)
                if (localStorage.getItem('alsendo_order_updated_' + orderId)) {
                    needsFullRefresh = true;
                    localStorage.removeItem('alsendo_order_updated_' + orderId);
                }
                // Check if we had opened edit tab for this order
                if (sessionStorage.getItem('alsendo_editing_order_' + orderId)) {
                    needsFullRefresh = true;
                    sessionStorage.removeItem('alsendo_editing_order_' + orderId);
                }
            });

            if (needsFullRefresh) {
                // If any order was updated, do full page reload
                window.location.href = '{/literal}{$ajax_url|escape:'javascript':'UTF-8'}{literal}&batch_id=' + batchId + '&_refresh=' + Date.now();
            }
        }

        function downloadAllWaybillsZip() {
            const btn = document.getElementById('download-all-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="icon-spinner icon-spin"></i> {/literal}{l s='Preparing ZIP...' mod='alsendo'}{literal}';

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = ajaxUrl + 'action=download_all_waybills_zip';
            form.style.display = 'none';

            const batchInput = document.createElement('input');
            batchInput.type = 'hidden';
            batchInput.name = 'batch_id';
            batchInput.value = batchId;

            form.appendChild(batchInput);
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);

            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="icon-download"></i> {/literal}{l s='Download All Waybills (ZIP)' mod='alsendo'}{literal}';
            }, 2000);
        }

        function downloadSingleWaybill(orderId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = ajaxUrl + 'action=download_waybill';
            form.style.display = 'none';

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id_order';
            idInput.value = orderId;

            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        function cancelBatch() {
            if (!confirm('{/literal}{l s='Are you sure you want to cancel and delete this entire batch? All sent shipments will be cancelled via courier API and all records will be deleted. This returns orders to the state before sending.' mod='alsendo'}{literal}')) {
                return;
            }

            const btn = document.getElementById('cancel-batch-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="icon-spinner icon-spin"></i> {/literal}{l s='Cancelling...' mod='alsendo'}{literal}';

            fetch(ajaxUrl + '&action=cancel_batch', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'batch_id=' + batchId + '&ajax=1'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Stop polling
                        if (pollTimer) {
                            clearInterval(pollTimer);
                            pollTimer = null;
                        }

                        // Show success message
                        showSuccessModal(data.message || '{/literal}{l s='Batch cancelled successfully' mod='alsendo'}{literal}');

                        // Reload page with batch_id to show updated statuses
                        window.location.href = '{/literal}{$ajax_url|escape:'javascript':'UTF-8'}{literal}&batch_id=' + batchId;
                    } else {
                        showErrorModal('{/literal}{l s='Error cancelling batch: ' mod='alsendo'}{literal}' + (data.message || 'Unknown error'));
                        btn.disabled = false;
                        btn.innerHTML = '<i class="icon-times"></i> {/literal}{l s='Cancel Batch' mod='alsendo'}{literal}';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorModal('{/literal}{l s='An error occurred while cancelling the batch' mod='alsendo'}{literal}');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="icon-times"></i> {/literal}{l s='Cancel Batch' mod='alsendo'}{literal}';
                });
        }

        function cancelOrderShipment(orderId) {
            if (!confirm('{/literal}{l s='Are you sure you want to cancel this shipment?' mod='alsendo'}{literal}')) return;

            fetch(ajaxUrl + '&action=cancel_order', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'batch_id=' + batchId + '&id_order=' + orderId + '&ajax=1'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = document.querySelector('tr[data-order-id="' + orderId + '"]');
                        if (row) {
                            row.classList.remove('status-success');
                            row.classList.add('status-cancelled');
                            const badge = row.querySelector('.badge');
                            badge.style.backgroundColor = '#999';
                            badge.textContent = '{/literal}{l s='CANCELLED' mod='alsendo'}{literal}';
                            const actionCell = row.querySelector('.action-cell');
                            actionCell.innerHTML = '<span style="color: #999;">{/literal}{l s='Cancelled' mod='alsendo'}{literal}</span>';
                        }
                        updateCounts();
                    }
                });
        }

        function pollBatchStatus() {
            // Clear any existing timer first
            if (pollTimer) {
                clearInterval(pollTimer);
            }

            pollTimer = setInterval(() => {
                fetch(ajaxUrl + '&action=get_batch_status', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'batch_id=' + batchId + '&ajax=1'
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.batch && (data.batch.status === 'completed' || data.batch.status === 'error' || data.batch.status === 'cancelled')) {
                            clearInterval(pollTimer);
                            pollTimer = null;
                        }
                        if (data.items) {
                            data.items.forEach(item => updateRowStatus(item));
                        }
                        updateCounts();
                    });
            }, pollingInterval);

            setTimeout(() => {
                if (pollTimer) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                }
            }, 120000);
        }

        function updateUIFromResults(results) {
            if (!results.items) return;
            Object.keys(results.items).forEach(orderId => {
                const item = results.items[orderId];
                const row = document.querySelector('tr[data-order-id="' + orderId + '"]');
                if (row) {
                    row.classList.remove('status-pending', 'status-processing', 'status-success', 'status-failed');
                    row.classList.add('status-' + item.status);
                    const badge = row.querySelector('.badge');
                    switch (item.status) {
                        case 'success':
                            badge.style.backgroundColor = '#28a745';
                            badge.textContent = 'SUCCESS';
                            updateSuccessRow(row, orderId);
                            break;
                        case 'failed':
                            badge.style.backgroundColor = '#d9534f';
                            badge.textContent = 'FAILED';
                            updateFailedRow(row, orderId, item);
                            break;
                    }
                }
            });
            updateCounts();
        }

        function updateRowStatus(item) {
            const row = document.querySelector('tr[data-order-id="' + item.id_order + '"]');
            if (!row) return;
            row.classList.remove('status-pending', 'status-processing', 'status-success', 'status-failed');
            row.classList.add('status-' + item.status);
            const badge = row.querySelector('.badge');
            switch (item.status) {
                case 'success':
                    badge.style.backgroundColor = '#28a745';
                    badge.textContent = 'SUCCESS';
                    updateSuccessRow(row, item.id_order);
                    break;
                case 'failed':
                    badge.style.backgroundColor = '#d9534f';
                    badge.textContent = 'FAILED';
                    updateFailedRow(row, item.id_order, item);
                    break;
            }
        }

        function updateSuccessRow(row, orderId) {
            const actionCell = row.querySelector('.action-cell');
            actionCell.innerHTML = '<button class="btn btn-sm btn-info" onclick="downloadSingleWaybill(' + orderId + ')" style="margin-right: 5px;"><i class="icon-download"></i> Waybill</button><button class="btn btn-sm btn-danger" onclick="cancelOrderShipment(' + orderId + ')"><i class="icon-times"></i> Cancel</button>';
        }

        function updateFailedRow(row, orderId, item) {
            const actionCell = row.querySelector('.action-cell');
            const errorMsg = item.error_message || '{/literal}{l s='Unknown error' mod='alsendo'}{literal}';
            actionCell.innerHTML = '';
            var errorDiv = document.createElement('div');
            errorDiv.style.cssText = 'color: #d9534f; font-size: 11px; margin-bottom: 8px;';
            var strong = document.createElement('strong');
            strong.textContent = 'Error: ';
            errorDiv.appendChild(strong);
            errorDiv.appendChild(document.createTextNode(errorMsg));
            actionCell.appendChild(errorDiv);
        }

        function updateCounts() {
            const rows = document.querySelectorAll('#orders-body tr');
            let pending = 0, success = 0, failed = 0;
            rows.forEach(row => {
                if (row.classList.contains('status-pending')) pending++;
                else if (row.classList.contains('status-success')) success++;
                else if (row.classList.contains('status-failed')) failed++;
            });
            document.getElementById('pending-count').textContent = pending;
            document.getElementById('success-count').textContent = success;
            document.getElementById('failed-count').textContent = failed;
            document.getElementById('retry-failed-btn').disabled = (failed === 0 || isProcessing);
            document.getElementById('download-all-btn').disabled = (success === 0);
        }

        window.downloadSingleWaybill = downloadSingleWaybill;
        window.cancelOrderShipment = cancelOrderShipment;
    })();
    {/literal}
</script>