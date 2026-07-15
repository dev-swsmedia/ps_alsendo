{*
* Alsendo - PrestaShop shipping module
*
* @author    Innovation Software
* @copyright 2026 Innovation Software
* @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
*}

<form id="alsendo-bulk-send-form" method="post" style="display: none;">
    <input type="hidden" name="submitBulkActionAslendobulk" value="1">
    <input type="hidden" name="order_ids" id="alsendo-order-ids-input" value="">
</form>

<div id="bulk-actions-alsendo" style="display: none; margin-top: 20px; padding: 15px; background-color: #e3f2fd; border: 1px solid #2196f3; border-radius: 4px;">
    <button type="button" id="alsendo-bulk-send-btn" class="btn btn-primary" style="margin-right: 10px; font-weight: 600;">
        <i class="icon-truck"></i> Send with Alsendo
    </button>
    <span id="alsendo-selected-count" style="font-size: 12px; color: #666; margin-left: 10px;"></span>
</div>

<script src="{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}alsendo/views/js/admin/alsendo-modal.js?v={$smarty.now|escape:'html':'UTF-8'}"></script>
<script>
    window.ALSENDO_MSG_SELECT_ORDERS = "{l s='Please select at least one order' mod='alsendo' js=1}";
    {literal}
    (function() {
        'use strict';

        console.log('Alsendo: Bulk send script loaded');

        function initBulkSendAction() {
            console.log('Alsendo: Initializing bulk send action');

            const checkboxes = document.querySelectorAll('input[name="order_orders_bulk[]"]');
            console.log('Alsendo: Found checkboxes:', checkboxes.length);

            if (!checkboxes.length) {
                console.log('Alsendo: No checkboxes found, retrying...');
                setTimeout(initBulkSendAction, 500);
                return;
            }

            const form = document.getElementById('alsendo-bulk-send-form');
            const container = document.getElementById('bulk-actions-alsendo');
            const button = document.getElementById('alsendo-bulk-send-btn');
            const countSpan = document.getElementById('alsendo-selected-count');

            if (!form || !container || !button) {
                console.error('Alsendo: Elements not found');
                return;
            }

            console.log('Alsendo: Elements found, initializing');

            function updateVisibility() {
                const checked = document.querySelectorAll('input[name="order_orders_bulk[]"]:checked');
                container.style.display = checked.length > 0 ? 'block' : 'none';
                countSpan.textContent = checked.length > 0 ? ' (' + checked.length + ' ' + (window.ALSENDO_MSG_ORDERS_SELECTED || 'order(s) selected') + ')' : '';
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateVisibility);
            });

            const selectAllCheckbox = document.getElementById('order_grid_bulk_action_select_all');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    setTimeout(updateVisibility, 100);
                });
            }

            button.addEventListener('click', function(e) {
                e.preventDefault();

                const checked = document.querySelectorAll('input[name="order_orders_bulk[]"]:checked');
                if (!checked.length) {
                    showErrorModal(window.ALSENDO_MSG_SELECT_ORDERS);
                    return;
                }

                const orderIds = Array.from(checked).map(cb => cb.value).join(',');
                document.getElementById('alsendo-order-ids-input').value = orderIds;

                console.log('Alsendo: Submitting with orders:', orderIds);
                form.submit();
            });

            console.log('Alsendo: Initialization complete');
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initBulkSendAction);
        } else {
            initBulkSendAction();
        }
    })();
    {/literal}
</script>