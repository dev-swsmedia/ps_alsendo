{*
* Alsendo - PrestaShop shipping module
*
* @author    Innovation Software
* @copyright 2026 Innovation Software
* @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
*}

<link rel="stylesheet" href="{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}alsendo/views/css/alsendo-admin.css">

{if $alsendo_sender || $alsendo_shipping_address || $alsendo_package || $alsendo_shipping}
    <div class="panel">
        <div class="panel-heading">
            <i class="fas fa-truck"></i>
            <h3>{l s='Alsendo Shipping' mod='alsendo'}</h3>
        </div>
        <div class="panel-body">
            {if $alsendo_sender}
                <div class="mb-4">
                    <strong>{l s='Sender Address' mod='alsendo'}</strong>
                    <p class="mb-1" style="margin-top: 8px;">
                        {if $alsendo_sender.template_name|default:'' ne '' && $alsendo_sender.template_name ne '-'}
                            <strong>{l s='Template Name' mod='alsendo'}:</strong> {$alsendo_sender.template_name|escape:'html':'UTF-8'}<br>
                        {/if}
                        {if $alsendo_sender.company_name|default:'' ne ''}
                            <strong>{$alsendo_sender.company_name|default:''|escape:'html':'UTF-8'}</strong><br>
                        {/if}
                        {$alsendo_sender.first_name|default:''|escape:'html':'UTF-8'} {$alsendo_sender.last_name|default:''|escape:'html':'UTF-8'}<br>
                        {$alsendo_sender.street|default:''|escape:'html':'UTF-8'} {$alsendo_sender.building_number|default:''|escape:'html':'UTF-8'}
                        {if $alsendo_sender.apartment_number|default:'' ne ''}/{$alsendo_sender.apartment_number|default:''|escape:'html':'UTF-8'}{/if},
                        {$alsendo_sender.postal_code|default:''|escape:'html':'UTF-8'} {$alsendo_sender.city|default:''|escape:'html':'UTF-8'}
                    </p>
                </div>
            {/if}

            {if $alsendo_shipping_address}
                <div class="mb-4">
                    <strong>{l s='Shipping Address' mod='alsendo'}</strong>
                    <p class="mb-1" style="margin-top: 8px;">
                        {if $alsendo_shipping_address.company|default:'' ne ''}{$alsendo_shipping_address.company|default:''|escape:'html':'UTF-8'}<br>{/if}
                        {$alsendo_shipping_address.first_name|default:''|escape:'html':'UTF-8'} {$alsendo_shipping_address.last_name|default:''|escape:'html':'UTF-8'}<br>
                        {$alsendo_shipping_address.address|default:''|escape:'html':'UTF-8'}
                        {if $alsendo_shipping_address.address2|default:'' ne ''} /{$alsendo_shipping_address.address2|default:''|escape:'html':'UTF-8'}{/if},
                        {$alsendo_shipping_address.postal_code|default:''|escape:'html':'UTF-8'} {$alsendo_shipping_address.town|default:''|escape:'html':'UTF-8'}, {$alsendo_shipping_address.country|default:''|escape:'html':'UTF-8'}
                    </p>
                </div>
            {/if}

            {if $alsendo_package}
                <div class="mb-4" id="alsendo-package-details">
                    <strong>{l s='Package' mod='alsendo'}</strong>
                    <p class="mb-1" style="margin-top: 8px;">
                        <span id="alsendo-pkg-template-name">
                        {if $alsendo_package.template_name|default:'' ne '' && $alsendo_package.template_name ne '-'}
                            <strong>{l s='Template Name' mod='alsendo'}:</strong> {$alsendo_package.template_name|escape:'html':'UTF-8'}<br>
                        {/if}
                        </span>
                        <strong>{l s='Dimensions' mod='alsendo'}:</strong> <span id="alsendo-pkg-dimensions">{$alsendo_package.width|default:'0'|escape:'html':'UTF-8'}x{$alsendo_package.height|default:'0'|escape:'html':'UTF-8'}x{$alsendo_package.length|default:'0'|escape:'html':'UTF-8'}</span>cm<br>
                        <strong>{l s='Weight' mod='alsendo'}:</strong> <span id="alsendo-pkg-weight">{$alsendo_package.weight|default:'0'|escape:'html':'UTF-8'}</span>kg<br>
                        <span id="alsendo-pkg-type">
                        {if $alsendo_package.package_type|default:'' ne '' && $alsendo_package.package_type ne '-'}
                            <strong>{l s='Package Type' mod='alsendo'}:</strong> {$alsendo_package.package_type|escape:'html':'UTF-8'}<br>
                        {/if}
                        </span>
                        <span id="alsendo-pkg-content">
                        {if $alsendo_package.package_content|default:'' ne ''}
                            <strong>{l s='Content' mod='alsendo'}:</strong> {$alsendo_package.package_content|default:''|escape:'html':'UTF-8'}<br>
                        {/if}
                        </span>
                        {if $alsendo_package.cod_value|default:'' ne '' && $alsendo_package.cod_value|default:'0' ne '0'}
                            <strong>{l s='Cash on Delivery' mod='alsendo'}:</strong> {$alsendo_package.cod_value|default:''|escape:'html':'UTF-8'} {$alsendo_currency|default:'PLN'|escape:'html':'UTF-8'}<br>
                        {/if}
                        {if $alsendo_package.declared_value|default:'' ne '' && $alsendo_package.declared_value|default:'0' ne '0'}
                            <strong>{l s='Declared Value' mod='alsendo'}:</strong> {$alsendo_package.declared_value|default:''|escape:'html':'UTF-8'} {$alsendo_currency|default:'PLN'|escape:'html':'UTF-8'}<br>
                        {/if}
                    </p>
                </div>
            {/if}

            {if $alsendo_pickup_point}
                <div class="mb-4">
                    <strong>{l s='Pickup Point' mod='alsendo'}</strong>
                    <p class="mb-1" style="margin-top: 8px;">
                        {if $alsendo_pickup_point.code|default:'' ne ''}
                            <strong>{l s='Point ID' mod='alsendo'}:</strong> {$alsendo_pickup_point.code|escape:'html':'UTF-8'}<br>
                        {/if}
                        {if $alsendo_pickup_point.name|default:'' ne ''}
                            <strong>{l s='Name' mod='alsendo'}:</strong> {$alsendo_pickup_point.name|escape:'html':'UTF-8'}<br>
                        {/if}
                        {if $alsendo_pickup_point.address|default:'' ne ''}
                            <strong>{l s='Address' mod='alsendo'}:</strong> {$alsendo_pickup_point.address|escape:'html':'UTF-8'}<br>
                        {/if}
                    </p>
                </div>
            {/if}

            {if $order_total|default:0 > 0}
                <div class="mb-4">
                    <p class="mb-2">
                        <strong>{l s='Order Total' mod='alsendo'}:</strong>
                        {$order_total|string_format:"%.2f"|escape:'html':'UTF-8'} {$alsendo_currency|default:'PLN'|escape:'html':'UTF-8'}
                    </p>
                </div>
            {/if}

            {assign var=hasShipment value=($alsendo_shipping|@count > 0)}

            <div class="mb-4">
                <strong>{l s='Shipment' mod='alsendo'}</strong>
            </div>

            {if $hasShipment && $alsendo_shipping.status|default:'' ne '' && $alsendo_shipping.status|default:'' ne 'cancelled'}
                <div class="mb-3">
                    <strong>{l s='Courier' mod='alsendo'}:</strong> {$alsendo_shipping.shipping_method|default:'-'|escape:'html':'UTF-8'}<br>
                    <strong>{l s='Service ID' mod='alsendo'}:</strong> {$alsendo_shipping.courier_service|default:'-'|escape:'html':'UTF-8'}<br>
                    <strong>{l s='Pickup Type' mod='alsendo'}:</strong> {if isset($pickup_type_labels[$alsendo_package.pickup_type])}{$pickup_type_labels[$alsendo_package.pickup_type]|escape:'html':'UTF-8'}{else}{$alsendo_package.pickup_type|default:'-'|escape:'html':'UTF-8'}{/if}<br>
                    <strong>{l s='Price' mod='alsendo'}:</strong> {if $alsendo_price_display|default:''}{$alsendo_price_display|escape:'html':'UTF-8'} {$alsendo_currency|default:'PLN'|escape:'html':'UTF-8'}{else}-{/if}<br>
                </div>

                {if $alsendo_pickup_point && $alsendo_package.pickup_type|default:'' == 'SELF'}
                    <div class="mb-3">
                        <strong>{l s='Selected Pickup Point' mod='alsendo'}:</strong><br>
                        <strong>{l s='Point ID' mod='alsendo'}:</strong> {$alsendo_pickup_point.code|default:'-'|escape:'html':'UTF-8'}<br>
                        {if $alsendo_pickup_point.name|default:''}
                            <strong>{l s='Name' mod='alsendo'}:</strong> {$alsendo_pickup_point.name|escape:'html':'UTF-8'}<br>
                        {/if}
                        {if $alsendo_pickup_point.address|default:''}
                            <strong>{l s='Address' mod='alsendo'}:</strong> {$alsendo_pickup_point.address|escape:'html':'UTF-8'}<br>
                        {/if}
                    </div>
                {/if}

                <div class="mb-3">
                    {if $alsendo_shipping.waybill_number|default:'' ne ''}
                        <strong>{l s='Waybill Number' mod='alsendo'}:</strong> {$alsendo_shipping.waybill_number|escape:'html':'UTF-8'}
                        <button class="btn btn-sm" style="all: unset; color: #1e91cf; cursor: pointer;" onclick="navigator.clipboard.writeText('{$alsendo_shipping.waybill_number|escape:'javascript':'UTF-8'}')">{l s='Copy' mod='alsendo'}</button><br>
                    {/if}

                    {if $alsendo_shipping.carrier_tracking_number|default:'' ne ''}
                        <strong>{l s='Carrier Tracking Number' mod='alsendo'}:</strong> {$alsendo_shipping.carrier_tracking_number|escape:'html':'UTF-8'}
                        <button class="btn btn-sm" style="all: unset; color: #1e91cf; cursor: pointer;" onclick="navigator.clipboard.writeText('{$alsendo_shipping.carrier_tracking_number|escape:'javascript':'UTF-8'}')">{l s='Copy' mod='alsendo'}</button><br>
                    {/if}

                    {if $alsendo_shipping.tracking_url|default:''}
                        <strong>{l s='Tracking link' mod='alsendo'}:</strong>
                        <a href="{$alsendo_shipping.tracking_url|escape:'html':'UTF-8'}" target="_blank" rel="noopener">
                            {$alsendo_shipping.tracking_url|escape:'html':'UTF-8'}
                        </a><br>
                    {/if}

                    <strong>{l s='Status' mod='alsendo'}:</strong> {$alsendo_shipping.status|default:'-'|escape:'html':'UTF-8'}
                </div>
            {else}
                {if $alsendo_estimated_price|default:''}
                    <div class="mb-3">
                        <strong>{l s='Estimated Price' mod='alsendo'}:</strong> {$alsendo_estimated_price_display|escape:'html':'UTF-8'} {$alsendo_currency|default:'PLN'}<br>
                        {if $alsendo_estimated_service_id|default:''}
                            <strong>{l s='Service ID' mod='alsendo'}:</strong> {$alsendo_estimated_service_id|escape:'html':'UTF-8'}<br>
                        {/if}
                        {if $alsendo_package.pickup_type|default:''}
                            <strong>{l s='Pickup Type' mod='alsendo'}:</strong> {if isset($pickup_type_labels[$alsendo_package.pickup_type])}{$pickup_type_labels[$alsendo_package.pickup_type]|escape:'html':'UTF-8'}{else}{$alsendo_package.pickup_type|escape:'html':'UTF-8'}{/if}<br>
                        {/if}
                    </div>

                    {if $alsendo_pickup_point && $alsendo_package.pickup_type|default:'' == 'SELF'}
                        <div class="mb-3">
                            <strong>{l s='Selected Pickup Point' mod='alsendo'}:</strong><br>
                            <strong>{l s='Point ID' mod='alsendo'}:</strong> {$alsendo_pickup_point.code|default:'-'|escape:'html':'UTF-8'}<br>
                            {if $alsendo_pickup_point.name|default:''}
                                <strong>{l s='Name' mod='alsendo'}:</strong> {$alsendo_pickup_point.name|escape:'html':'UTF-8'}<br>
                            {/if}
                            {if $alsendo_pickup_point.address|default:''}
                                <strong>{l s='Address' mod='alsendo'}:</strong> {$alsendo_pickup_point.address|escape:'html':'UTF-8'}<br>
                            {/if}
                        </div>
                    {/if}
                {else}
                    <p style="color: var(--als-muted);" class="mb-3">{l s='No submitted shipment yet for this order.' mod='alsendo'}</p>
                {/if}
            {/if}

            <div class="alsendo-actions">
                {if $hasShipment && $alsendo_shipping.status|default:'' ne '' && $alsendo_shipping.status|default:'' ne 'cancelled'}
                    <button class="btn btn-primary btn-sm download-waybill-button" data-order-id="{$order_id|escape:'html':'UTF-8'}">
                        <i class="fa fa-download"></i> {l s='Download Waybill' mod='alsendo'}
                    </button>
                    <button class="btn btn-danger btn-sm cancel-shipment-button" data-order-id="{$order_id|escape:'html':'UTF-8'}">
                        <i class="fa fa-times"></i> {l s='Cancel Shipment' mod='alsendo'}
                    </button>
                {else}
                    <div class="mb-2" style="display: flex; align-items: center; gap: 8px;">
                        <label for="alsendo_quick_send_template_{$order_id|intval}" style="margin: 0; white-space: nowrap;">
                            {l s='Template' mod='alsendo'}:
                        </label>
                        <select id="alsendo_quick_send_template_{$order_id|intval}" class="form-control form-control-sm quick-send-template-select" style="max-width: 200px;">
                        </select>
                    </div>
                    <button class="btn btn-warning btn-sm quick-send-button" data-order-id="{$order_id|escape:'html':'UTF-8'}">
                        <i class="fa fa-bolt"></i> {l s='Quick Send' mod='alsendo'}
                    </button>
                    <a href="{if isset($link) && $link}{$link->getAdminLink('AdminAlsendoOrder')|escape:'html':'UTF-8'}{else}index.php?controller=AdminAlsendoOrder&token={$user_token|escape:'html':'UTF-8'}{/if}&action=showFullForm&id_order={$order_id|intval}" class="btn btn-success btn-sm">
                        <i class="fa fa-truck"></i> {l s='Create Shipment' mod='alsendo'}
                    </a>
                {/if}
            </div>
        </div>
    </div>
{else}
    <p>{l s='No Alsendo shipping data found for this order.' mod='alsendo'}</p>
{/if}

<script src="{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}alsendo/views/js/admin/alsendo-modal.js?v={$smarty.now|escape:'html':'UTF-8'}"></script>
<script>
    var ALSENDO_TOKEN = '{$user_token|escape:"javascript":'UTF-8'}';
    var ALSENDO_CANCEL_CONFIRM = '{l s="Cancel this shipment?" d="Modules.Alsendo.Admin" js=1}';
    var ALSENDO_CONFIRM_SEND = "{l s='Send this order with selected template settings?' mod='alsendo' js=1}";
    var ALSENDO_MSG_SENDING = "{l s='Sending...' mod='alsendo' js=1}";
    var ALSENDO_MSG_SENT_OK = "{l s='Shipment sent successfully!' mod='alsendo' js=1}";
    var ALSENDO_MSG_REQUEST_FAILED = "{l s='Request failed' mod='alsendo' js=1}";
    var ajaxUrl = 'index.php?controller=AdminAlsendoOrder&ajax=1&token=' + ALSENDO_TOKEN + '&ajax_action=';
</script>

{literal}
    <script>
        var alsendoPackageTemplates = [];

        // Always fetch fresh templates from server (don't use stale localStorage cache)
        function loadPackageTemplates() {
            $.get(ajaxUrl.replace('ajax_action=', '') + 'ajax_action=getPackageTemplates', function(resp) {
                if (resp && resp.success && resp.templates) {
                    alsendoPackageTemplates = resp.templates;
                    // Update localStorage cache for future use
                    localStorage.setItem('alsendo_package_templates', JSON.stringify(alsendoPackageTemplates));
                    populateQuickSendTemplates();
                }
            }, 'json');
        }

        function populateQuickSendTemplates() {
            // Find default (main) template index
            var defaultIdx = 0;
            alsendoPackageTemplates.forEach(function(tpl, idx) {
                if (tpl.main) {
                    defaultIdx = idx;
                }
            });

            $('.quick-send-template-select').each(function() {
                var select = $(this);
                select.empty();  // Clear all options including the "Default" placeholder

                alsendoPackageTemplates.forEach(function(tpl, idx) {
                    var name = tpl.alsendo_template_name || tpl.name || ('Szablon ' + (idx + 1));
                    if (tpl.main) name += ' (domyślny)';
                    select.append('<option value="' + idx + '">' + name + '</option>');
                });

                // Auto-select the main/default template
                select.val(defaultIdx);
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            console.log('Quick send button loaded');
            loadPackageTemplates();  // Always load fresh templates

            $(document).off('click', '.quick-send-button').on('click', '.quick-send-button', function (e) {
                e.preventDefault();

                var btn = $(this);
                var orderId = btn.data('order-id');
                var templateIdx = parseInt($('#alsendo_quick_send_template_' + orderId).val() || 0, 10);

                if (!confirm(ALSENDO_CONFIRM_SEND)) return;

                btn.prop('disabled', true);
                btn.html('<i class="fa fa-spinner fa-spin"></i> ' + ALSENDO_MSG_SENDING);

                $.post(ajaxUrl + 'quickSendOrderShipment', {
                    order_id: orderId,
                    template_index: templateIdx
                }, function (resp) {
                    btn.prop('disabled', false);
                    btn.html('<i class="fa fa-bolt"></i> Quick Send');

                    if (resp && resp.success) {
                        showSuccessModal(ALSENDO_MSG_SENT_OK);
                        setTimeout(function() { location.reload(); }, 900);
                    } else {
                        var msg = (resp && resp.error) ? resp.error : 'Error';
                        if (resp && resp.errors) {
                            msg += '\n\n';
                            $.each(resp.errors, function(k, v) {
                                msg += '- ' + k + ': ' + v + '\n';
                            });
                        }
                        showErrorModal(msg);
                    }
                }, 'json').fail(function(error) {
                    btn.prop('disabled', false);
                    btn.html('<i class="fa fa-bolt"></i> Quick Send');
                    showErrorModal(ALSENDO_MSG_REQUEST_FAILED);
                });
            });

            // Update package details display when template dropdown changes
            $(document).off('change', '.quick-send-template-select').on('change', '.quick-send-template-select', function () {
                var tplIdx = parseInt(this.value, 10);
                var tpl = alsendoPackageTemplates[tplIdx];
                if (!tpl) return;

                var w = parseFloat(tpl.alsendo_width || 0);
                var l = parseFloat(tpl.alsendo_length || 0);
                var h = parseFloat(tpl.alsendo_height || 0);
                var wt = parseFloat(tpl.alsendo_weight || 0);
                var name = tpl.alsendo_template_name || '';
                var pkgType = tpl.alsendo_package_type || '';
                var content = tpl.alsendo_shipment_content || '';

                $('#alsendo-pkg-dimensions').text(w + 'x' + h + 'x' + l);
                $('#alsendo-pkg-weight').text(wt);
                if (name) {
                    $('#alsendo-pkg-template-name').html('<strong>{/literal}{l s="Template Name" mod="alsendo"}{literal}:</strong> ' + $('<span>').text(name).html() + '<br>');
                } else {
                    $('#alsendo-pkg-template-name').html('');
                }
                if (pkgType && pkgType !== '-') {
                    $('#alsendo-pkg-type').html('<strong>{/literal}{l s="Package Type" mod="alsendo"}{literal}:</strong> ' + $('<span>').text(pkgType).html() + '<br>');
                }
                if (content) {
                    $('#alsendo-pkg-content').html('<strong>{/literal}{l s="Content" mod="alsendo"}{literal}:</strong> ' + $('<span>').text(content).html() + '<br>');
                }
            });

            $(document).off('click', '.download-waybill-button').on('click', '.download-waybill-button', function (e) {
                e.preventDefault();
                var id = $(this).data('order-id');
                $.ajax({
                    url: ajaxUrl + 'downloadShipmentWaybill',
                    type: 'POST',
                    data: { order_id: id },
                    xhrFields: { responseType: 'blob' },
                    success: function(data, status, xhr) {
                        var ct = xhr.getResponseHeader('content-type') || '';
                        if (ct.indexOf('application/pdf') !== -1) {
                            var url = URL.createObjectURL(data);
                            var a = document.createElement('a');
                            a.href = url;
                            a.download = 'waybill_' + id + '.pdf';
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            URL.revokeObjectURL(url);
                        } else {
                            var reader = new FileReader();
                            reader.onload = function() {
                                try {
                                    var json = JSON.parse(reader.result);
                                    showErrorModal(json.error || 'Failed to download waybill');
                                } catch(e) {
                                    showErrorModal('Failed to download waybill');
                                }
                            };
                            reader.readAsText(data);
                        }
                    },
                    error: function() {
                        showErrorModal('Failed to download waybill');
                    }
                });
            });

            $(document).off('click', '.cancel-shipment-button').on('click', '.cancel-shipment-button', function (e) {
                e.preventDefault();
                if (!confirm(ALSENDO_CANCEL_CONFIRM)) return;
                var id = $(this).data('order-id');
                $.post(ajaxUrl + 'cancelOrderShipment', {order_id: id}, function (resp) {
                    if (resp && resp.success) {
                        location.reload();
                    } else {
                        showErrorModal((resp && resp.error) ? resp.error : 'Error');
                    }
                }, 'json');
            });
        });
    </script>
{/literal}