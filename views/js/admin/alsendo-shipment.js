/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('alsendo-order-form');
    if (!form) {
        return;
    }

    const token = form.getAttribute('data-token') || document.querySelector("input[name='token']").value;
    const ajaxUrl = "index.php?controller=AdminAlsendoOrder&ajax=1&token=" + token + "&ajax_action=";

    const quoteBtnContainer = document.getElementById('get-a-quote-container');
    const quoteBtn = document.getElementById('get-quote-button');
    const quoteContainer = document.getElementById('courier-services-container');
    const quoteInner = document.getElementById('courier-services-container-inner');
    const selectedServiceInput = document.getElementById('shipment-selected-service');

    const tagSelect = document.getElementById('package-content-tags');
    const contentInput = document.getElementById('package-content-input');

    if (!quoteBtn) {
        return;
    }

    var addressBookSelect = document.getElementById('sender-address-book-select');
    if (addressBookSelect) {
        function setSenderFieldsReadonly(readonly) {
            var senderFields = [
                'sender_company_name', 'sender_full_name', 'sender_street',
                'sender_building_number', 'sender_apartment_number',
                'sender_postal_code', 'sender_city', 'sender_country',
                'sender_contact_person', 'sender_phone_number', 'sender_email',
                'sender_address_type'
            ];
            senderFields.forEach(function(name) {
                var el = form.querySelector('[name="' + name + '"]');
                if (el) {
                    if (el.tagName === 'SELECT') {
                        el.disabled = readonly;
                    } else {
                        el.readOnly = readonly;
                    }
                    el.style.backgroundColor = readonly ? '#e9ecef' : '';
                }
            });
            document.querySelectorAll('.sender-type-btn').forEach(function(btn) {
                if (readonly) {
                    btn.setAttribute('disabled', 'disabled');
                } else {
                    btn.removeAttribute('disabled');
                }
            });
        }

        function fillSenderFromAddressBook(addr) {
            if (!addr) return;
            var companyInput = form.querySelector('[name="sender_company_name"]');
            if (companyInput) companyInput.value = addr.company || '';
            var fullNameInput = form.querySelector('[name="sender_full_name"]');
            if (fullNameInput) fullNameInput.value = ((addr.firstname || '') + ' ' + (addr.surname || '')).trim();
            var streetInput = form.querySelector('[name="sender_street"]');
            var buildingInput = form.querySelector('[name="sender_building_number"]');
            if (streetInput && buildingInput && addr.street) {
                var match = addr.street.match(/^(.+)\s+(\d+\w{0,5}(?:\s*\/\s*\S+)?)\s*$/);
                if (match) {
                    streetInput.value = match[1].trim();
                    buildingInput.value = match[2].trim();
                } else {
                    streetInput.value = addr.street;
                    buildingInput.value = '';
                }
            } else {
                if (streetInput) streetInput.value = addr.street || '';
            }
            var postalInput = form.querySelector('[name="sender_postal_code"]');
            if (postalInput) postalInput.value = addr.zip || '';
            var cityInput = form.querySelector('[name="sender_city"]');
            if (cityInput) cityInput.value = addr.city || '';
            var countrySelect = form.querySelector('[name="sender_country"]');
            if (countrySelect && addr.country) countrySelect.value = addr.country;
            var phoneInput = form.querySelector('[name="sender_phone_number"]');
            if (phoneInput) phoneInput.value = addr.phone || '';
            var emailInput = form.querySelector('[name="sender_email"]');
            if (emailInput) emailInput.value = addr.email || '';
            var contactInput = form.querySelector('[name="sender_contact_person"]');
            if (contactInput) contactInput.value = ((addr.firstname || '') + ' ' + (addr.surname || '')).trim();
            var externalIdInput = form.querySelector('[name="sender_external_id"]');
            if (externalIdInput) externalIdInput.value = addr.id || '';

            var companyRow = document.getElementById('sender-company-row');
            if (companyRow) {
                companyRow.style.display = addr.company ? 'block' : 'none';
            }
        }

        addressBookSelect.addEventListener('change', function() {
            var selected = this.options[this.selectedIndex];
            if (!selected || !selected.value) {
                setSenderFieldsReadonly(false);
                var externalIdInput = form.querySelector('[name="sender_external_id"]');
                if (externalIdInput) externalIdInput.value = '';
                return;
            }
            try {
                var addr = JSON.parse(selected.getAttribute('data-address'));
                fillSenderFromAddressBook(addr);
                setSenderFieldsReadonly(true);
            } catch(e) {
            }
        });

        if (addressBookSelect.value) {
            addressBookSelect.dispatchEvent(new Event('change'));
        }

        var senderTemplateSelectEarly = document.getElementById('sender-template-select');
        if (senderTemplateSelectEarly) {
            senderTemplateSelectEarly.addEventListener('change', function() {
                addressBookSelect.value = '';
                setSenderFieldsReadonly(false);
            });
        }
    }

    var shipViaCheckbox = document.getElementById('shipping-via-pickup-point');
    if (shipViaCheckbox) {
        shipViaCheckbox.addEventListener('change', function() {
            var container = document.getElementById('shipment-pickup-point-container');
            if (container) {
                if (this.checked) {
                    container.style.display = 'block';
                } else {
                    var pickupTypeSelect = document.getElementById('selected_pickup_type');
                    container.style.display = (pickupTypeSelect && pickupTypeSelect.value === 'SELF' && !window.ALSENDO_CARRIER_SKIP_MERCHANT_PICKUP) ? 'block' : 'none';
                }
            }
        });
    }

    var pickupReqCheckbox = document.getElementById('pickup-request');
    if (shipViaCheckbox && pickupReqCheckbox) {
        function updateOrderFormCheckboxExclusivity() {
            if (shipViaCheckbox.checked) {
                pickupReqCheckbox.checked = false;
                pickupReqCheckbox.disabled = true;
            } else if (pickupReqCheckbox.checked) {
                shipViaCheckbox.checked = false;
                shipViaCheckbox.disabled = true;
            } else {
                shipViaCheckbox.disabled = false;
                pickupReqCheckbox.disabled = false;
            }
        }
        shipViaCheckbox.addEventListener('change', updateOrderFormCheckboxExclusivity);
        pickupReqCheckbox.addEventListener('change', updateOrderFormCheckboxExclusivity);
        updateOrderFormCheckboxExclusivity();
    }

    function updateCzCheckboxes(serviceId) {
        if (typeof ALSENDO_SERVICES_CAPABILITIES === 'undefined') return;

        var shipViaContainer = document.getElementById('ship-via-pickup-point-container');
        var pickupReqContainer = document.getElementById('pickup-request-container');
        var pickupDateContainer = document.getElementById('shipment-preferred-pickup-date-container');
        var pickupHoursContainer = document.getElementById('shipment-preferred-pickup-hours-container');
        var pickupTypeSelect = document.getElementById('selected_pickup_type');

        if (!shipViaContainer && !pickupReqContainer) return;

        var svc = null;
        for (var i = 0; i < ALSENDO_SERVICES_CAPABILITIES.length; i++) {
            if (ALSENDO_SERVICES_CAPABILITIES[i].service_id === serviceId) {
                svc = ALSENDO_SERVICES_CAPABILITIES[i];
                break;
            }
        }

        if (pickupDateContainer) {
            pickupDateContainer.style.display = 'none';
            var dateEl = pickupDateContainer.querySelector('input[name="shipment_preferred_pickup_date"]');
            if (dateEl) dateEl.disabled = true;
        }
        if (pickupHoursContainer) {
            pickupHoursContainer.style.display = 'none';
            pickupHoursContainer.querySelectorAll('input').forEach(function(el) { el.disabled = true; });
        }

        var senderPickupContainer = document.getElementById('shipment-pickup-point-container');
        var shipViaCheckboxEl = document.getElementById('shipping-via-pickup-point');

        if (!svc) {
            if (shipViaContainer) shipViaContainer.style.display = 'none';
            if (pickupReqContainer) pickupReqContainer.style.display = 'none';
            if (pickupTypeSelect) pickupTypeSelect.closest('.form-group').style.display = 'none';
            if (senderPickupContainer) senderPickupContainer.style.display = 'none';
            if (shipViaCheckboxEl) shipViaCheckboxEl.checked = false;
            return;
        }

        if (pickupTypeSelect) pickupTypeSelect.closest('.form-group').style.display = '';

        var showShipVia = svc.point_to_point || svc.point_to_door;
        if (shipViaContainer) {
            shipViaContainer.style.display = showShipVia ? 'block' : 'none';
        }
        if (!showShipVia) {
            if (shipViaCheckboxEl) shipViaCheckboxEl.checked = false;
            if (senderPickupContainer) {
                var showByPickupType = pickupTypeSelect && pickupTypeSelect.value === 'SELF' && !window.ALSENDO_CARRIER_SKIP_MERCHANT_PICKUP;
                senderPickupContainer.style.display = showByPickupType ? 'block' : 'none';
            }
        } else {
            if (senderPickupContainer && shipViaCheckboxEl) {
                var showByPickupType = pickupTypeSelect && pickupTypeSelect.value === 'SELF' && !window.ALSENDO_CARRIER_SKIP_MERCHANT_PICKUP;
                senderPickupContainer.style.display = (shipViaCheckboxEl.checked || showByPickupType) ? 'block' : 'none';
            }
        }

        if (pickupReqContainer) {
            var showPickupReq = svc.door_to_point || svc.door_to_door;
            pickupReqContainer.style.display = showPickupReq ? 'block' : 'none';
        }
    }

    function clearValidation() {
        form.querySelectorAll('.input-validation-error').forEach(el => {
            el.classList.remove('input-validation-error');
        });
        form.querySelectorAll('small.validation-error-message').forEach(el => {
            el.remove();
        });
    }

    function renderQuotes(data, warning) {
        quoteInner.innerHTML = '';
        quoteInner.className = 'alsendo-quote-grid';

        if (data && !Array.isArray(data) && typeof data === 'object') {
            data = Object.values(data);
        }

        if (!Array.isArray(data) || !data.length) {
            quoteContainer.classList.remove('d-none');
            quoteContainer.style.display = 'block';
            if (warning) {
                quoteInner.innerHTML = '<div class="alert alert-warning" style="margin:0">' + warning + '</div>';
            } else {
                quoteInner.innerHTML = '<p>' + (window.ALSENDO_MSG_NO_SERVICES || 'No services available for this configuration.') + '</p>';
            }
            return;
        }

        data.forEach(function (item) {
            if (item.no_price) return;

            const sid = String(item.service_id || item.external_id || '');
            const card = document.createElement('div');
            card.className = 'alsendo-quote-card';
            card.setAttribute('data-service-id', sid);
            card.setAttribute('data-courier-name', item.courier_name || item.service_name || '');

            if (item.logo_url) {
                const logo = document.createElement('img');
                logo.className = 'alsendo-quote-logo';
                logo.src = item.logo_url;
                logo.alt = item.service_name || 'Service';
                card.appendChild(logo);
            }

            const name = document.createElement('div');
            name.className = 'alsendo-quote-name';
            name.textContent = item.service_name || 'Service';

            const price = document.createElement('div');
            price.className = 'alsendo-quote-price';
            if (item.no_price) {
                price.innerHTML = '<small style="color:#999">price N/A</small>';
            } else {
                price.innerHTML = '<strong>' + (item.price_gross_display || '') + '</strong>'
                    + (item.price_net_display ? '<br><small>netto: ' + item.price_net_display + '</small>' : '');
            }

            card.appendChild(name);
            card.appendChild(price);

            if (item.is_mapped) {
                card.classList.add('alsendo-quote-mapped');
                var badge = document.createElement('span');
                badge.className = 'alsendo-quote-badge';
                badge.textContent = 'default';
                card.appendChild(badge);
            }

            card.addEventListener('click', function() {
                document.querySelectorAll('.alsendo-quote-card').forEach(c => {
                    c.classList.remove('selected');
                });
                card.classList.add('selected');
                selectedServiceInput.value = sid;
                updateCzCheckboxes(sid);
            });

            quoteInner.appendChild(card);
        });

        quoteContainer.classList.remove('d-none');
        quoteContainer.style.display = 'block';

        var mappedCard = quoteInner.querySelector('.alsendo-quote-card.alsendo-quote-mapped');
        if (mappedCard) {
            mappedCard.classList.add('selected');
            selectedServiceInput.value = mappedCard.getAttribute('data-service-id');
        } else if (window.ALSENDO_PRESELECTED_SERVICE_ID) {
            var match = quoteInner.querySelector(
                '.alsendo-quote-card[data-service-id="' +
                String(window.ALSENDO_PRESELECTED_SERVICE_ID) +
                '"]'
            );
            if (match) {
                match.classList.add('selected');
                selectedServiceInput.value = String(window.ALSENDO_PRESELECTED_SERVICE_ID);
            }
        } else {
            var first = quoteInner.querySelector('.alsendo-quote-card');
            if (first) {
                first.classList.add('selected');
                selectedServiceInput.value = first.getAttribute('data-service-id');
            }
        }

        if (selectedServiceInput.value) {
            updateCzCheckboxes(selectedServiceInput.value);
        }
    }

    if (window.ALSENDO_AUTOSELECT_COURIER && window.ALSENDO_PRESELECTED_SERVICE_ID !== 0) {
        selectedServiceInput.value = String(window.ALSENDO_PRESELECTED_SERVICE_ID);
        quoteContainer.style.display = 'none';
        quoteBtnContainer.style.display = 'block';
        quoteBtn.style.display = 'block';
    } else {
        quoteBtnContainer.style.display = 'block';
        quoteBtn.style.display = 'block';
        quoteContainer.style.display = 'none';
    }

    quoteBtn.addEventListener('click', function(e) {
        e.preventDefault();

        clearValidation();

        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        fetch(ajaxUrl + 'getQuote', {
            method: 'POST',
            body: params
        })
            .then(response => {
                return response.json();
            })
            .then(res => {
                if (res.success && res.data) {
                    window.ALSENDO_PRESELECTED_SERVICE_ID = null;
                    renderQuotes(res.data, res.warning || null);
                } else if (res.success && res.warning) {
                    renderQuotes([], res.warning);
                } else if (res.errors) {
                    var errorMessages = [];
                    Object.entries(res.errors).forEach(function ([k, v]) {
                        const input = form.querySelector('[name="' + k + '"]');
                        if (input) {
                            input.classList.add('input-validation-error');
                            const errorMsg = document.createElement('small');
                            errorMsg.className = 'validation-error-message';
                            errorMsg.textContent = v;
                            input.parentNode.insertBefore(errorMsg, input.nextSibling);
                        }
                        errorMessages.push(v);
                    });
                    showErrorModal(errorMessages.join('\n'));
                } else {
                    showErrorModal(res.error || window.ALSENDO_MSG_QUOTE_ERROR || 'Error fetching quote');
                }
            })
            .catch(error => {
                showErrorModal((window.ALSENDO_MSG_QUOTE_FAILED || 'Quote request failed:') + ' ' + error);
            });
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        clearValidation();

        const pickupTypeSelect = document.getElementById('selected_pickup_type');
        if (pickupTypeSelect && pickupTypeSelect.value === 'COURIER') {
            const dateInput = document.querySelector('input[name="shipment_preferred_pickup_date"]');
            const hoursToInput = document.querySelector('input[name="shipment_preferred_pickup_hours_to"]');

            if (dateInput && dateInput.value && hoursToInput && hoursToInput.value) {
                const pickupDate = dateInput.value;
                const pickupTimeTo = hoursToInput.value;
                const today = new Date().toISOString().split('T')[0];
                const currentTime = new Date().toTimeString().slice(0, 5);

                if (pickupDate === today && pickupTimeTo <= currentTime) {
                    showErrorModal(window.ALSENDO_MSG_PICKUP_TIME_PASSED || 'Pickup end time has already passed. Please select a future time or choose a later date.');
                    const errorMsg = document.createElement('small');
                    errorMsg.className = 'validation-error-message';
                    errorMsg.style.color = '#dc3545';
                    errorMsg.textContent = window.ALSENDO_MSG_PICKUP_TIME_PASSED_SHORT || 'Pickup end time has already passed';
                    hoursToInput.style.borderColor = '#dc3545';
                    if (!hoursToInput.nextElementSibling || !hoursToInput.nextElementSibling.classList.contains('validation-error-message')) {
                        hoursToInput.parentNode.insertBefore(errorMsg, hoursToInput.nextSibling);
                    }
                    return;
                }

                if (pickupDate < today) {
                    showErrorModal(window.ALSENDO_MSG_PICKUP_DATE_PAST || 'Pickup date cannot be in the past. Please select today or a future date.');
                    const errorMsg = document.createElement('small');
                    errorMsg.className = 'validation-error-message';
                    errorMsg.style.color = '#dc3545';
                    errorMsg.textContent = window.ALSENDO_MSG_DATE_PAST_SHORT || 'Date cannot be in the past';
                    dateInput.style.borderColor = '#dc3545';
                    if (!dateInput.nextElementSibling || !dateInput.nextElementSibling.classList.contains('validation-error-message')) {
                        dateInput.parentNode.insertBefore(errorMsg, dateInput.nextSibling);
                    }
                    return;
                }
            }
        }

        const submitBtn = document.getElementById('order-shipment-btn');
        submitBtn.disabled = true;

        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        fetch(ajaxUrl + 'validatePreOrderShipment', {
            method: 'POST',
            body: params
        })
            .then(response => response.json())
            .then(val => {
                if (val.success) {
                    const submitParams = new URLSearchParams(formData);
                    return fetch(ajaxUrl + 'submitOrderShipment', {
                        method: 'POST',
                        body: submitParams
                    }).then(response => response.json());
                } else {
                    submitBtn.disabled = false;
                    if (val.errors) {
                        var errorMessages = [];
                        Object.entries(val.errors).forEach(function ([k, v]) {
                            const input = form.querySelector('[name="' + k + '"]');
                            if (input) {
                                input.classList.add('input-validation-error');
                                const errorMsg = document.createElement('small');
                                errorMsg.className = 'validation-error-message';
                                errorMsg.textContent = v;
                                input.parentNode.insertBefore(errorMsg, input.nextSibling);
                            }
                            errorMessages.push(v);
                        });
                        showErrorModal(errorMessages.join('\n'));
                    }
                    throw new Error('Validation failed');
                }
            })
            .then(r => {
                submitBtn.disabled = false;
                if (r.success) {
                    const orderViewUrlInput = document.getElementById('order-view-url');
                    let redirectUrl = orderViewUrlInput ? orderViewUrlInput.value : window.ORDER_VIEW_URL;

                    if (!redirectUrl || redirectUrl === '') {
                        const orderId = form.querySelector('[name="order_id"]').value;
                        const referer = document.referrer;

                        if (referer && referer.includes('/index.php/sell/orders/')) {
                            const refererUrl = new URL(referer);
                            const token = refererUrl.searchParams.get('_token');
                            if (token) {
                                const baseUrl = referer.split('/index.php/')[0];
                                redirectUrl = baseUrl + '/index.php/sell/orders/' + orderId + '/view?_token=' + token;
                            }
                        }
                    }
                    showSuccessModal(window.ALSENDO_MSG_SHIPMENT_CREATED || 'Shipment has been successfully created. Redirecting...');
                    setTimeout(function() {
                        window.location.href = redirectUrl;
                    }, 900);
                } else {
                    let errorMsg = 'Shipment submission failed:\n\n';

                    if (r.error) {
                        errorMsg += r.error + '\n';
                    }

                    if (r.message) {
                        errorMsg += r.message + '\n';
                    }

                    if (r.errors && typeof r.errors === 'object') {
                        errorMsg += '\n' + (window.ALSENDO_MSG_DETAILED_ERRORS || 'Detailed errors:') + '\n';
                        Object.entries(r.errors).forEach(function([field, message]) {
                            errorMsg += '- ' + field + ': ' + message + '\n';
                        });
                    }

                    if (r.data && typeof r.data === 'object' && Object.keys(r.data).length > 0) {
                    }

                    showErrorModal(errorMsg);
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                showErrorModal((window.ALSENDO_MSG_REQUEST_FAILED || 'Request failed:') + ' ' + error);
            });
    });

    const saveBtn = document.getElementById('save-order-details-btn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="icon-spinner icon-spin"></i> Saving...';

            const formData = new FormData(form);
            const params = new URLSearchParams(formData);

            fetch(ajaxUrl + 'saveOrderDetails', {
                method: 'POST',
                body: params
            })
                .then(response => {
                    return response.json();
                })
                .then(r => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = 'Save';
                    if (r.success) {
                        const orderIdInput = form.querySelector('[name="order_id"]');
                        if (orderIdInput && orderIdInput.value) {
                            localStorage.setItem('alsendo_order_updated_' + orderIdInput.value, Date.now().toString());
                        }
                        showSuccessModal(window.ALSENDO_MSG_SAVED || 'Order details saved successfully.');
                    } else {
                        showErrorModal((window.ALSENDO_MSG_SAVE_ERROR || 'Error saving:') + ' ' + (r.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = 'Save';
                    showErrorModal((window.ALSENDO_MSG_SAVE_ERROR || 'Error saving:') + ' ' + error);
                });
        });
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('quick-send-button') || e.target.closest('.quick-send-button')) {
            const btn = e.target.classList.contains('quick-send-button') ? e.target : e.target.closest('.quick-send-button');
            const orderId = btn.dataset.orderId;

            if (!confirm(window.ALSENDO_MSG_QUICK_SEND_CONFIRM || 'Send this order with default settings?')) return;

            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + (window.ALSENDO_MSG_SENDING || 'Sending...');

            const ajaxUrlBase = "index.php?controller=AdminAlsendoOrder&ajax=1&token=" + token + "&ajax_action=";

            fetch(ajaxUrlBase + 'quickSendOrderShipment', {
                method: 'POST',
                body: new URLSearchParams({ order_id: orderId })
            })
                .then(r => r.json())
                .then(r => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-bolt"></i> Quick Send';

                    if (r.success) {
                        showSuccessModal(window.ALSENDO_MSG_SENT_OK || 'Shipment sent successfully!');
                        setTimeout(function() { location.reload(); }, 900);
                    } else {
                        let errorMsg = (window.ALSENDO_MSG_QUICK_SEND_FAILED || 'Quick send failed:') + '\n\n';
                        if (r.error) errorMsg += r.error + '\n';
                        if (r.message) errorMsg += r.message + '\n';
                        if (r.errors && typeof r.errors === 'object') {
                            errorMsg += '\n' + (window.ALSENDO_MSG_DETAILED_ERRORS || 'Detailed errors:') + '\n';
                            Object.entries(r.errors).forEach(function([field, message]) {
                                errorMsg += '- ' + field + ': ' + message + '\n';
                            });
                        }
                        showErrorModal(errorMsg);
                    }
                })
                .catch(error => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-bolt"></i> Quick Send';
                    showErrorModal((window.ALSENDO_MSG_REQUEST_FAILED || 'Request failed:') + ' ' + error);
                });
        }

        if (e.target.classList.contains('cancel-shipment-button') || e.target.closest('.cancel-shipment-button')) {
            const cancelBtn = e.target.classList.contains('cancel-shipment-button') ? e.target : e.target.closest('.cancel-shipment-button');
            if (!confirm(window.ALSENDO_MSG_CANCEL_CONFIRM || 'Cancel this shipment?')) return;
            const orderId = cancelBtn.dataset.orderId;
            fetch(ajaxUrl + 'cancelOrderShipment', {
                method: 'POST',
                body: new URLSearchParams({ order_id: orderId })
            })
                .then(r => r.json())
                .then(r => {
                    if (r.success) location.reload();
                    else showErrorModal(r.error || window.ALSENDO_MSG_CANCEL_FAILED || 'Cancellation failed');
                });
        }

        if (e.target.classList.contains('download-waybill-button') || e.target.closest('.download-waybill-button')) {
            const waybillBtn = e.target.classList.contains('download-waybill-button') ? e.target : e.target.closest('.download-waybill-button');
            const orderId = waybillBtn.dataset.orderId;
            fetch(ajaxUrl + 'downloadShipmentWaybill', {
                method: 'POST',
                body: new URLSearchParams({ order_id: orderId })
            })
                .then(r => {
                    var ct = r.headers.get('content-type') || '';
                    if (ct.indexOf('application/pdf') !== -1) {
                        return r.blob().then(function(blob) {
                            var url = URL.createObjectURL(blob);
                            var a = document.createElement('a');
                            a.href = url;
                            a.download = 'waybill_' + orderId + '.pdf';
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            URL.revokeObjectURL(url);
                        });
                    }
                    return r.json().then(function(data) {
                        if (data && data.error) {
                            showErrorModal(data.error);
                        } else {
                            showErrorModal(window.ALSENDO_MSG_WAYBILL_FAILED || 'Failed to download waybill');
                        }
                    });
                })
                .catch(function() {
                    showErrorModal(window.ALSENDO_MSG_WAYBILL_FAILED || 'Failed to download waybill');
                });
        }
    });

    if (tagSelect) {
        tagSelect.addEventListener('change', function() {
            if (this.value) {
                if (this.value === 'custom_text') {
                    document.getElementById('package-content-custom-col').style.display = 'block';
                    contentInput.value = 'custom_text';
                } else {
                    document.getElementById('package-content-custom-col').style.display = 'none';
                    contentInput.value = '{' + this.value + '}';
                }
                this.value = '';
            }
        });
    }

    const packageTemplateSelect = document.getElementById('package-template-select');
    if (packageTemplateSelect && window.ALSENDO_PACKAGE_TEMPLATES) {
        packageTemplateSelect.addEventListener('change', function() {
            const selectedIndex = parseInt(this.value);

            if (isNaN(selectedIndex) || !window.ALSENDO_PACKAGE_TEMPLATES[selectedIndex]) {
                return;
            }

            const template = window.ALSENDO_PACKAGE_TEMPLATES[selectedIndex];

            const typeSelect = form.querySelector('[name="package_shipment_type"]');
            if (typeSelect && template.package_type) {
                typeSelect.value = template.package_type;
            }

            const nstdCheckbox = form.querySelector('[name="package_is_nstd"][type="checkbox"]');
            if (nstdCheckbox) {
                nstdCheckbox.checked = !!(template.is_nstd);
            }

            const widthInput = form.querySelector('[name="package_width"]');
            if (widthInput && template.width !== undefined) {
                widthInput.value = template.width;
            }

            const lengthInput = form.querySelector('[name="package_length"]');
            if (lengthInput && template.length !== undefined) {
                lengthInput.value = template.length;
            }

            const heightInput = form.querySelector('[name="package_height"]');
            if (heightInput && template.height !== undefined) {
                heightInput.value = template.height;
            }

            const weightInput = form.querySelector('[name="package_weight"]');
            if (weightInput && template.weight !== undefined) {
                weightInput.value = template.weight;
            }

            const contentInputField = form.querySelector('[name="package_content"]');
            if (contentInputField && template.package_content !== undefined) {
                contentInputField.value = template.package_content;
            }

            const declaredValueInput = form.querySelector('[name="package_declared_value"]');
            if (declaredValueInput) {
                var declVal = parseFloat(template.declared_value) || 0;
                if (window.ALSENDO_AUTO_DECLARED_VALUE) {
                    var orderTotal = window.ALSENDO_ORDER_TOTAL || 0;
                    declaredValueInput.value = (declVal > orderTotal) ? declVal : orderTotal;
                } else {
                    declaredValueInput.value = declVal;
                }
            }

            const pickupTypeSelect = form.querySelector('[name="selected_pickup_type"]');
            if (pickupTypeSelect && template.pickup_type) {
                pickupTypeSelect.value = template.pickup_type;
                pickupTypeSelect.dispatchEvent(new Event('change'));
            }
        });
    }

    const senderTemplateSelect = document.getElementById('sender-template-select');
    if (senderTemplateSelect && window.ALSENDO_SENDER_TEMPLATES) {
        senderTemplateSelect.addEventListener('change', function() {
            const selectedIndex = parseInt(this.value);

            if (isNaN(selectedIndex) || !window.ALSENDO_SENDER_TEMPLATES[selectedIndex]) {
                return;
            }

            const template = window.ALSENDO_SENDER_TEMPLATES[selectedIndex];

            const addressTypeInput = document.getElementById('sender-address-type');
            const addressType = template.address_type || 'company';
            if (addressTypeInput) {
                addressTypeInput.value = addressType;
            }

            document.querySelectorAll('.sender-type-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.getAttribute('data-value') === addressType) {
                    btn.classList.add('active');
                }
            });

            const companyRow = document.getElementById('sender-company-row');
            if (companyRow) {
                companyRow.style.display = (addressType === 'home') ? 'none' : 'block';
            }

            const companyInput = form.querySelector('[name="sender_company_name"]');
            if (companyInput && template.company !== undefined) {
                companyInput.value = template.company;
            }

            const fullNameInput = form.querySelector('[name="sender_full_name"]');
            if (fullNameInput) {
                const fullName = [template.firstname || '', template.lastname || ''].filter(x => x).join(' ');
                fullNameInput.value = fullName;
            }

            const streetInput = form.querySelector('[name="sender_street"]');
            if (streetInput && template.street !== undefined) {
                streetInput.value = template.street;
            }

            const buildingInput = form.querySelector('[name="sender_building_number"]');
            if (buildingInput && template.building !== undefined) {
                buildingInput.value = template.building;
            }

            const apartmentInput = form.querySelector('[name="sender_apartment_number"]');
            if (apartmentInput && template.apartment !== undefined) {
                apartmentInput.value = template.apartment;
            }

            const blockInput = form.querySelector('[name="sender_block"]');
            if (blockInput && template.block !== undefined) {
                blockInput.value = template.block;
            }

            const entranceInput = form.querySelector('[name="sender_entrance"]');
            if (entranceInput && template.entrance !== undefined) {
                entranceInput.value = template.entrance;
            }

            const floorInput = form.querySelector('[name="sender_floor"]');
            if (floorInput && template.floor !== undefined) {
                floorInput.value = template.floor;
            }

            const flatInput = form.querySelector('[name="sender_flat"]');
            if (flatInput && template.flat !== undefined) {
                flatInput.value = template.flat;
            }

            const postalInput = form.querySelector('[name="sender_postal_code"]');
            if (postalInput && template.postal !== undefined) {
                postalInput.value = template.postal;
            }

            const cityInput = form.querySelector('[name="sender_city"]');
            if (cityInput && template.city !== undefined) {
                cityInput.value = template.city;
            }

            const contactInput = form.querySelector('[name="sender_contact_person"]');
            if (contactInput && template.contact !== undefined) {
                contactInput.value = template.contact;
            }

            const phoneInput = form.querySelector('[name="sender_phone_number"]');
            if (phoneInput && template.phone !== undefined) {
                phoneInput.value = template.phone;
            }

            const emailInput = form.querySelector('[name="sender_email"]');
            if (emailInput && template.email !== undefined) {
                emailInput.value = template.email;
            }

            const bankInput = form.querySelector('[name="sender_bank_account_number"]');
            if (bankInput && template.bank !== undefined) {
                bankInput.value = template.bank;
            }

            const bankCodeInput = form.querySelector('[name="sender_bank_code"]');
            if (bankCodeInput) {
                bankCodeInput.value = template.bank_code || '';
            }
            const ibanInput = form.querySelector('[name="sender_additional_bank_account_number"]');
            if (ibanInput) {
                ibanInput.value = template.additional_bank_account_number || '';
            }
            const externalIdInput = form.querySelector('[name="sender_external_id"]');
            if (externalIdInput) {
                externalIdInput.value = template.external_id || '';
            }

            const countrySelect = form.querySelector('[name="sender_country"]');
            if (countrySelect && template.country !== undefined) {
                countrySelect.value = template.country;
            }
        });

    }

    const senderTypeButtons = document.querySelectorAll('.sender-type-btn');
    if (senderTypeButtons.length > 0) {
        senderTypeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const value = this.getAttribute('data-value');

                senderTypeButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const addressTypeInput = document.getElementById('sender-address-type');
                if (addressTypeInput) {
                    addressTypeInput.value = value;
                }

                const companyRow = document.getElementById('sender-company-row');
                if (companyRow) {
                    companyRow.style.display = (value === 'home') ? 'none' : 'block';
                }
            });
        });
    }

    const pickupTypeSelect = document.getElementById('selected_pickup_type');
    if (pickupTypeSelect) {
        pickupTypeSelect.addEventListener('change', function() {
            const pickupType = this.value;
            const courierDateContainer = document.getElementById('shipment-preferred-pickup-date-container');
            const courierHoursContainer = document.getElementById('shipment-preferred-pickup-hours-container');
            const pickupPointContainer = document.getElementById('shipment-pickup-point-container');

            if (selectedServiceInput) {
                selectedServiceInput.value = '';
            }
            if (quoteContainer) {
                quoteContainer.style.display = 'none';
            }
            if (quoteInner) {
                quoteInner.innerHTML = '';
            }

            var isCzRegion = typeof ALSENDO_SERVICES_CAPABILITIES !== 'undefined';
            var showCourierFields = !isCzRegion && pickupType === 'COURIER';
            if (courierDateContainer) {
                courierDateContainer.style.display = showCourierFields ? 'block' : 'none';
                var dateEl = courierDateContainer.querySelector('input[name="shipment_preferred_pickup_date"]');
                if (dateEl) dateEl.disabled = !showCourierFields;
            }

            if (courierHoursContainer) {
                courierHoursContainer.style.display = showCourierFields ? 'block' : 'none';
                courierHoursContainer.querySelectorAll('input').forEach(function(el) { el.disabled = !showCourierFields; });
            }

            if (pickupPointContainer) {
                pickupPointContainer.style.display = (pickupType === 'SELF' && !window.ALSENDO_CARRIER_SKIP_MERCHANT_PICKUP) ? 'block' : 'none';
            }

            if (pickupType === 'COURIER') {
                const dateInput = document.querySelector('input[name="shipment_preferred_pickup_date"]');
                const hoursFromInput = document.querySelector('input[name="shipment_preferred_pickup_hours_from"]');
                const hoursToInput = document.querySelector('input[name="shipment_preferred_pickup_hours_to"]');

                if (dateInput && !dateInput.value) {
                    const defaultDate = new Date();
                    if (typeof ALSENDO_SAME_DAY_PICKUP === 'undefined' || !ALSENDO_SAME_DAY_PICKUP) {
                        defaultDate.setDate(defaultDate.getDate() + 1);
                    }
                    const year = defaultDate.getFullYear();
                    const month = String(defaultDate.getMonth() + 1).padStart(2, '0');
                    const day = String(defaultDate.getDate()).padStart(2, '0');
                    dateInput.value = `${year}-${month}-${day}`;
                }

                if (dateInput) {
                    const today = new Date();
                    const year = today.getFullYear();
                    const month = String(today.getMonth() + 1).padStart(2, '0');
                    const day = String(today.getDate()).padStart(2, '0');
                    dateInput.min = `${year}-${month}-${day}`;
                }

                if (hoursFromInput && !hoursFromInput.value) {
                    hoursFromInput.value = hoursFromInput.defaultValue || '08:00';
                }
                if (hoursToInput && !hoursToInput.value) {
                    hoursToInput.value = hoursToInput.defaultValue || '17:00';
                }
            }
        });

        pickupTypeSelect.dispatchEvent(new Event('change'));
    }
});
