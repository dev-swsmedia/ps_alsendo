{*
* Alsendo - PrestaShop shipping module
*
* @author    Innovation Software
* @copyright 2026 Innovation Software
* @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
*}

<script>
    window.ALSENDO_MSG_SAVED = "{$alsendo_msg_saved|default:'Saved!'|escape:'javascript':'UTF-8'}";
    window.ALSENDO_BTN_SAVE = "{l s='Save' mod='alsendo' js=1}";
    window.ALSENDO_BTN_DELETE = "{l s='Delete' mod='alsendo' js=1}";
    window.ALSENDO_BTN_CANCEL = "{l s='Cancel' mod='alsendo' js=1}";
    window.ALSENDO_MSG_ENTER_NAME = "{l s='Please enter a method name.' mod='alsendo' js=1}";
    window.ALSENDO_MSG_ADD_FAILED = "{l s='Add failed' mod='alsendo' js=1}";
    window.ALSENDO_MSG_SAVE_FAILED = "{l s='Save failed' mod='alsendo' js=1}";
    window.ALSENDO_MSG_DELETE_FAILED = "{l s='Delete failed' mod='alsendo' js=1}";
    window.ALSENDO_MSG_DELETE_CONFIRM = "{l s='Delete this method?' mod='alsendo' js=1}";
    window.ALSENDO_MSG_UPDATE_FAILED = "{l s='Update failed' mod='alsendo' js=1}";
    window.ALSENDO_MSG_ERROR_PREFIX = "{l s='Error: ' mod='alsendo' js=1}";
    window.ALSENDO_MSG_SAVING = "{l s='Saving...' mod='alsendo' js=1}";
    window.ALSENDO_MSG_SYNCHRONIZED = "{l s='Synchronized!' mod='alsendo' js=1}";
    window.ALSENDO_MSG_LOAD_ERROR = "{l s='Error loading services: ' mod='alsendo' js=1}";
</script>
<link rel="stylesheet" href="{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}alsendo/views/css/alsendo-admin.css">
<script src="{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}alsendo/views/js/admin/alsendo-modal.js?v={$smarty.now|escape:'html':'UTF-8'}"></script>
<style>
    /* Unsaved indicator styling */
    .unsaved-indicator {
        display: inline-block;
        margin-left: 5px;
        position: relative;
    }
</style>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4><i class="fa-solid fa-truck"></i> {l s='Shipping Methods' mod='alsendo'}</h4>
        </div>
        <div class="card-body">
            <p class="alsendo-muted">{l s='Manage available shipping methods and associate them with Alsendo courier services.' mod='alsendo'}</p>

            <div class="table-responsive">
                <table class="table table-bordered" id="alsendo-shipping-methods">
                    <thead>
                    <tr>
                        <th>{l s='Method Name' mod='alsendo'} <span class="text-danger">*</span></th>
                        <th>{l s='Courier Service' mod='alsendo'} <span class="text-danger">*</span></th>
                        <th>{l s='Price' mod='alsendo'}</th>
                        <th>{l s='Map' mod='alsendo'}</th>
                        {if $alsendo_region == 'cz'}
                        <th>{l s='Ship via Pickup Point' mod='alsendo'}</th>
                        <th>{l s='Pickup Request' mod='alsendo'}</th>
                        {/if}
                        <th>{l s='Active' mod='alsendo'}</th>
                        <th>{l s='Actions' mod='alsendo'}</th>
                    </tr>
                    </thead>
                    <tbody id="methods-body">
                    {foreach $methods as $method}
                        <tr data-id="{$method.id_alsendo_shipping_method|escape:'html':'UTF-8'}">
                            <td><input type="text" value="{$method.method_name|escape:'html':'UTF-8'}" class="form-control method-name" /></td>
                            <td>
                                <select class="form-control service-id">
                                    <option value="">{l s='Any Service' mod='alsendo'}</option>
                                    {foreach $available_services as $service}
                                        <option value="{$service.service_id|escape:'html':'UTF-8'}"{if $service.service_id==$method.id_service} selected{/if}>{$service.name|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            </td>
                            <td><input type="number" step="0.01" class="form-control price" value="{$method.price|escape:'html':'UTF-8'}" /></td>
                            <td><input type="checkbox" class="has-map" {if $method.has_map}checked{/if} /></td>
                            {if $alsendo_region == 'cz'}
                            <td><input type="checkbox" class="ship-via-pickup-point" {if $method.ship_via_pickup_point}checked{/if} /></td>
                            <td><input type="checkbox" class="pickup-request" {if $method.pickup_request}checked{/if} /></td>
                            {/if}
                            <td><input type="checkbox" class="alsendo-active-toggle" {if $method.active}checked{/if} /></td>
                            <td class="alsendo-actions">
                                <button class="btn btn-primary btn-sm save-row">{l s='Save' mod='alsendo'}</button>
                                <button class="btn btn-danger btn-sm delete-row">{l s='Delete' mod='alsendo'}</button>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>

            <script type="text/template" id="row-template">
                <tr data-id="">
                    <td><input type="text" class="form-control method-name" placeholder="Enter method name" required></td>
                    <td>
                        <select class="form-control service-id">
                            <option value="">{l s='Any Service' mod='alsendo'}</option>
                            {foreach $available_services as $service}
                                <option value="{$service.service_id|escape:'html':'UTF-8'}">{$service.name|escape:'html':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </td>
                    <td><input type="number" step="0.01" class="form-control price" value="0.00"></td>
                    <td><input type="checkbox" class="has-map"></td>
                    {if $alsendo_region == 'cz'}
                    <td><input type="checkbox" class="ship-via-pickup-point"></td>
                    <td><input type="checkbox" class="pickup-request"></td>
                    {/if}
                    <td><input type="checkbox" class="alsendo-active-toggle" checked></td>
                    <td class="alsendo-actions">
                        <button class="btn btn-success btn-sm confirm-add"><i class="fa fa-check"></i> <span>{l s='Save' mod='alsendo'}</span></button>
                        <button class="btn btn-secondary btn-sm cancel-add"><i class="fa fa-times"></i> <span>{l s='Cancel' mod='alsendo'}</span></button>
                        <span class="unsaved-indicator" style="display:none;"><i class="fa fa-asterisk" style="color: #dc3545; font-size: 16px;"></i></span>
                    </td>
                </tr>
            </script>

            <div class="text-start mt-2">
                <button id="add-method" class="btn btn-outline-primary">{l s='Add Method' mod='alsendo'}</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4><i class="fa-solid fa-list"></i> {l s='Available Courier Services' mod='alsendo'}</h4>
        </div>
        <div class="card-body">
            <p class="alsendo-muted">{l s='Toggle services ON/OFF. Only active services will appear in checkout dropdown and quotes.' mod='alsendo'}</p>
            <div id="alsendo-services-msg" class="mt-2" style="margin-bottom:10px;"></div>

            <div class="alsendo-actions mb-3">
                <button id="alsendo-show-services" class="btn btn-outline-primary">
                    <i class="fa fa-eye"></i> {l s='View Saved Services' mod='alsendo'}
                </button>
                <button id="alsendo-sync-services" class="btn btn-secondary">
                    <i class="fa fa-refresh"></i> {l s='Synchronize with Alsendo' mod='alsendo'}
                </button>
            </div>

            <div id="services-container" style="display: none;">
                <div class="mb-2 text-right">
                    <button id="alsendo-select-all" class="btn btn-outline-secondary btn-sm">
                        {l s='Select All' mod='alsendo'}
                    </button>
                    <button id="alsendo-deselect-all" class="btn btn-outline-secondary btn-sm">
                        {l s='Deselect All' mod='alsendo'}
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="alsendo-services-table">
                        <thead>
                        <tr>
                            <th>{l s='ID' mod='alsendo'}</th>
                            <th>{l s='Service Name' mod='alsendo'}</th>
                            <th>{l s='Supplier' mod='alsendo'}</th>
                            <th>{l s='Delivery Time' mod='alsendo'}</th>
                            <th style="width: 80px; text-align: center;">
                                {l s='Active' mod='alsendo'}
                            </th>
                        </tr>
                        </thead>
                        <tbody id="services-body">
                        </tbody>
                    </table>
                </div>

                <div class="alsendo-actions">
                    <button id="alsendo-save-services" class="btn btn-success">
                        {l s='Save Services' mod='alsendo'}
                    </button>
                    <button id="alsendo-hide-services" class="btn btn-secondary">
                        {l s='Hide' mod='alsendo'}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const alsendoTokenConfig   = '{$token_shipping_config|escape:"javascript":'UTF-8'}';
    const alsendoTokenMethods  = '{$token_shipping_methods|escape:"javascript":'UTF-8'}';
    const alsendoServicesUrl = 'index.php?controller=AdminAlsendoShippingMethods&token=' + alsendoTokenMethods;
    const alsendoAjaxUrl     = alsendoServicesUrl;
    const alsendoRegion = '{$alsendo_region|escape:"javascript":'UTF-8'}';
    {if $alsendo_region == 'cz'}
    var ALSENDO_SERVICES_CAPABILITIES = JSON.parse('{$alsendo_services_capabilities_json|escape:'javascript':'UTF-8'}');
    {/if}
</script>

{literal}
    <script>
        $(function () {
            let allServices = {}; // Store by service_id

            function showMessage(text, ok = true) {
                const msg = $('#alsendo-services-msg');
                msg.text(text)
                    .css({ color: ok ? 'green' : 'red', fontWeight: 'bold' })
                    .show();
                setTimeout(() => msg.fadeOut(), 4000);
            }

            function loadAndRenderServices() {
                console.log('[Alsendo] loadAndRenderServices started');

                fetch(alsendoServicesUrl + '&ajax=1&action=get_available_services')
                    .then(r => r.json())
                    .then(services => {
                        console.log('[Alsendo] Loaded services count:', services.length);

                        // Store by service_id
                        allServices = {};
                        services.forEach(service => {
                            allServices[service.service_id] = service;
                        });

                        renderServicesTable(services);
                    })
                    .catch(err => {
                        console.error('[Alsendo] Load error:', err);
                        showMessage(window.ALSENDO_MSG_LOAD_ERROR + err.message, false);
                    });
            }

            function renderServicesTable(services) {
                console.log('[Alsendo] Rendering', services.length, 'services');

                const tbody = $('#services-body');
                tbody.empty();

                services.forEach((service) => {
                    const isActive = service.active ? 'checked' : '';
                    const isInactive = !service.active ? 'class="inactive"' : '';

                    const row = `
                        <tr data-service-id="${service.service_id}" ${isInactive}>
                            <td>${service.service_id}</td>
                            <td>${service.name || ''}</td>
                            <td>${service.supplier || ''}</td>
                            <td>${service.delivery_time || ''}</td>
                            <td style="text-align: center;">
                                <input type="checkbox"
                                       class="service-toggle"
                                       data-service-id="${service.service_id}"
                                       ${isActive}>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });

                // Attach change handlers
                $('.service-toggle').off('change').on('change', function() {
                    const $row = $(this).closest('tr');
                    if (this.checked) {
                        $row.removeClass('inactive');
                    } else {
                        $row.addClass('inactive');
                    }
                });
            }

            // Select All / Deselect All
            $('#alsendo-select-all').on('click', function() {
                $('.service-toggle').prop('checked', true).trigger('change');
            });
            $('#alsendo-deselect-all').on('click', function() {
                $('.service-toggle').prop('checked', false).trigger('change');
            });

            // Show services table
            $('#alsendo-show-services').off('click').on('click', function() {
                $('#services-container').slideDown();
                loadAndRenderServices();
            });

            // Hide services table
            $('#alsendo-hide-services').off('click').on('click', function() {
                $('#services-container').slideUp();
            });

            // Save services - READ ACTUAL CHECKBOX VALUES!
            $('#alsendo-save-services').off('click').on('click', function() {
                showMessage(window.ALSENDO_MSG_SAVING, true);

                // Build array by reading actual checkbox states
                const servicesToSave = [];
                $('.service-toggle').each(function() {
                    const serviceId = $(this).data('service-id');
                    const isChecked = this.checked;

                    // Get original service data
                    const originalService = allServices[serviceId];
                    if (originalService) {
                        servicesToSave.push({
                            ...originalService,
                            active: isChecked  // Use actual checkbox state!
                        });
                    }
                });

                console.log('[Alsendo] Saving', servicesToSave.length, 'services');

                // Count active/inactive
                const activeCount = servicesToSave.filter(s => s.active).length;
                const inactiveCount = servicesToSave.filter(s => !s.active).length;
                console.log('[Alsendo] Active:', activeCount, ', Inactive:', inactiveCount);

                fetch(alsendoServicesUrl + '&ajax=1&action=save_available_services', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'services=' + encodeURIComponent(JSON.stringify(servicesToSave))
                })
                    .then(r => r.json())
                    .then(resp => {
                        console.log('[Alsendo] Save response:', resp);
                        showMessage(resp.message || (window.ALSENDO_MSG_SAVED || 'Saved!'), !resp.error);
                        if (!resp.error) {
                            setTimeout(() => {
                                loadAndRenderServices();
                                // Reload shipping methods to update dropdown
                                location.reload();
                            }, 500);
                        }
                    })
                    .catch(err => {
                        console.error('[Alsendo] Save error:', err);
                        showMessage(window.ALSENDO_MSG_ERROR_PREFIX + err.message, false);
                    });
            });

            // Sync services
            $('#alsendo-sync-services').off('click').on('click', function() {
                showMessage(window.ALSENDO_MSG_SAVING, true);

                fetch(alsendoServicesUrl + '&ajax=1&action=sync_available_services', {
                    method: 'POST'
                })
                    .then(r => r.json())
                    .then(resp => {
                        console.log('[Alsendo] Sync response:', resp);
                        showMessage(resp.message || window.ALSENDO_MSG_SYNCHRONIZED, !resp.error);
                        if (!resp.error) {
                            $('#services-container').slideDown();
                            setTimeout(() => loadAndRenderServices(), 500);
                        }
                    })
                    .catch(err => {
                        console.error('[Alsendo] Sync error:', err);
                        showMessage(window.ALSENDO_MSG_ERROR_PREFIX + err.message, false);
                    });
            });

            // Shipping methods functionality
            function send(action, data, cb) {
                data.action = action;
                $.ajax({
                    url: alsendoAjaxUrl,
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: cb,
                    error: function (xhr) { console.log('AJAX error', xhr.responseText); }
                });
            }

            $('#add-method').off('click').on('click', function () {
                const tpl = $('#row-template').html();
                const $row = $(tpl);
                // Show unsaved indicator (red asterisk)
                $row.find('.unsaved-indicator').show();
                $('#methods-body').prepend($row);
            });

            $(document).off('click', '.confirm-add').on('click', '.confirm-add', function () {
                const row = $(this).closest('tr');
                const data = {
                    method_name: row.find('.method-name').val(),
                    service_id: row.find('.service-id').val(),
                    price: row.find('.price').val() || 0,
                    has_map: row.find('.has-map').is(':checked') ? 1 : 0,
                    ship_via_pickup_point: row.find('.ship-via-pickup-point').is(':checked') ? 1 : 0,
                    pickup_request: row.find('.pickup-request').is(':checked') ? 1 : 0,
                };
                if (!data.method_name) { showInfoModal(window.ALSENDO_MSG_ENTER_NAME, 'info'); return; }
                send('add', data, function (res) {
                    if (res && res.success && res.id) {
                        // Update row data-id with the returned ID
                        row.attr('data-id', res.id);
                        // Hide unsaved indicator
                        row.find('.unsaved-indicator').hide();
                        // Replace confirm-add/cancel-add buttons with save-row/delete-row buttons
                        row.find('.alsendo-actions').html(
                            '<button class="btn btn-primary btn-sm save-row">' + window.ALSENDO_BTN_SAVE + '</button>' +
                            '<button class="btn btn-danger btn-sm delete-row">' + window.ALSENDO_BTN_DELETE + '</button>'
                        );
                    } else {
                        showErrorModal(window.ALSENDO_MSG_ADD_FAILED);
                    }
                });
            });

            $(document).off('click', '.cancel-add').on('click', '.cancel-add', function () {
                $(this).closest('tr').remove();
            });

            $(document).off('click', '.save-row').on('click', '.save-row', function () {
                const row = $(this).closest('tr');
                const data = {
                    id: row.data('id'),
                    method_name: row.find('.method-name').val(),
                    service_id: row.find('.service-id').val(),
                    price: row.find('.price').val(),
                    has_map: row.find('.has-map').is(':checked') ? 1 : 0,
                    ship_via_pickup_point: row.find('.ship-via-pickup-point').is(':checked') ? 1 : 0,
                    pickup_request: row.find('.pickup-request').is(':checked') ? 1 : 0,
                };
                send('update', data, function (res) {
                    if (!res.success) {
                        showErrorModal(window.ALSENDO_MSG_SAVE_FAILED);
                    } else {
                        showSuccessModal(window.ALSENDO_MSG_SAVED || 'Saved!');
                    }
                });
            });

            $(document).off('click', '.delete-row').on('click', '.delete-row', function () {
                const id = $(this).closest('tr').data('id');
                if (confirm(window.ALSENDO_MSG_DELETE_CONFIRM)) {
                    send('delete', { id: id }, function (res) {
                        if (res && res.success) location.reload(); else showErrorModal(window.ALSENDO_MSG_DELETE_FAILED);
                    });
                }
            });

            // Handle active checkbox changes - update immediately without reload
            $(document).off('change', '.alsendo-active-toggle').on('change', '.alsendo-active-toggle', function () {
                const row = $(this).closest('tr');
                const id = row.data('id');
                if (!id) return; // Skip for new rows

                const data = {
                    id: id,
                    active: this.checked ? 1 : 0,
                };
                $.ajax({
                    url: alsendoAjaxUrl,
                    type: 'POST',
                    data: { action: 'update_active', ...data },
                    dataType: 'json',
                    success: function (res) {
                        if (!res.success) {
                            // Revert checkbox on failure
                            row.find('.alsendo-active-toggle').prop('checked', !data.active);
                            showErrorModal(window.ALSENDO_MSG_UPDATE_FAILED);
                        }
                    },
                    error: function (xhr) {
                        // Revert checkbox on error
                        row.find('.active').prop('checked', !data.active);
                        showErrorModal(window.ALSENDO_MSG_ERROR_PREFIX + xhr.responseText);
                    }
                });
            });

            // CZ: capability-dependent checkboxes + mutual exclusivity
            if (alsendoRegion === 'cz' && typeof ALSENDO_SERVICES_CAPABILITIES !== 'undefined') {
                function updateRowCheckboxCapabilities(row) {
                    var serviceId = row.find('.service-id').val();
                    var shipViaCb = row.find('.ship-via-pickup-point');
                    var pickupReqCb = row.find('.pickup-request');
                    var hasMapCb = row.find('.has-map');
                    if (!shipViaCb.length || !pickupReqCb.length) return;

                    // Auto-check map for new rows when service has to_point capability
                    if (serviceId && !row.data('id') && hasMapCb.length) {
                        var svcForMap = null;
                        for (var j = 0; j < ALSENDO_SERVICES_CAPABILITIES.length; j++) {
                            if (String(ALSENDO_SERVICES_CAPABILITIES[j].service_id) === String(serviceId)) {
                                svcForMap = ALSENDO_SERVICES_CAPABILITIES[j];
                                break;
                            }
                        }
                        if (svcForMap && (svcForMap.door_to_point || svcForMap.point_to_point)) {
                            hasMapCb.prop('checked', true);
                        }
                    }

                    if (!serviceId) {
                        // "Any Service" — both enabled, mutual exclusivity still applies
                        shipViaCb.prop('disabled', false);
                        pickupReqCb.prop('disabled', false);
                    } else {
                        var svc = null;
                        for (var i = 0; i < ALSENDO_SERVICES_CAPABILITIES.length; i++) {
                            if (String(ALSENDO_SERVICES_CAPABILITIES[i].service_id) === String(serviceId)) {
                                svc = ALSENDO_SERVICES_CAPABILITIES[i];
                                break;
                            }
                        }
                        var canShipVia = svc && (svc.point_to_point || svc.point_to_door);
                        var canPickupReq = svc && (svc.door_to_point || svc.door_to_door);

                        if (!canShipVia) {
                            shipViaCb.prop('checked', false).prop('disabled', true);
                        } else {
                            shipViaCb.prop('disabled', pickupReqCb.is(':checked'));
                        }
                        if (!canPickupReq) {
                            pickupReqCb.prop('checked', false).prop('disabled', true);
                        } else {
                            pickupReqCb.prop('disabled', shipViaCb.is(':checked'));
                        }
                    }

                    // Mutual exclusivity
                    if (shipViaCb.is(':checked')) {
                        pickupReqCb.prop('disabled', true);
                    } else if (pickupReqCb.is(':checked')) {
                        shipViaCb.prop('disabled', true);
                    }
                }

                // On service dropdown change
                $(document).off('change', '.service-id').on('change', '.service-id', function() {
                    var row = $(this).closest('tr');
                    updateRowCheckboxCapabilities(row);
                });

                // Initialize capabilities for existing rows
                $('#methods-body tr').each(function() {
                    updateRowCheckboxCapabilities($(this));
                });

                // Ship via checkbox change — mutual exclusivity + AJAX
                $(document).off('change', '.ship-via-pickup-point').on('change', '.ship-via-pickup-point', function() {
                    var row = $(this).closest('tr');
                    var id = row.data('id');
                    var pickupReqCb = row.find('.pickup-request');
                    if (this.checked) {
                        pickupReqCb.prop('checked', false).prop('disabled', true);
                    } else {
                        updateRowCheckboxCapabilities(row);
                    }
                    if (!id) return;
                    $.ajax({
                        url: alsendoAjaxUrl,
                        type: 'POST',
                        data: { action: 'update_ship_via', id: id, ship_via_pickup_point: this.checked ? 1 : 0 },
                        dataType: 'json',
                        error: function() {
                            row.find('.ship-via-pickup-point').prop('checked', !row.find('.ship-via-pickup-point').is(':checked'));
                            showErrorModal(window.ALSENDO_MSG_UPDATE_FAILED);
                        }
                    });
                });

                // Pickup request checkbox change
                $(document).off('change', '.pickup-request').on('change', '.pickup-request', function() {
                    var row = $(this).closest('tr');
                    var id = row.data('id');
                    var shipViaCb = row.find('.ship-via-pickup-point');
                    if (this.checked) {
                        shipViaCb.prop('checked', false).prop('disabled', true);
                    } else {
                        updateRowCheckboxCapabilities(row);
                    }
                    if (!id) return;
                    $.ajax({
                        url: alsendoAjaxUrl,
                        type: 'POST',
                        data: { action: 'update_pickup_request', id: id, pickup_request: this.checked ? 1 : 0 },
                        dataType: 'json',
                        error: function() {
                            row.find('.pickup-request').prop('checked', !row.find('.pickup-request').is(':checked'));
                            showErrorModal(window.ALSENDO_MSG_UPDATE_FAILED);
                        }
                    });
                });
            }

            // Handle has_map checkbox changes - update immediately without reload
            $(document).off('change', '.has-map').on('change', '.has-map', function () {
                const row = $(this).closest('tr');
                const id = row.data('id');
                if (!id) return; // Skip for new rows

                const data = {
                    id: id,
                    has_map: this.checked ? 1 : 0,
                };
                $.ajax({
                    url: alsendoAjaxUrl,
                    type: 'POST',
                    data: { action: 'update_map', ...data },
                    dataType: 'json',
                    success: function (res) {
                        if (!res.success) {
                            // Revert checkbox on failure
                            row.find('.has-map').prop('checked', !data.has_map);
                            showErrorModal(window.ALSENDO_MSG_UPDATE_FAILED);
                        }
                    },
                    error: function (xhr) {
                        // Revert checkbox on error
                        row.find('.has-map').prop('checked', !data.has_map);
                        showErrorModal(window.ALSENDO_MSG_ERROR_PREFIX + xhr.responseText);
                    }
                });
            });
        });
    </script>
{/literal}