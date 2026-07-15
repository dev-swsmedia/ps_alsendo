{*
* Alsendo - PrestaShop shipping module
*
* @author    Innovation Software
* @copyright 2026 Innovation Software
* @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
*}

<script>
    window.ALSENDO_MSG_SAVED = "{$alsendo_msg_saved|default:'Saved!'|escape:'javascript':'UTF-8'}";
    window.ALSENDO_MSG_ERROR = "{$alsendo_msg_error|default:'Error'|escape:'javascript':'UTF-8'}";
    window.ALSENDO_MSG_SET_DEFAULT = "{$alsendo_msg_set_default|default:'Set as default!'|escape:'javascript':'UTF-8'}";
    window.ALSENDO_PACKAGE_TYPES_LIST = JSON.parse('{if $alsendo_package_types}{$alsendo_package_types|json_encode|escape:'javascript':'UTF-8'}{else}[]{/if}');
</script>
<link rel="stylesheet" href="{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}alsendo/views/css/alsendo-admin.css">
<style>
    .alsendo-flex-row {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }

    /* Override PrestaShop's button uppercase styling */
    .btn,
    button.btn {
        text-transform: none !important;
    }

    .alsendo-panel-half {
        flex: 1 1 0;
        min-width: 0;
    }

    .alsendo-panel-full {
        width: 100%;
        margin-bottom: 20px;
    }

    .alsendo-panel-icon {
        margin-right: 10px;
        font-size: 1.3em;
        vertical-align: middle;
    }

    .alsendo-sender-card {
        border: 1px solid #ccc;
        border-radius: 6px;
        padding: 12px 16px;
        margin-bottom: 10px;
        background: #fafbfc;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        justify-content: flex-start;
    }

    .alsendo-sender-card-labels {
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
        width: 100%;
    }

    .alsendo-sender-card-labels span {
        width: 30%;
    }

    .alsendo-sender-card-actions {
        display: flex;
        gap: 8px;
    }

    .alsendo-sender-edit-fields {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .alsendo-sender-edit-fields input {
        max-width: 100%;
        width: 100% !important;
        min-width: 0;
        display: inline-block;
        margin-right: 2%;
        margin-bottom: 8px;
        box-sizing: border-box;
    }

    .alsendo-sender-edit-fields input:nth-child(2n) {
        margin-right: 0;
    }

    /* Validation error styles */
    .alsendo-error-input {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }

    .alsendo-error-message {
        color: #dc3545;
        font-size: 12px;
        margin-top: 4px;
        display: block;
    }

    .alsendo-error-panel {
        border-color: #dc3545 !important;
    }
</style>
<script>
    var alsendoAjaxUrl = "{$link->getAdminLink('AdminAlsendoModuleConfiguration', true)|escape:'javascript':'UTF-8'}";
</script>
{literal}
    <script>
        const AlsendoService = {
            ajaxUrl: window.alsendoAjaxUrl,
            save: function(panel, data, cb) {

                const params = new URLSearchParams(Object.assign({action: 'save_'+panel, ajax: 1}, data)).toString();
                fetch(this.ajaxUrl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: params
                })
                    .then(r => r.json())
                    .then(cb)
                    .catch(() => cb({error: true, message: 'AJAX error'}));
            }
        };

        const AlsendoValidation = {
            clearErrors: function(container) {
                if (typeof container === 'string') {
                    container = document.querySelector(container);
                }
                if (!container) return;

                // Remove error classes from inputs
                container.querySelectorAll('.alsendo-error-input').forEach(function(el) {
                    el.classList.remove('alsendo-error-input');
                });

                // Remove error messages
                container.querySelectorAll('.alsendo-error-message').forEach(function(el) {
                    el.remove();
                });

                // Remove panel error highlights
                container.querySelectorAll('.alsendo-error-panel').forEach(function(el) {
                    el.classList.remove('alsendo-error-panel');
                });
            },

            showFieldError: function(fieldId, errorMessage) {
                var field = document.getElementById(fieldId);
                if (!field) {
                    field = document.querySelector('.' + fieldId);
                }
                if (!field) return;

                // Add error class to input
                field.classList.add('alsendo-error-input');

                // Remove existing error message if any
                var existingError = field.parentElement.querySelector('.alsendo-error-message');
                if (existingError) {
                    existingError.remove();
                }

                // Add error message
                var errorEl = document.createElement('span');
                errorEl.className = 'alsendo-error-message';
                errorEl.textContent = errorMessage;
                field.parentElement.appendChild(errorEl);

                // Highlight panel
                var panel = field.closest('.panel');
                if (panel) {
                    panel.classList.add('alsendo-error-panel');
                }
            },

            showErrors: function(errors, panelSelector) {
                if (!errors || typeof errors !== 'object') return;

                // Clear previous errors in the panel
                if (panelSelector) {
                    this.clearErrors(panelSelector);
                }

                // Show each error
                for (var fieldKey in errors) {
                    if (errors.hasOwnProperty(fieldKey)) {
                        this.showFieldError(fieldKey, errors[fieldKey]);
                    }
                }
            },

            parseSenderErrors: function(errors, templateIdx) {
                var parsedErrors = {};
                if (!errors || typeof errors !== 'object') return parsedErrors;

                for (var key in errors) {
                    if (errors.hasOwnProperty(key)) {
                        // Check if error is for current sender template (e.g., "sender[0].firstname")
                        var match = key.match(/^sender\[(\d+)\]\.(.+)$/);
                        if (match && parseInt(match[1]) === parseInt(templateIdx)) {
                            var fieldName = match[2];
                            parsedErrors['alsendo_sender_' + fieldName] = errors[key];
                        }
                    }
                }
                return parsedErrors;
            },

            parseShippingErrors: function(errors, templateIdx) {
                var parsedErrors = {};
                if (!errors || typeof errors !== 'object') return parsedErrors;

                for (var key in errors) {
                    if (errors.hasOwnProperty(key)) {
                        // Check if error is for current shipping template (e.g., "package[0].width")
                        var match = key.match(/^package\[(\d+)\]\.(.+)$/);
                        if (match && parseInt(match[1]) === parseInt(templateIdx)) {
                            var fieldName = match[2];
                            // Map backend field names to frontend class names
                            var fieldMap = {
                                'template_name': 'alsendo_template_name',
                                'width': 'alsendo_width',
                                'length': 'alsendo_length',
                                'height': 'alsendo_height',
                                'weight': 'alsendo_weight',
                                'cod': 'alsendo_cod',
                                'declared_value': 'alsendo_declared_value',
                                'package_type': 'alsendo_package_type',
                                'pickup_type': 'alsendo_pickup_type'
                            };
                            var mappedField = fieldMap[fieldName] || fieldName;
                            parsedErrors[mappedField] = errors[key];
                        }
                    }
                }
                return parsedErrors;
            }
        };
    </script>
{/literal}

<div class="row">
    <div class="col-md-12">
        <div class="panel" id="alsendo-panel-config">
            <div class="panel-heading">
                <i class="icon-cog"></i>
                {l s='Region and API Configuration' mod='alsendo'}
            </div>
            <div class="form-wrapper">
                <div id="alsendo-config-msg"></div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="alsendo_region">{l s='Select region' mod='alsendo'}</label>
                            <select name="alsendo_region" id="alsendo_region" class="form-control">
                                <option value="pl" {if $alsendo_region == 'pl'} selected{/if}>Poland</option>
                                <option value="cz" {if $alsendo_region == 'cz'} selected{/if}>Czechia</option>
                                <option value="ro" {if $alsendo_region == 'ro'} selected{/if}>Romania</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="form-group" style="padding-top: 28px;">
                            <label for="alsendo_test_mode" style="font-weight: normal;">
                                <input type="checkbox" id="alsendo_test_mode" name="alsendo_test_mode" value="1" {if $alsendo_test_mode}checked{/if}>
                                {l s='Test Mode (use test API endpoints)' mod='alsendo'}
                            </label>
                            <p class="help-block" style="margin-top: 5px; margin-bottom: 0;">{l s='Enable this to use test environment for API calls' mod='alsendo'}</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group" id="alsendo-app-id-group">
                            <label for="alsendo_app_id">{l s='APP ID' mod='alsendo'}</label>
                            <input type="text" id="alsendo_app_id" name="alsendo_app_id" class="form-control"
                                   value="{$alsendo_app_id|escape:'html':'UTF-8'}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" id="alsendo-app-secret-group">
                            <label for="alsendo_secret">{l s='APP Secret' mod='alsendo'}</label>
                            <input type="password" id="alsendo_secret" name="alsendo_secret" class="form-control"
                                   value="{$alsendo_secret|escape:'html':'UTF-8'}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group" id="alsendo-api-key-group">
                            <label for="alsendo_api_key">{l s='API Key' mod='alsendo'}</label>
                            <input type="password" id="alsendo_api_key" name="alsendo_api_key" class="form-control"
                                   value="{$alsendo_api_key|escape:'html':'UTF-8'}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" id="alsendo-token-group">
                            <label for="alsendo_token">{l s='Token' mod='alsendo'}</label>
                            <input type="password" id="alsendo_token" name="alsendo_token" class="form-control"
                                   value="{$alsendo_token|escape:'html':'UTF-8'}">
                        </div>
                    </div>
                </div>
                <div id="alsendo-ecolet-oauth-group">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" id="alsendo-ro-client-id-group">
                                <label for="alsendo_ro_client_id">{l s='Client ID' mod='alsendo'}</label>
                                <input type="text" id="alsendo_ro_client_id" name="alsendo_ro_client_id" class="form-control"
                                       value="{$alsendo_ro_client_id|escape:'html':'UTF-8'}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" id="alsendo-ro-client-secret-group">
                                <label for="alsendo_ro_client_secret">{l s='Client Secret' mod='alsendo'}</label>
                                <input type="password" id="alsendo_ro_client_secret" name="alsendo_ro_client_secret" class="form-control"
                                       value="{$alsendo_ro_client_secret|escape:'html':'UTF-8'}">
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 8px;">
                        <p>{l s='Register your OAuth app here:' mod='alsendo'} <a href="https://panel.ecolet.ro/en/account/oauth/clients" target="_blank">https://panel.ecolet.ro/en/account/oauth/clients</a></p>
                        <p class="mb-0 pb-0">{l s='Set this as Redirect URL when registering:' mod='alsendo'}</p>
                        <p class="fw-semibold" style="font-weight: 600;">{$alsendo_ecolet_redirect_url|escape:'html':'UTF-8'}</p>
                        {if $alsendo_ecolet_is_localhost}
                            <p style="color:#ff0000; font-weight: bold;">
                                LOCALHOST DETECTED. {l s='For development, use' mod='alsendo'} 'https://google.com' {l s='for redirect URL when registering a new app. Once redirected to Google, 1) replace' mod='alsendo'} 'https://google.com' {l s='in the URL with the link above, 2) replace "?code" with "&code" and hit enter.' mod='alsendo'}
                            </p>
                        {/if}
                        <div style="display: flex; align-items: center; gap: 16px; margin-top: 8px;">
                            <button type="button" class="btn btn-outline-primary" id="ecolet-authorize-btn">
                                {l s='Authorize with Ecolet' mod='alsendo'}
                            </button>
                            {if $alsendo_ecolet_authorized}
                                <span style="color: #28a745; font-weight: bold;">{l s='Connected' mod='alsendo'}</span>
                            {else}
                                <span style="color: #dc3545; font-weight: bold;">{l s='Not connected' mod='alsendo'}</span>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn btn-default pull-right" id="alsendo-save-config">
                    <i class="process-icon-save"></i>
                    {l s='Save Configuration' mod='alsendo'}
                </button>
            </div>
        </div>
    </div>
</div>
{literal}
    <script>
        function updateApiKeyFieldsByRegion(region) {
            document.getElementById('alsendo-app-id-group').style.display = 'none';
            document.getElementById('alsendo-app-secret-group').style.display = 'none';
            document.getElementById('alsendo-api-key-group').style.display = 'none';
            document.getElementById('alsendo-token-group').style.display = 'none';
            document.getElementById('alsendo-ecolet-oauth-group').style.display = 'none';
            if (region === 'pl') {
                document.getElementById('alsendo-app-id-group').style.display = '';
                document.getElementById('alsendo-app-secret-group').style.display = '';
            } else if (region === 'cz') {
                document.getElementById('alsendo-api-key-group').style.display = '';
            } else if (region === 'ro') {
                document.getElementById('alsendo-ecolet-oauth-group').style.display = '';
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            var regionSelect = document.getElementById('alsendo_region');
            updateApiKeyFieldsByRegion(regionSelect.value);
            regionSelect.addEventListener('change', function() {
                updateApiKeyFieldsByRegion(this.value);
                // Fetch package types for the new region
                fetch(alsendoAjaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'ajax=1&action=get_package_types_for_region&region=' + encodeURIComponent(this.value)
                })
                .then(function(r) { return r.json(); })
                .then(function(json) {
                    if (json.package_types) {
                        window.ALSENDO_PACKAGE_TYPES_LIST = json.package_types;
                    }
                    if (typeof renderShippingForm === 'function') {
                        renderShippingForm();
                    }
                })
                .catch(function() {
                    if (typeof renderShippingForm === 'function') {
                        renderShippingForm();
                    }
                });
            });
        });
    </script>
{/literal}

<div class="row">
    <div class="col-md-12">
        <div class="panel" id="panel-default-sender-address">
            <div class="panel-heading">
                <i class="icon-user"></i>
                {l s='Default sender address' mod='alsendo'}
            </div>
            <div class="form-wrapper">
                <div class="form-group" style="margin-bottom:20px">
                    <label for="alsendo_sender_template_select">{l s='Select template' mod='alsendo'}</label>
                    <select id="alsendo_sender_template_select" class="custom-select"></select>
                </div>
                {if $alsendo_region == 'cz' && !empty($alsendo_address_book)}
                <div class="form-group" id="alsendo-address-book-wrapper" style="margin-bottom:20px">
                    <label>{l s='Address from address book' mod='alsendo'}</label>
                    <select class="custom-select" id="alsendo-address-book-select">
                        <option value="">{l s='Select address...' mod='alsendo'}</option>
                        {foreach $alsendo_address_book as $abId => $ab}
                            <option value="{$abId|escape:'html':'UTF-8'}" data-address='{$ab|json_encode|escape:'htmlall':'UTF-8'}'>
                                {if $ab.company}{$ab.company|escape:'html':'UTF-8'} - {/if}
                                {$ab.firstname|escape:'html':'UTF-8'} {$ab.surname|escape:'html':'UTF-8'} ({$ab.street|escape:'html':'UTF-8'}, {$ab.city|escape:'html':'UTF-8'})
                            </option>
                        {/foreach}
                    </select>
                </div>
                {/if}
                <div id="alsendo-sender-list"></div>
                <div id="alsendo-template-modal"
                     style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);z-index:9999;align-items:center;justify-content:center;">
                    <div style="background:#fff;padding:32px 24px;border-radius:8px;max-width:400px;margin:auto;">
                        <h4>{l s='Enter template name' mod='alsendo'}</h4>
                        <input type="text" id="alsendo-template-name-input" class="form-control"
                               style="margin-bottom:16px;">
                        <button class="btn btn-primary"
                                id="alsendo-template-modal-save">{l s='Save' mod='alsendo'}</button>
                        <button class="btn btn-secondary"
                                id="alsendo-template-modal-cancel">{l s='Cancel' mod='alsendo'}</button>
                    </div>
                </div>
            </div>
            <div id="alsendo-settings-msg" style="margin-top: 10px;"></div>
        </div>
    </div>
</div>
<script>
    var alsendoSenderList = [];
    try {
        alsendoSenderList = JSON.parse('{$alsendo_sender_list|escape:'javascript':'UTF-8'}');
        console.log('alsendoSenderList loaded:', alsendoSenderList);
    } catch (e) {
        alsendoSenderList = [];
        console.log('alsendoSenderList parse error:', e);
    }
    var alsendoSenderEditIdx = null;
    var alsendoSelectedTemplateIdx = 'new';

    // B.8: Auto-select default (main) sender template after page load
    if (alsendoSenderList && alsendoSenderList.length > 0) {
        for (var i = 0; i < alsendoSenderList.length; i++) {
            if (alsendoSenderList[i].main === true) {
                alsendoSelectedTemplateIdx = i;
                break;
            }
        }
        if (alsendoSelectedTemplateIdx === 'new') alsendoSelectedTemplateIdx = 0;
    }

    function updateSenderDropdown() {
        var select = document.getElementById('alsendo_sender_template_select');
        select.innerHTML = '';
        var newOpt = document.createElement('option');
        newOpt.value = 'new';
        newOpt.textContent = '{l s='New template' mod='alsendo'}';
        select.appendChild(newOpt);

        if (alsendoSenderList && alsendoSenderList.length > 0) {
            alsendoSenderList.forEach(function(sender, idx) {
                var name = sender.template_name || sender.company || '{l s='Unnamed' mod='alsendo'}';
                if (sender.main === true) {
                    name += ' - default';
                }
                var option = document.createElement('option');
                option.value = idx;
                option.textContent = name;
                select.appendChild(option);
            });
        }

        // Validate selected index
        if (alsendoSelectedTemplateIdx !== 'new' &&
            (!alsendoSenderList || alsendoSenderList.length === 0 ||
                alsendoSelectedTemplateIdx >= alsendoSenderList.length)) {
            alsendoSelectedTemplateIdx = 'new';
        }

        select.value = alsendoSelectedTemplateIdx;
    }

    // CZ: Address book selection handler
    function onAddressBookSelect(select) {
        var option = select.options[select.selectedIndex];
        if (!option || !option.value) {
            setSenderFieldsReadonly(false);
            var extId = document.querySelector('.alsendo_sender_external_id');
            if (extId) extId.value = '';
            return;
        }
        try {
            var addr = JSON.parse(option.getAttribute('data-address'));
            // Split street + building number (same logic as full form)
            var streetVal = addr.street || '';
            var buildingVal = '';
            {literal}
            if (streetVal) {
                var match = streetVal.match(/^(.+)\s+(\d+\w{0,5}(?:\s*\/\s*\S+)?)\s*$/);
                if (match) {
                    streetVal = match[1].trim();
                    buildingVal = match[2].trim();
                }
            }
            {/literal}
            var fields = {
                '.alsendo_sender_company': addr.company || '',
                '.alsendo_sender_firstname': addr.firstname || '',
                '.alsendo_sender_lastname': addr.surname || '',
                '.alsendo_sender_street': streetVal,
                '.alsendo_sender_building': buildingVal,
                '.alsendo_sender_postal': addr.zip || '',
                '.alsendo_sender_city': addr.city || '',
                '.alsendo_sender_phone': addr.phone || '',
                '.alsendo_sender_email': addr.email || '',
                '.alsendo_sender_contact': ((addr.firstname || '') + ' ' + (addr.surname || '')).trim()
            };
            for (var sel in fields) {
                var el = document.querySelector(sel);
                if (el) el.value = fields[sel];
            }
            var extId = document.querySelector('.alsendo_sender_external_id');
            if (extId) extId.value = addr.id || '';
            setSenderFieldsReadonly(true);
        } catch(e) { console.error('Address book parse error', e); }
    }

    function setSenderFieldsReadonly(readonly) {
        var sels = ['.alsendo_sender_company','.alsendo_sender_firstname','.alsendo_sender_lastname',
            '.alsendo_sender_street','.alsendo_sender_building','.alsendo_sender_apartment',
            '.alsendo_sender_postal','.alsendo_sender_city','.alsendo_sender_contact',
            '.alsendo_sender_phone','.alsendo_sender_email'];
        sels.forEach(function(s) {
            var el = document.querySelector(s);
            if (el) {
                el.readOnly = readonly;
                el.style.backgroundColor = readonly ? '#f0f0f0' : '';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateSenderDropdown();
        renderSenderList();
        document.getElementById('alsendo_sender_template_select').onchange = function() {
            alsendoSelectedTemplateIdx = this.value;
            renderSenderList();
        };

        // CZ: Address book dropdown handler
        var abSelect = document.getElementById('alsendo-address-book-select');
        if (abSelect) {
            abSelect.onchange = function() { onAddressBookSelect(this); };
        }

        var modalSaveBtn = document.getElementById('alsendo-template-modal-save');
        var modalCancelBtn = document.getElementById('alsendo-template-modal-cancel');
        if (modalSaveBtn) {
            modalSaveBtn.onclick = function() {
                var name = document.getElementById('alsendo-template-name-input').value.trim();
                if (!name) return;

                // Clear previous errors
                AlsendoValidation.clearErrors('#panel-default-sender-address');

                var data = {
                    company: document.querySelector('.alsendo_sender_company').value,
                    firstname: document.querySelector('.alsendo_sender_firstname').value,
                    lastname: document.querySelector('.alsendo_sender_lastname').value,
                    street: document.querySelector('.alsendo_sender_street').value,
                    building: document.querySelector('.alsendo_sender_building').value,
                    apartment: document.querySelector('.alsendo_sender_apartment') ? document.querySelector('.alsendo_sender_apartment').value : '',
                    block: document.querySelector('.alsendo_sender_block') ? document.querySelector('.alsendo_sender_block').value : '',
                    entrance: document.querySelector('.alsendo_sender_entrance') ? document.querySelector('.alsendo_sender_entrance').value : '',
                    floor: document.querySelector('.alsendo_sender_floor') ? document.querySelector('.alsendo_sender_floor').value : '',
                    flat: document.querySelector('.alsendo_sender_flat') ? document.querySelector('.alsendo_sender_flat').value : '',
                    postal: document.querySelector('.alsendo_sender_postal').value,
                    city: document.querySelector('.alsendo_sender_city').value,
                    contact: document.querySelector('.alsendo_sender_contact').value,
                    phone: document.querySelector('.alsendo_sender_phone').value,
                    email: document.querySelector('.alsendo_sender_email').value,
                    bank: document.querySelector('.alsendo_sender_bank').value,
                    bank_code: (document.querySelector('.alsendo_sender_bank_code') || {}).value || '',
                    additional_bank_account_number: (document.querySelector('.alsendo_sender_additional_bank') || {}).value || '',
                    external_id: (document.querySelector('.alsendo_sender_external_id') || {}).value || '',
                    address_type: document.querySelector('.alsendo_sender_address_type').value,
                    template_name: name
                };
                alsendoSenderList.push(data);
                var newIdx = alsendoSenderList.length - 1;
                alsendoSelectedTemplateIdx = newIdx;

                AlsendoService.save('settings', {
                    alsendo_sender_list: JSON.stringify(alsendoSenderList)
                }, function(resp) {
                    var msgElem = document.getElementById('alsendo-settings-msg');
                    msgElem.innerText = resp.message || (resp.error ? (window.ALSENDO_MSG_ERROR || 'Error') : (window.ALSENDO_MSG_SAVED || 'Saved!'));
                    msgElem.className = resp.error ? 'alert alert-danger' : 'alert alert-success';

                    if (resp.error && resp.errors) {
                        // If there's an error, remove the template we just added
                        alsendoSenderList.pop();
                        alsendoSelectedTemplateIdx = 'new';

                        // Parse errors for the new template (last index)
                        var templateErrors = AlsendoValidation.parseSenderErrors(resp.errors, newIdx);
                        AlsendoValidation.showErrors(templateErrors, '#panel-default-sender-address');
                    } else {
                        document.getElementById('alsendo-template-modal').style.display = 'none';
                        updateSenderDropdown();
                        renderSenderList();
                    }
                });
            };
        }
        if (modalCancelBtn) {
            modalCancelBtn.onclick = function() {
                document.getElementById('alsendo-template-modal').style.display = 'none';
            };
        }
    });

    function renderSenderList() {
        var html = '';
        if (!Array.isArray(alsendoSenderList) || alsendoSenderList.length === 0) alsendoSenderList = [];
        var sender = null;
        if (alsendoSelectedTemplateIdx === 'new') {
            sender = {
                company: '',
                firstname: '',
                lastname: '',
                street: '',
                building: '',
                apartment: '',
                block: '',
                entrance: '',
                floor: '',
                flat: '',
                postal: '',
                city: '',
                contact: '',
                phone: '',
                email: '',
                bank: '',
                address_type: 'company'
            };
        } else {
            sender = alsendoSenderList[alsendoSelectedTemplateIdx] || {
                company: '',
                firstname: '',
                lastname: '',
                street: '',
                building: '',
                apartment: '',
                block: '',
                entrance: '',
                floor: '',
                flat: '',
                postal: '',
                city: '',
                contact: '',
                phone: '',
                email: '',
                bank: '',
                address_type: 'company'
            };
        }
        html += '<div class="alsendo-sender-card" data-idx="' + alsendoSelectedTemplateIdx + '">';
        var addressType = sender.address_type || 'company';
        html += '<div style="margin-bottom:20px;width:100%;display:block;clear:both">';
        html += '<label style="display:block;margin-bottom:8px;font-weight:bold">{l s='Address type' mod='alsendo'}</label>';
        html += '<div style="display:flex;gap:10px;width:100%">';
        html += '<button type="button" class="btn btn-outline-secondary alsendo-config-address-type-btn '+(addressType==='company'?'active':'')+'" data-value="company" style="flex:1">{l s='Company' mod='alsendo'}</button>';
        html += '<button type="button" class="btn btn-outline-secondary alsendo-config-address-type-btn '+(addressType==='home'?'active':'')+'" data-value="home" style="flex:1">{l s='Home' mod='alsendo'}</button>';
        html += '</div>';
        html += '<input type="hidden" class="alsendo_sender_address_type" value="'+addressType+'">';
        html += '</div>';
        html += '<div class="alsendo-sender-edit-fields" style="display:flex;gap:16px;width:100%">';
        html += '<div style="flex:1">';
        html += '<div id="alsendo-config-company-row" style="'+(addressType==='home'?'display:none':'margin-bottom:15px')+'">';
        html += '<label>{l s='Company name' mod='alsendo'}'+(addressType==='company'?'*':'')+'</label><input type="text" class="form-control alsendo_sender_company" value="'+(sender.company||'')+'" '+(addressType==='company'?'required':'')+' data-address-type="'+addressType+'">';
        html += '</div>';
        html += '<div style="margin-bottom:15px;"><label>{l s='First name' mod='alsendo'}*</label><input type="text" class="form-control alsendo_sender_firstname" value="'+(sender.firstname||'')+'" required></div>';
        html += '<div style="margin-bottom:15px;"><label>{l s='Last name' mod='alsendo'}*</label><input type="text" class="form-control alsendo_sender_lastname" value="'+(sender.lastname||'')+'" required></div>';
        html += '<div style="margin-bottom:15px;"><label>{l s='Street' mod='alsendo'}*</label><input type="text" class="form-control alsendo_sender_street" value="'+(sender.street||'')+'" required></div>';
        html += '<div style="margin-bottom:15px;"><label>{l s='Building number' mod='alsendo'}*</label><input type="text" class="form-control alsendo_sender_building" value="'+(sender.building||'')+'" required></div>';
        var currentRegion = document.getElementById('alsendo_region') ? document.getElementById('alsendo_region').value : '{$alsendo_region|escape:'javascript':'UTF-8'}';
        if (currentRegion === 'ro') {
            html += '<div style="display:flex;gap:10px;margin-bottom:15px;">';
            html += '<div style="flex:1"><label>{l s='Bloc' mod='alsendo'}</label><input type="text" class="form-control alsendo_sender_block" value="'+(sender.block||'')+'"></div>';
            html += '<div style="flex:1"><label>{l s='Scara' mod='alsendo'}</label><input type="text" class="form-control alsendo_sender_entrance" value="'+(sender.entrance||'')+'"></div>';
            html += '<div style="flex:1"><label>{l s='Etaj' mod='alsendo'}</label><input type="text" class="form-control alsendo_sender_floor" value="'+(sender.floor||'')+'"></div>';
            html += '<div style="flex:1"><label>{l s='Apartament' mod='alsendo'}</label><input type="text" class="form-control alsendo_sender_flat" value="'+(sender.flat||'')+'"></div>';
            html += '</div>';
        } else {
            html += '<div style="margin-bottom:15px;"><label>{l s='Apartment number' mod='alsendo'}</label><input type="text" class="form-control alsendo_sender_apartment" value="'+(sender.apartment||'')+'"></div>';
        }
        html += '<div style="margin-bottom:15px;"><label>{l s='Postal code' mod='alsendo'}*</label><input type="text" class="form-control alsendo_sender_postal" value="'+(sender.postal||'')+'" required></div>';
        html += '<div style="margin-bottom:15px;"><label>{l s='City' mod='alsendo'}*</label><input type="text" class="form-control alsendo_sender_city" value="'+(sender.city||'')+'" required></div>';
        html += '</div>';
        html += '<div style="flex:1">';
        html += '<div style="margin-bottom:15px;"><label>{l s='Contact person' mod='alsendo'}*</label><input type="text" class="form-control alsendo_sender_contact" value="'+(sender.contact||'')+'" required></div>';
        html += '<div style="margin-bottom:15px;"><label>{l s='Phone number' mod='alsendo'}*</label><input type="text" class="form-control alsendo_sender_phone" value="'+(sender.phone||'')+'" required></div>';
        html += '<div style="margin-bottom:15px;"><label>{l s='Email address' mod='alsendo'}*</label><input type="email" class="form-control alsendo_sender_email" value="'+(sender.email||'')+'" required></div>';
        html += '<div style="margin-bottom:15px;"><label>{l s='Bank account number' mod='alsendo'}</label><input type="text" class="form-control alsendo_sender_bank" value="'+(sender.bank||'')+'"></div>';
        if (currentRegion === 'cz') {
            html += '<div style="margin-bottom:15px;"><label>{l s='Bank code' mod='alsendo'}</label><input type="text" class="form-control alsendo_sender_bank_code" maxlength="4" value="'+(sender.bank_code||'')+'"></div>';
            html += '<div style="margin-bottom:15px;"><label>{l s='IBAN bank account number' mod='alsendo'}</label><input type="text" class="form-control alsendo_sender_additional_bank" value="'+(sender.additional_bank_account_number||'')+'"></div>';
            html += '<input type="hidden" class="alsendo_sender_external_id" value="'+(sender.external_id||'')+'">';
        }
        html += '</div>';
        html += '</div>';
        html += '<div style="width:100%;margin-top:15px;display:flex;gap:10px;justify-content:space-between;align-items:center">';
        html += '<div style="display:flex;gap:10px">';
        if (alsendoSelectedTemplateIdx !== 'new') {
            var isMain = sender.main === true;
            html += '<button class="btn btn-primary alsendo-update-sender-edit" type="button">{l s='Update' mod='alsendo'}</button>';
            if (!isMain) {
                html += '<button class="btn btn-warning alsendo-set-main-sender" type="button">{l s='Set as Default' mod='alsendo'}</button>';
            }
            html += '<button class="btn btn-danger alsendo-remove-sender" type="button">{l s='Remove' mod='alsendo'}</button>';
        } else {
            html += '<button class="btn btn-success alsendo-save-sender-edit" type="button">{l s='Save' mod='alsendo'}</button>';
        }
        html += '</div>';
        if (alsendoSelectedTemplateIdx !== 'new' && sender.main === true) {
            html += '<span style="color:#777;font-size:13px;font-style:italic">{l s='Default template' mod='alsendo'}</span>';
        }
        html += '</div>';
        html += '</div>';
        document.getElementById('alsendo-sender-list').innerHTML = html;

        var saveBtn = document.querySelector('.alsendo-save-sender-edit');
        if (saveBtn) {
            saveBtn.onclick = function() {
                document.getElementById('alsendo-template-modal').style.display = 'flex';
                document.getElementById('alsendo-template-name-input').value = '';
            };
        }

        var updateBtn = document.querySelector('.alsendo-update-sender-edit');
        if (updateBtn) {
            updateBtn.onclick = function() {
                AlsendoValidation.clearErrors('#panel-default-sender-address');
                var idx = alsendoSelectedTemplateIdx;
                var data = {
                    company: document.querySelector('.alsendo_sender_company').value,
                    firstname: document.querySelector('.alsendo_sender_firstname').value,
                    lastname: document.querySelector('.alsendo_sender_lastname').value,
                    street: document.querySelector('.alsendo_sender_street').value,
                    building: document.querySelector('.alsendo_sender_building').value,
                    apartment: document.querySelector('.alsendo_sender_apartment') ? document.querySelector('.alsendo_sender_apartment').value : '',
                    block: document.querySelector('.alsendo_sender_block') ? document.querySelector('.alsendo_sender_block').value : '',
                    entrance: document.querySelector('.alsendo_sender_entrance') ? document.querySelector('.alsendo_sender_entrance').value : '',
                    floor: document.querySelector('.alsendo_sender_floor') ? document.querySelector('.alsendo_sender_floor').value : '',
                    flat: document.querySelector('.alsendo_sender_flat') ? document.querySelector('.alsendo_sender_flat').value : '',
                    postal: document.querySelector('.alsendo_sender_postal').value,
                    city: document.querySelector('.alsendo_sender_city').value,
                    contact: document.querySelector('.alsendo_sender_contact').value,
                    phone: document.querySelector('.alsendo_sender_phone').value,
                    email: document.querySelector('.alsendo_sender_email').value,
                    bank: document.querySelector('.alsendo_sender_bank').value,
                    bank_code: (document.querySelector('.alsendo_sender_bank_code') || {}).value || '',
                    additional_bank_account_number: (document.querySelector('.alsendo_sender_additional_bank') || {}).value || '',
                    external_id: (document.querySelector('.alsendo_sender_external_id') || {}).value || '',
                    address_type: document.querySelector('.alsendo_sender_address_type').value,
                    template_name: alsendoSenderList[idx].template_name || '',
                    main: alsendoSenderList[idx].main || false
                };
                alsendoSenderList[idx] = data;
                AlsendoService.save('settings', {
                    alsendo_sender_list: JSON.stringify(alsendoSenderList)
                }, function(resp) {
                    var msgElem = document.getElementById('alsendo-settings-msg');
                    msgElem.innerText = resp.message || (resp.error ? 'Error' : 'Updated!');
                    msgElem.className = resp.error ? 'alert alert-danger' : 'alert alert-success';
                    if (resp.error && resp.errors) {
                        var templateErrors = AlsendoValidation.parseSenderErrors(resp.errors, idx);
                        AlsendoValidation.showErrors(templateErrors, '#panel-default-sender-address');
                    } else {
                        updateSenderDropdown();
                        renderSenderList();
                    }
                });
            };
        }

        var setMainBtn = document.querySelector('.alsendo-set-main-sender');
        if (setMainBtn) {
            setMainBtn.onclick = function() {
                var idx = alsendoSelectedTemplateIdx;
                // Remove main from all templates
                alsendoSenderList.forEach(function(s) { s.main = false; });
                // Set current as main
                alsendoSenderList[idx].main = true;
                AlsendoService.save('settings', {
                    alsendo_sender_list: JSON.stringify(alsendoSenderList)
                }, function(resp) {
                    var msgElem = document.getElementById('alsendo-settings-msg');
                    msgElem.innerText = window.ALSENDO_MSG_SET_DEFAULT || 'Set as default!';
                    msgElem.className = 'alert alert-success';
                    updateSenderDropdown();
                    renderSenderList();
                });
            };
        }
        var removeBtn = document.querySelector('.alsendo-remove-sender');
        if (removeBtn) {
            removeBtn.onclick = function() {
                var idx = alsendoSelectedTemplateIdx;
                if (idx !== 'new' && alsendoSenderList[idx]) {
                    alsendoSenderList.splice(idx, 1);
                    alsendoSelectedTemplateIdx = 'new';
                    AlsendoService.save('settings', {
                        alsendo_sender_list: JSON.stringify(alsendoSenderList)
                    }, function(resp) {
                        updateSenderDropdown();
                        renderSenderList();
                        document.getElementById('alsendo-settings-msg').innerText = resp.message || (resp.error ? 'Error' : 'Removed!');
                    });
                }
            };
        }

        var addressTypeButtons = document.querySelectorAll('.alsendo-config-address-type-btn');
        if (addressTypeButtons.length > 0) {
            addressTypeButtons.forEach(function(btn) {
                btn.onclick = function() {
                    var value = this.getAttribute('data-value');
                    addressTypeButtons.forEach(function(b) {
                        b.classList.remove('active');
                    });
                    this.classList.add('active');
                    var addressTypeInput = document.querySelector('.alsendo_sender_address_type');
                    if (addressTypeInput) {
                        addressTypeInput.value = value;
                    }
                    var companyRow = document.getElementById('alsendo-config-company-row');
                    var companyInput = document.querySelector('.alsendo_sender_company');
                    var companyLabel = companyRow ? companyRow.querySelector('label') : null;
                    if (companyRow) {
                        companyRow.style.display = (value === 'home') ? 'none' : 'block';
                    }
                    if (companyInput) {
                        if (value === 'company') {
                            companyInput.setAttribute('required', 'required');
                            if (companyLabel) companyLabel.innerHTML = '{l s='Company name' mod='alsendo'}*';
                        } else {
                            companyInput.removeAttribute('required');
                            if (companyLabel) companyLabel.innerHTML = '{l s='Company name' mod='alsendo'}';
                        }
                    }
                };
            });
        }
    }
</script>

<div class="panel alsendo-panel-full" id="panel-shipping-settings">
    <h3><span class="alsendo-panel-icon"><i
                    class="material-icons">local_shipping</i></span>{l s='Shipping settings' mod='alsendo'}</h3>
    <div class="form-group" style="margin-bottom:20px">
        <label for="alsendo_shipping_template_select">{l s='Select template' mod='alsendo'}</label>
        <select id="alsendo_shipping_template_select" class="form-control"></select>
    </div>
    <div id="alsendo-shipping-settings-form"></div>
    <div id="alsendo-shipping-template-modal"
         style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:32px 24px;border-radius:8px;max-width:400px;margin:auto;">
            <h4>{l s='Enter template name' mod='alsendo'}</h4>
            <input type="text" id="alsendo-shipping-template-name-input" class="form-control"
                   style="margin-bottom:16px;">
            <button class="btn btn-primary"
                    id="alsendo-shipping-template-modal-save">{l s='Save' mod='alsendo'}</button>
            <button class="btn btn-secondary"
                    id="alsendo-shipping-template-modal-cancel">{l s='Cancel' mod='alsendo'}</button>
        </div>
    </div>
    <div id="alsendo-shipping-settings-msg" style="margin-top: 10px;"></div>
    <script>
        var alsendoShippingSettingsList = [];
        try {
            alsendoShippingSettingsList = JSON.parse('{$alsendo_shipping_settings_list|escape:'javascript':'UTF-8'}');
        } catch (e) { alsendoShippingSettingsList = []; }
        var alsendoSelectedShippingIdx = 'new';

        // B.8: Auto-select default (main) shipping template after page load
        if (alsendoShippingSettingsList && alsendoShippingSettingsList.length > 0) {
            for (var i = 0; i < alsendoShippingSettingsList.length; i++) {
                if (alsendoShippingSettingsList[i].main === true) {
                    alsendoSelectedShippingIdx = i;
                    break;
                }
            }
            if (alsendoSelectedShippingIdx === 'new') alsendoSelectedShippingIdx = 0;
        }

        function updateShippingDropdown() {
            var select = document.getElementById('alsendo_shipping_template_select');
            select.innerHTML = '';
            var newOpt = document.createElement('option');
            newOpt.value = 'new';
            newOpt.textContent = '{l s='New template' mod='alsendo'}';
            select.appendChild(newOpt);

            if (alsendoShippingSettingsList && alsendoShippingSettingsList.length > 0) {
                alsendoShippingSettingsList.forEach(function(tpl, idx) {
                    var name = tpl.alsendo_template_name || '{l s='Unnamed' mod='alsendo'}';
                    if (tpl.main === true) {
                        name += ' - default';
                    }
                    var option = document.createElement('option');
                    option.value = idx;
                    option.textContent = name;
                    select.appendChild(option);
                });
            }

            // Validate selected index
            if (alsendoSelectedShippingIdx !== 'new' &&
                (!alsendoShippingSettingsList || alsendoShippingSettingsList.length === 0 ||
                    alsendoSelectedShippingIdx >= alsendoShippingSettingsList.length)) {
                alsendoSelectedShippingIdx = 'new';
            }

            select.value = alsendoSelectedShippingIdx;
        }

        var ALSENDO_PICKUP_TYPES_BY_REGION = {
            'pl': [
                ['self', "{l s='Deliver to point' mod='alsendo'}"],
                ['courier', "{l s='Courier pickup' mod='alsendo'}"],
                ['no_pickup', "{l s='No pickup' mod='alsendo'}"]
            ],
            'cz': [
                ['on_demand', "{l s='Self-printed label (drop-off)' mod='alsendo'}"],
                ['occasional', "{l s='Label provided by courier' mod='alsendo'}"]
            ],
            'ro': [
                ['courier', "{l s='Courier pickup' mod='alsendo'}"],
                ['self', "{l s='Deliver to point' mod='alsendo'}"]
            ]
        };

        var ALSENDO_CURRENCY_BY_REGION = { 'pl': 'PLN', 'cz': 'CZK', 'ro': 'RON' };

        function getCurrentRegion() {
            var sel = document.getElementById('alsendo_region');
            return sel ? sel.value : 'pl';
        }

        function renderShippingForm() {
            var currentRegion = getCurrentRegion();
            var currency = ALSENDO_CURRENCY_BY_REGION[currentRegion] || 'PLN';
            var settings = null;
            if (alsendoSelectedShippingIdx === 'new') {
                settings = {
                    alsendo_template_name: '',
                    alsendo_package_type: '',
                    alsendo_is_nstd: 0,
                    alsendo_width: '',
                    alsendo_length: '',
                    alsendo_height: '',
                    alsendo_weight: '',
                    alsendo_cod: '0',
                    alsendo_declared_value: '',
                    alsendo_shipment_content: '',
                    alsendo_pickup_type: ''
                };
            } else {
                settings = alsendoShippingSettingsList[alsendoSelectedShippingIdx] || {
                    alsendo_template_name: '',
                    alsendo_package_type: '',
                    alsendo_is_nstd: 0,
                    alsendo_width: '',
                    alsendo_length: '',
                    alsendo_height: '',
                    alsendo_weight: '',
                    alsendo_cod: '0',
                    alsendo_declared_value: '',
                    alsendo_shipment_content: '',
                    alsendo_pickup_type: ''
                };
            }

            setTimeout(function() {
                document.querySelector('.alsendo_package_type').value = settings.alsendo_package_type || '';
                var nstdCb = document.querySelector('.alsendo_is_nstd');
                if (nstdCb) nstdCb.checked = !!(settings.alsendo_is_nstd);
                document.querySelector('.alsendo_width').value = settings.alsendo_width || '';
                document.querySelector('.alsendo_length').value = settings.alsendo_length || '';
                document.querySelector('.alsendo_height').value = settings.alsendo_height || '';
                document.querySelector('.alsendo_weight').value = settings.alsendo_weight || '';

                var codValue = settings.alsendo_cod;
                if (codValue !== undefined && codValue !== null && codValue !== '') {
                    document.querySelector('.alsendo_cod').value = codValue;
                } else {
                    document.querySelector('.alsendo_cod').value = '0';
                }

                document.querySelector('.alsendo_declared_value').value = settings.alsendo_declared_value || '';
                document.querySelector('.alsendo_shipment_content').value = settings.alsendo_shipment_content || '';

                if (settings.alsendo_pickup_type) {
                    var radios = document.querySelectorAll('input[name="alsendo_pickup_type"]');
                    radios.forEach(function(radio) {
                        radio.checked = radio.value === settings.alsendo_pickup_type;
                    });
                }

                var codInput = document.querySelector('.alsendo_cod');
                if (codInput) {
                    codInput.addEventListener('blur', function() {
                        if (this.value === '' || this.value === null) {
                            this.value = '0';
                        }
                    });
                }
            }, 0);

            var html = '';
            html += '<div class="alsendo-shipping-card" data-idx="' + alsendoSelectedShippingIdx + '">';
            html += '<div class="alsendo-shipping-edit-fields">';

            // Package type + Non-standard checkbox
            html += '<div class="row mb-0">';
            html += '<div class="col-md-4">';
            html += '<label class="form-label">{l s='Package type' mod='alsendo'}</label>';
            html += '<select class="form-control alsendo_package_type">';
            if (window.ALSENDO_PACKAGE_TYPES_LIST && window.ALSENDO_PACKAGE_TYPES_LIST.length > 0) {
                window.ALSENDO_PACKAGE_TYPES_LIST.forEach(function(pt) {
                    html += '<option value="' + pt.type + '">' + (pt.desc || pt.type) + '</option>';
                });
            } else {
                if (alsendoRegion === 'cz') {
                    html += '<option value="PACKAGE">PACKAGE</option>';
                } else if (alsendoRegion === 'ro') {
                    html += '<option value="package">package</option>';
                } else {
                    html += '<option value="PACZKA">PACZKA</option>';
                }
            }
            html += '</select>';
            html += '</div>';
            html += '<div class="col-md-4 d-flex align-items-end">';
            html += '<div class="form-check mb-2">';
            html += '<input type="checkbox" class="form-check-input alsendo_is_nstd">';
            html += '<label class="form-check-label">{l s='Non-standard' mod='alsendo'}</label>';
            html += '</div>';
            html += '</div>';
            html += '</div>';

            // Dimensions row (width, length, height, weight)
            html += '<div class="row mb-0">';
            html += '<div class="col">';
            html += '<label class="form-label">{l s='Width' mod='alsendo'}</label>';
            html += '<div class="input-unit"><input type="number" class="form-control alsendo_width" min="0" step="0.01"><span class="input-unit-text">cm</span></div>';
            html += '</div>';
            html += '<div class="col">';
            html += '<label class="form-label">{l s='Length' mod='alsendo'}</label>';
            html += '<div class="input-unit"><input type="number" class="form-control alsendo_length" min="0" step="0.01"><span class="input-unit-text">cm</span></div>';
            html += '</div>';
            html += '<div class="col">';
            html += '<label class="form-label">{l s='Height' mod='alsendo'}</label>';
            html += '<div class="input-unit"><input type="number" class="form-control alsendo_height" min="0" step="0.01"><span class="input-unit-text">cm</span></div>';
            html += '</div>';
            html += '<div class="col">';
            html += '<label class="form-label">{l s='Weight' mod='alsendo'}</label>';
            html += '<div class="input-unit"><input type="number" class="form-control alsendo_weight" min="0" step="0.01"><span class="input-unit-text">kg</span></div>';
            html += '</div>';
            html += '</div>';

            // Finance row (COD, Declared value)
            html += '<div class="row mb-0">';
            html += '<div class="col">';
            html += '<label class="form-label">{l s='Cash on Delivery' mod='alsendo'}</label>';
            html += '<div class="input-unit"><input type="number" class="form-control alsendo_cod" min="0" step="0.01" placeholder="0"><span class="input-unit-text">' + currency + '</span></div>';
            html += '<small class="form-text text-muted">{l s='0 = no COD' mod='alsendo'}</small>';
            html += '</div>';
            html += '<div class="col">';
            html += '<label class="form-label">{l s='Declared value' mod='alsendo'}</label>';
            html += '<div class="input-unit"><input type="number" class="form-control alsendo_declared_value" min="0" step="0.01"><span class="input-unit-text">' + currency + '</span></div>';
            html += '</div>';
            html += '</div>';

            // Content + tags
            html += '<div class="row mb-0">';
            html += '<div class="col-md-8">';
            html += '<label class="form-label">{l s='Shipment content' mod='alsendo'}</label>';
            html += '<input type="text" class="form-control alsendo_shipment_content" id="shipping-content-input">';
            html += '</div>';
            html += '<div class="col-md-4">';
            html += '<label class="form-label">{l s='Insert tag' mod='alsendo'}</label>';
            html += '<select class="form-control" id="shipping-content-tags">';
            html += '<option value="">{l s='Select tag...' mod='alsendo'}</option>';
            {foreach from=$available_tags key=tagKey item=tagLabel}
            html += '<option value="{$tagKey|escape:'html':'UTF-8'}">{$tagLabel|escape:'html':'UTF-8'}</option>';
            {/foreach}
            html += '</select>';
            html += '</div>';
            html += '</div>';

            // Pickup type
            html += '<div class="row mb-0">';
            html += '<div class="col-12">';
            html += '<label class="form-label">{l s='Pickup type' mod='alsendo'}</label>';
            html += '<div class="d-flex gap-3 align-items-center">';
            var pickupTypes = ALSENDO_PICKUP_TYPES_BY_REGION[currentRegion] || ALSENDO_PICKUP_TYPES_BY_REGION['pl'];
            pickupTypes.forEach(function(pt) {
                html += '<label class="form-check"><input type="radio" name="alsendo_pickup_type" value="' + pt[0] + '" class="form-check-input alsendo_pickup_type"> <span class="form-check-label">' + pt[1] + '</span></label>';
            });
            html += '</div>';
            html += '</div>';
            html += '</div>';

            // Action buttons
            html += '<div class="d-flex justify-content-between align-items-center mt-3">';
            html += '<div class="d-flex gap-2">';
            if (alsendoSelectedShippingIdx !== 'new') {
                var isMain = settings.main === true;
                html += '<button class="btn btn-primary alsendo-update-shipping-edit" type="button">{l s='Update' mod='alsendo'}</button>';
                if (!isMain) {
                    html += '<button class="btn btn-warning alsendo-set-main-shipping" type="button">{l s='Set as Default' mod='alsendo'}</button>';
                }
                html += '<button class="btn btn-danger alsendo-remove-shipping" type="button">{l s='Remove' mod='alsendo'}</button>';
            } else {
                html += '<button class="btn btn-success alsendo-save-shipping-edit" type="button">{l s='Save' mod='alsendo'}</button>';
            }
            html += '</div>';
            if (alsendoSelectedShippingIdx !== 'new' && settings.main === true) {
                html += '<span class="text-muted" style="font-size:13px;font-style:italic">{l s='Default template' mod='alsendo'}</span>';
            }
            html += '</div>';
            html += '</div>'; // close .alsendo-shipping-edit-fields
            html += '</div>'; // close .alsendo-shipping-card

            document.getElementById('alsendo-shipping-settings-form').innerHTML = html;

            // Setup tag select event listener (must be done after HTML is inserted)
            var tagSelect = document.getElementById('shipping-content-tags');
            if (tagSelect) {
                tagSelect.addEventListener('change', function() {
                    if (this.value) {
                        var input = document.getElementById('shipping-content-input');
                        if (input) {
                            // Insert tag at cursor position or append
                            var startPos = input.selectionStart;
                            var endPos = input.selectionEnd;
                            var currentValue = input.value;
                            var tagValue = this.value;

                            // If content exists and cursor is at end, add space before tag
                            if (currentValue && startPos === currentValue.length) {
                                tagValue = ' ' + tagValue;
                            }

                            input.value = currentValue.substring(0, startPos) + tagValue + currentValue.substring(endPos, currentValue.length);
                            input.focus();
                        }
                        this.value = ''; // Reset select
                    }
                });
            }

            var saveBtn = document.querySelector('.alsendo-save-shipping-edit');
            if (saveBtn) {
                saveBtn.onclick = function() {
                    document.getElementById('alsendo-shipping-template-modal').style.display = 'flex';
                    document.getElementById('alsendo-shipping-template-name-input').value = '';
                };
            }

            var updateBtn = document.querySelector('.alsendo-update-shipping-edit');
            if (updateBtn) {
                updateBtn.onclick = function() {
                    AlsendoValidation.clearErrors('#panel-shipping-settings');

                    var idx = alsendoSelectedShippingIdx;
                    var codVal = document.querySelector('.alsendo_cod').value;
                    if (codVal === '' || codVal === null) {
                        codVal = '0';
                    }

                    var data = {
                        alsendo_template_name: alsendoShippingSettingsList[idx].alsendo_template_name || '',
                        alsendo_package_type: document.querySelector('.alsendo_package_type').value,
                        alsendo_is_nstd: document.querySelector('.alsendo_is_nstd').checked ? 1 : 0,
                        alsendo_width: document.querySelector('.alsendo_width').value,
                        alsendo_length: document.querySelector('.alsendo_length').value,
                        alsendo_height: document.querySelector('.alsendo_height').value,
                        alsendo_weight: document.querySelector('.alsendo_weight').value,
                        alsendo_cod: codVal,
                        alsendo_declared_value: document.querySelector('.alsendo_declared_value').value,
                        alsendo_shipment_content: document.querySelector('.alsendo_shipment_content').value,
                        alsendo_pickup_type: document.querySelector('input[name="alsendo_pickup_type"]:checked')
                            ? document.querySelector('input[name="alsendo_pickup_type"]:checked').value
                            : '',
                        main: alsendoShippingSettingsList[idx].main || false
                    };
                    alsendoShippingSettingsList[idx] = data;
                    AlsendoService.save('shipping_settings', {
                        alsendo_shipping_settings_list: JSON.stringify(alsendoShippingSettingsList)
                    }, function(resp) {
                        var msgElem = document.getElementById('alsendo-shipping-settings-msg');
                        msgElem.innerText = resp.message || (resp.error ? 'Error' : 'Updated!');
                        msgElem.className = resp.error ? 'alert alert-danger' : 'alert alert-success';

                        if (resp.error && resp.errors) {
                            var templateErrors = AlsendoValidation.parseShippingErrors(resp.errors, idx);
                            AlsendoValidation.showErrors(templateErrors, '#panel-shipping-settings');
                        } else {
                            updateShippingDropdown();
                            renderShippingForm();
                        }
                    });
                };
            }

            var setMainBtn = document.querySelector('.alsendo-set-main-shipping');
            if (setMainBtn) {
                setMainBtn.onclick = function() {
                    var idx = alsendoSelectedShippingIdx;
                    // Remove main from all templates
                    alsendoShippingSettingsList.forEach(function(s) { s.main = false; });
                    // Set current as main
                    alsendoShippingSettingsList[idx].main = true;
                    AlsendoService.save('shipping_settings', {
                        alsendo_shipping_settings_list: JSON.stringify(alsendoShippingSettingsList)
                    }, function(resp) {
                        var msgElem = document.getElementById('alsendo-shipping-settings-msg');
                        msgElem.innerText = window.ALSENDO_MSG_SET_DEFAULT || 'Set as default!';
                        msgElem.className = 'alert alert-success';
                        updateShippingDropdown();
                        renderShippingForm();
                    });
                };
            }

            var removeBtn = document.querySelector('.alsendo-remove-shipping');
            if (removeBtn) {
                removeBtn.onclick = function() {
                    var idx = alsendoSelectedShippingIdx;
                    if (idx !== 'new' && alsendoShippingSettingsList[idx]) {
                        alsendoShippingSettingsList.splice(idx, 1);
                        alsendoSelectedShippingIdx = 'new';
                        AlsendoService.save('shipping_settings', {
                            alsendo_shipping_settings_list: JSON.stringify(alsendoShippingSettingsList)
                        }, function(resp) {
                            updateShippingDropdown();
                            renderShippingForm();
                            document.getElementById('alsendo-shipping-settings-msg').innerText = resp.message || (resp.error ? 'Error' : 'Removed!');
                        });
                    }
                };
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            updateShippingDropdown();
            renderShippingForm();
            document.getElementById('alsendo_shipping_template_select').onchange = function() {
                alsendoSelectedShippingIdx = this.value;
                renderShippingForm();
            };
            var modalSaveBtn = document.getElementById('alsendo-shipping-template-modal-save');
            var modalCancelBtn = document.getElementById('alsendo-shipping-template-modal-cancel');
            if (modalSaveBtn) {
                modalSaveBtn.onclick = function() {
                    var name = document.getElementById('alsendo-shipping-template-name-input').value.trim();
                    if (!name) return;

                    // Add region suffix if not already present
                    var _region = getCurrentRegion();
                    var regionSuffix = ' - ' + _region.toUpperCase();
                    if (name.indexOf(regionSuffix) === -1) {
                        name += regionSuffix;
                    }

                    // Clear previous errors
                    AlsendoValidation.clearErrors('#panel-shipping-settings');

                    var data = {
                        alsendo_template_name: name,
                        alsendo_package_type: document.querySelector('.alsendo_package_type').value,
                        alsendo_is_nstd: document.querySelector('.alsendo_is_nstd').checked ? 1 : 0,
                        alsendo_width: document.querySelector('.alsendo_width').value,
                        alsendo_length: document.querySelector('.alsendo_length').value,
                        alsendo_height: document.querySelector('.alsendo_height').value,
                        alsendo_weight: document.querySelector('.alsendo_weight').value,
                        alsendo_cod: document.querySelector('.alsendo_cod').value,
                        alsendo_declared_value: document.querySelector('.alsendo_declared_value').value,
                        alsendo_shipment_content: document.querySelector('.alsendo_shipment_content')
                            .value,
                        alsendo_pickup_type: document.querySelector(
                            'input[name="alsendo_pickup_type"]:checked') ? document.querySelector(
                            'input[name="alsendo_pickup_type"]:checked').value : '',
                    };
                    alsendoShippingSettingsList.push(data);
                    var newIdx = alsendoShippingSettingsList.length - 1;
                    alsendoSelectedShippingIdx = newIdx;

                    AlsendoService.save('shipping_settings', {
                        alsendo_shipping_settings_list: JSON.stringify(alsendoShippingSettingsList)
                    }, function(resp) {
                        var msgElem = document.getElementById('alsendo-shipping-settings-msg');
                        msgElem.innerText = resp.message || (resp.error ? (window.ALSENDO_MSG_ERROR || 'Error') : (window.ALSENDO_MSG_SAVED || 'Saved!'));
                        msgElem.className = resp.error ? 'alert alert-danger' : 'alert alert-success';

                        if (resp.error && resp.errors) {
                            // If there's an error, remove the template we just added
                            alsendoShippingSettingsList.pop();
                            alsendoSelectedShippingIdx = 'new';

                            // Parse errors for the new template (last index)
                            var templateErrors = AlsendoValidation.parseShippingErrors(resp.errors, newIdx);
                            AlsendoValidation.showErrors(templateErrors, '#panel-shipping-settings');
                        } else {
                            document.getElementById('alsendo-shipping-template-modal').style.display = 'none';
                            updateShippingDropdown();
                            renderShippingForm();
                        }
                    });
                };
            }
            if (modalCancelBtn) {
                modalCancelBtn.onclick = function() {
                    document.getElementById('alsendo-shipping-template-modal').style.display = 'none';
                };
            }

            var codInput = document.querySelector('.alsendo_cod');
            if (codInput) {
                codInput.addEventListener('blur', function() {
                    if (this.value === '' || this.value === null) {
                        this.value = '0';
                    }
                });
            }
        });
    </script>
</div>

{if $alsendo_region != 'cz'}
<div class="panel alsendo-panel-full">
    <h3><span class="alsendo-panel-icon"><i class="material-icons">schedule</i></span>{l s='Default Pickup Hours for Courier Orders' mod='alsendo'}</h3>

    <div id="pickup-hours-error-banner" style="display:none; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 16px;">
        <strong>{l s='Validation Error:' mod='alsendo'}</strong>
        <ul id="pickup-hours-error-list" style="margin: 8px 0 0 20px;"></ul>
    </div>

    <p class="help-block" style="margin-bottom: 20px;">
        {l s='These hours will be automatically used for Quick Send, Bulk Send, and Full Form when pickup type is COURIER.' mod='alsendo'}
    </p>

    <table class="table table-bordered" style="background: white;">
        <thead>
            <tr>
                <th style="width: 30%">{l s='Setting' mod='alsendo'}</th>
                <th style="width: 30%">{l s='Value' mod='alsendo'}</th>
                <th>{l s='Constraints' mod='alsendo'}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>{l s='From (Start Time)' mod='alsendo'}</strong></td>
                <td>
                    <input type="time" class="form-control" id="alsendo_default_pickup_hours_from"
                           value="{$alsendo_default_pickup_hours_from|default:'08:00'|escape:'html':'UTF-8'}"
                           min="08:00" max="17:00">
                </td>
                <td>{l s='Minimum: 08:00' mod='alsendo'}</td>
            </tr>
            <tr>
                <td><strong>{l s='To (End Time)' mod='alsendo'}</strong></td>
                <td>
                    <input type="time" class="form-control" id="alsendo_default_pickup_hours_to"
                           value="{$alsendo_default_pickup_hours_to|default:'17:00'|escape:'html':'UTF-8'}"
                           min="08:00" max="17:00">
                </td>
                <td>{l s='Maximum: 17:00 (due to courier operational hours)' mod='alsendo'}</td>
            </tr>
            <tr>
                <td colspan="3">
                    <div class="alert alert-info" style="margin: 0;">
                        <i class="icon-info-circle"></i>
                        <strong>{l s='Requirements:' mod='alsendo'}</strong>
                        <ul style="margin: 8px 0 0 20px;">
                            <li>{l s='Start time must be at or after 08:00' mod='alsendo'}</li>
                            <li>{l s='End time must be at or before 17:00' mod='alsendo'}</li>
                            <li>{l s='Minimum window between start and end: 2 hours' mod='alsendo'}</li>
                            <li>{l s='End time must be after start time' mod='alsendo'}</li>
                        </ul>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <button class="btn btn-primary" id="alsendo-save-pickup-hours" style="margin-top: 16px;">
        <i class="icon-save"></i> {l s='Save Pickup Hours' mod='alsendo'}
    </button>
    <div id="alsendo-pickup-hours-msg" style="margin-top: 12px;"></div>
</div>
{/if}

{if $alsendo_region != 'cz'}
<div class="panel alsendo-panel-full" id="panel-additional-settings">
    <h3><span class="alsendo-panel-icon"><i
                    class="material-icons">settings</i></span>{l s='Additional settings' mod='alsendo'}</h3>
    <div style="display: flex; gap: 32px; flex-wrap: wrap;">
        <div style="flex:1;min-width:320px;max-width:100%">
            <h4>{l s='Default pickup points' mod='alsendo'}</h4>
            <div id="alsendo-pickup-operators-container">
            {foreach $alsendo_pickup_operators as $op}
                <div class="form-group alsendo-pickup-row" style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                    <label style="min-width:120px;">{$op.label|escape:'html':'UTF-8'}</label>
                    <input type="hidden" id="alsendo_pickup_{$op.key|escape:'html':'UTF-8'}" value="{$op.value|escape:'html':'UTF-8'}">
                    <input type="text" class="form-control" id="alsendo_pickup_{$op.key|escape:'html':'UTF-8'}_display" readonly value="{$op.display|escape:'html':'UTF-8'}">
                    <button class="btn btn-primary btn-sm alsendo-choose-pickup" data-carrier="{$op.key|escape:'html':'UTF-8'}" data-pos-type="{$op.pos_type|default:'POSTING'|escape:'html':'UTF-8'}" type="button">{l s='Select' mod='alsendo'}</button>
                    <button class="btn btn-danger btn-sm alsendo-clear-pickup" data-carrier="{$op.key|escape:'html':'UTF-8'}" type="button">{l s='Remove' mod='alsendo'}</button>
                </div>
            {/foreach}
            </div>

            <button class="btn btn-primary" id="alsendo-save-additional-settings" style="margin-top: 16px;">{l s='Save Additional Settings' mod='alsendo'}</button>
        </div>
        <div style="flex:1;min-width:320px;max-width:100%">
            <h4>{l s='Declared Value' mod='alsendo'}</h4>
            <div class="form-group">
                <label for="alsendo_auto_declared_value" style="font-weight: normal;">
                    <input type="checkbox" id="alsendo_auto_declared_value"
                           name="alsendo_auto_declared_value" value="1"
                           {if $alsendo_auto_declared_value}checked{/if}>
                    {l s='Auto-fill declared value with order total' mod='alsendo'}
                </label>
                <p class="help-block">
                    {l s='When enabled: declared value = order total (products + shipping). If package template has a higher declared value, the template value is used.' mod='alsendo'}<br>
                    {l s='When disabled: declared value = 0 (unless set by package template).' mod='alsendo'}
                </p>
            </div>

            <h4>{l s='Pickup Date' mod='alsendo'}</h4>
            <div class="form-group">
                <label for="alsendo_same_day_pickup" style="font-weight: normal;">
                    <input type="checkbox" id="alsendo_same_day_pickup"
                           name="alsendo_same_day_pickup" value="1"
                           {if $alsendo_same_day_pickup}checked{/if}>
                    {l s='Allow same-day pickup (send today)' mod='alsendo'}
                </label>
                <p class="help-block">
                    {l s='When enabled: pickup date defaults to today. If the carrier rejects same-day pickup, it will automatically retry with tomorrow.' mod='alsendo'}<br>
                    {l s='When disabled: pickup date defaults to tomorrow (next business day).' mod='alsendo'}
                </p>
            </div>
        </div>
    </div>
    <div id="alsendo-additional-settings-msg"></div>
    {literal}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Delegated click handler for pickup buttons
                document.addEventListener('click', function(e) {
                    if (e.target.classList.contains('alsendo-choose-pickup')) {
                        var senderPostal = document.querySelector('.alsendo_sender_postal');
                        var senderCity = document.querySelector('.alsendo_sender_city');
                        var initialAddr = (senderPostal && senderPostal.value) ? senderPostal.value : ((senderCity && senderCity.value) ? senderCity.value : null);
                        openPointMap(e.target.dataset.carrier, [e.target.dataset.carrier], e.target.dataset.posType || 'POSTING', null, initialAddr);
                    }
                    if (e.target.classList.contains('alsendo-clear-pickup')) {
                        var key = e.target.dataset.carrier;
                        var hidden = document.getElementById('alsendo_pickup_' + key);
                        var display = document.getElementById('alsendo_pickup_' + key + '_display');
                        if (hidden) hidden.value = '';
                        if (display) display.value = '';
                    }
                });

                // Dynamic region change — reload pickup operators
                var regionSelect = document.getElementById('alsendo_region');
                if (regionSelect) {
                    regionSelect.addEventListener('change', function() {
                        var newRegion = this.value;
                        fetch(alsendoAjaxUrl, {
                            method: 'POST',
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                            body: 'ajax=1&action=get_pickup_operators_for_region&region=' + encodeURIComponent(newRegion)
                        })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.operators_html) {
                                document.getElementById('alsendo-pickup-operators-container').innerHTML = data.operators_html;
                            }
                            if (data.map_config) {
                                window.ALSENDO_MAP_CONFIG = data.map_config;
                            }
                            // Refresh shipping form (pickup type options + currency) for the new region
                            if (typeof renderShippingForm === 'function') {
                                renderShippingForm();
                            }
                        })
                        .catch(function(err) {
                            console.error('[Alsendo] Failed to load operators for region:', err);
                        });
                    });
                }

                document.getElementById('alsendo-save-pickup-hours').onclick = function() {
                    var errorBanner = document.getElementById('pickup-hours-error-banner');
                    var errorList = document.getElementById('pickup-hours-error-list');
                    var msgElem = document.getElementById('alsendo-pickup-hours-msg');

                    errorBanner.style.display = 'none';
                    errorList.innerHTML = '';
                    msgElem.innerText = '';
                    msgElem.className = '';

                    document.querySelectorAll('#alsendo_default_pickup_hours_from, #alsendo_default_pickup_hours_to').forEach(function(el) {
                        el.style.borderColor = '';
                    });

                    var sameDayEl = document.getElementById('alsendo_same_day_pickup');
                    var data = {
                        default_pickup_hours_from: document.getElementById('alsendo_default_pickup_hours_from').value,
                        default_pickup_hours_to: document.getElementById('alsendo_default_pickup_hours_to').value,
                        same_day_pickup: sameDayEl ? (sameDayEl.checked ? 1 : 0) : undefined
                    };

                    AlsendoService.save('additional_settings', data, function(resp) {
                        if (resp.error && resp.errors) {
                            errorBanner.style.display = 'block';

                            Object.keys(resp.errors).forEach(function(field) {
                                var li = document.createElement('li');
                                li.textContent = resp.errors[field];
                                errorList.appendChild(li);

                                var fieldId = field.replace('default_', 'alsendo_default_');
                                var fieldElement = document.getElementById(fieldId);
                                if (fieldElement) {
                                    fieldElement.style.borderColor = '#dc3545';
                                }
                            });

                            errorBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        } else {
                            msgElem.innerText = resp.message || 'Pickup hours saved successfully!';
                            msgElem.className = 'alert alert-success';
                            msgElem.style.color = '#28a745';

                            setTimeout(function() {
                                msgElem.innerText = '';
                                msgElem.className = '';
                            }, 3000);
                        }
                    });
                };

                document.getElementById('alsendo-save-additional-settings').onclick = function() {
                    AlsendoValidation.clearErrors('#panel-additional-settings');

                    // Dynamically collect all pickup operator values
                    var pickupData = { pickup_operators: '1' };
                    var rows = document.querySelectorAll('#alsendo-pickup-operators-container .alsendo-pickup-row');
                    rows.forEach(function(row) {
                        var btn = row.querySelector('.alsendo-choose-pickup');
                        if (!btn) return;
                        var carrier = btn.dataset.carrier;
                        var hidden = document.getElementById('alsendo_pickup_' + carrier);
                        var display = document.getElementById('alsendo_pickup_' + carrier + '_display');
                        pickupData['pickup_' + carrier] = hidden ? hidden.value : '';
                        pickupData['pickup_' + carrier + '_display'] = display ? display.value : '';
                    });

                    pickupData['auto_declared_value'] = document.getElementById('alsendo_auto_declared_value').checked ? 1 : 0;
                    var sameDayEl2 = document.getElementById('alsendo_same_day_pickup');
                    if (sameDayEl2) {
                        pickupData['same_day_pickup'] = sameDayEl2.checked ? 1 : 0;
                    }

                    AlsendoService.save('additional_settings', pickupData, function(resp) {
                        var msgElem = document.getElementById('alsendo-additional-settings-msg');
                        msgElem.innerText = resp.message || (resp.error ? (window.ALSENDO_MSG_ERROR || 'Error') : (window.ALSENDO_MSG_SAVED || 'Saved!'));
                        msgElem.className = resp.error ? 'alert alert-danger' : 'alert alert-success';

                        if (resp.error && resp.errors) {
                            AlsendoValidation.showErrors(resp.errors, '#panel-additional-settings');
                        }
                    });
                };
            });
        </script>
    {/literal}
</div>
{/if}

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
{literal}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('alsendo-save-config').onclick = function(e) {
                e.preventDefault();
                var msgElem = document.getElementById('alsendo-config-msg');
                msgElem.innerText = 'Saving...';
                msgElem.className = 'alert alert-info';

                // Clear previous errors
                AlsendoValidation.clearErrors('#alsendo-panel-config');

                const region = document.getElementById('alsendo_region').value;
                const appId = document.getElementById('alsendo_app_id').value;
                const apiKey = document.getElementById('alsendo_api_key').value;
                const token = document.getElementById('alsendo_token').value;
                const secret = document.getElementById('alsendo_secret').value;
                const testMode = document.getElementById('alsendo_test_mode').checked ? 1 : 0;
                const roClientId = document.getElementById('alsendo_ro_client_id').value;
                const roClientSecret = document.getElementById('alsendo_ro_client_secret').value;

                AlsendoService.save('config', {
                        region: region,
                        alsendo_app_id: appId,
                        alsendo_api_key: apiKey,
                        alsendo_token: token,
                        alsendo_secret: secret,
                        alsendo_test_mode: testMode,
                        alsendo_ro_client_id: roClientId,
                        alsendo_ro_client_secret: roClientSecret
                    },
                    function(resp) {
                        var msgElem = document.getElementById('alsendo-config-msg');
                        msgElem.innerText = resp.message || (resp.error ? (window.ALSENDO_MSG_ERROR || 'Error') : (window.ALSENDO_MSG_SAVED || 'Saved!'));
                        msgElem.className = resp.error ? 'alert alert-danger' : 'alert alert-success';

                        // Show field-specific errors
                        if (resp.error && resp.errors) {
                            AlsendoValidation.showErrors(resp.errors, '#alsendo-panel-config');
                        }
                    }
                );
            };

            // Ecolet OAuth2 Authorize button
            var ecoletAuthorizeBtn = document.getElementById('ecolet-authorize-btn');
            if (ecoletAuthorizeBtn) {
                ecoletAuthorizeBtn.addEventListener('click', function() {
                    fetch(alsendoAjaxUrl, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'ajax=1&action=ecolet_authorize_url'
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(json) {
                        if (json.error) {
                            var msgElem = document.getElementById('alsendo-config-msg');
                            msgElem.innerText = json.message || 'Error';
                            msgElem.className = 'alert alert-danger';
                        } else if (json.url) {
                            window.open(json.url, '_blank');
                        }
                    })
                    .catch(function() {
                        var msgElem = document.getElementById('alsendo-config-msg');
                        msgElem.innerText = window.ALSENDO_MSG_ERROR || 'Error';
                        msgElem.className = 'alert alert-danger';
                    });
                });
            }
        });
    </script>
{/literal}
<link rel="stylesheet" href="{$alsendo_map_css_url|escape:'html':'UTF-8'}" media="screen">
<script type="text/javascript" src="{$alsendo_map_js_url|escape:'html':'UTF-8'}"></script>
<div id="alsendo-config-map-data" style="display:none;" data-map-config="{$alsendo_map_config|escape:'html':'UTF-8'}"></div>
<script>
    (function () {
        var cfgEl = document.getElementById('alsendo-config-map-data');
        try {
            window.ALSENDO_MAP_CONFIG = cfgEl ? JSON.parse(cfgEl.dataset.mapConfig || '{}') : {};
        } catch (e) {
            window.ALSENDO_MAP_CONFIG = {};
        }
    })();
    window.ALSENDO_MAP_TRANSLATIONS = {ldelim}
        loading: "{l s='Loading map...' mod='alsendo' js=1}",
        failed: "{l s='Map widget failed to load' mod='alsendo' js=1}",
        close: "{l s='Close' mod='alsendo' js=1}",
        retry: "{l s='Try again' mod='alsendo' js=1}"
    {rdelim};
</script>
<script src="{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}alsendo/views/js/map-widget.js?v={$smarty.now|escape:'html':'UTF-8'}"></script>