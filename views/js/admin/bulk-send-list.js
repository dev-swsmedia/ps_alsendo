/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        injectBulkSendButton();
    }, 500);
});

function injectBulkSendButton() {
    const bulkActionsBtn = document.querySelector('.js-bulk-actions-btn');
    if (!bulkActionsBtn) {
        return;
    }

    const btnGroup = bulkActionsBtn.closest('.btn-group');
    if (!btnGroup) {
        return;
    }
    const buttonContainer = document.createElement('div');
    buttonContainer.id = 'alsendo-bulk-button-container';
    buttonContainer.style.cssText = 'display: inline-block; margin-left: 12px; vertical-align: middle;';

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-primary';
    button.id = 'alsendo-bulk-send-btn';
    button.innerHTML = '<i class="material-icons" style="vertical-align: middle; margin-right: 6px; font-size: 18px;">local_shipping</i>' + (window.ALSENDO_MSG_SEND_WITH_ALSENDO || 'Send with Alsendo');
    button.style.cssText = 'white-space: nowrap; padding: 6px 12px; font-weight: 500;';

    button.addEventListener('click', handleBulkSend);
    buttonContainer.appendChild(button);

    btnGroup.insertAdjacentElement('afterend', buttonContainer);
}

function handleBulkSend(e) {
    e.preventDefault();
    const checkedBoxes = document.querySelectorAll('input[name="order_orders_bulk[]"]:checked');
    if (checkedBoxes.length === 0) {
        showErrorModal(window.ALSENDO_MSG_SELECT_ORDERS || 'Please select at least one order by checking the checkbox');
        return;
    }

    const orderIds = Array.from(checkedBoxes)
        .map(cb => cb.value)
        .join(',');
    const urlParams = new URLSearchParams(window.location.search);
    let token = urlParams.get('_token') || urlParams.get('token');

    if (!token) {
        const bulkBtn = document.querySelector('[data-form-submit-url*="_token"]');
        if (bulkBtn) {
            const url = bulkBtn.getAttribute('data-form-submit-url');
            const match = url.match(/_token=([^&]+)/);
            if (match) token = match[1];
        }
    }

    if (!token) {
        token = document.querySelector('input[name="token"]')?.value ||
            document.querySelector('input[name="_token"]')?.value;
    }
    if (!token) {
        showErrorModal(window.ALSENDO_MSG_TOKEN_ERROR || 'Security token not found. Please refresh the page and try again.');
        return;
    }

    const pathParts = window.location.pathname.split('/').filter(p => p);
    const adminFolder = '/' + pathParts[0] + '/';

    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';
    form.action = adminFolder + 'index.php?controller=AdminAlsendoBulkSend&token=' + token;

    form.innerHTML = `
        <input type="hidden" name="submitBulkActionAslendobulk" value="1">
        <input type="hidden" name="order_ids" value="${orderIds}">
        <input type="hidden" name="token" value="${token}">
        <input type="hidden" name="_token" value="${token}">
    `;

    document.body.appendChild(form);
    form.submit();
}
