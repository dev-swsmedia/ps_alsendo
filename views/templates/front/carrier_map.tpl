{*
* Alsendo - PrestaShop shipping module
*
* @author    Innovation Software
* @copyright 2026 Innovation Software
* @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
*}
<link rel="stylesheet" href="{$alsendo_map_css_url|escape:'html':'UTF-8'}" media="screen">
<script type="text/javascript" src="{$alsendo_map_js_url|escape:'html':'UTF-8'}" defer></script>
<script type="text/javascript" src="{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}alsendo/views/js/map-widget.js" defer></script>

<div id="alsendo-carrier-map-container"
     style="margin-bottom: 15px; display: block; transition: opacity 0.15s ease;"
     data-cart-id="{$alsendo_cart->id|intval|escape:'html':'UTF-8'}"
     data-map-config="{$alsendo_map_config|escape:'html':'UTF-8'}"
     data-shipping-methods="{$alsendo_shipping_methods_json|escape:'html':'UTF-8'}">
    <input type="hidden" id="alsendo_pickup_point" name="alsendo_pickup_point" data-map-selector="pickup" readonly
           value="{$alsendo_pickup_point|escape:'html':'UTF-8'}" />

    <input type="text" id="alsendo_pickup_point_display" readonly data-map-selector="pickup" class="form-control"
           placeholder="{l s='No pickup point selected' mod='alsendo'}"
           value="{$alsendo_pickup_point_display|escape:'html':'UTF-8'}" />
    <div class="clearfix"></div>
    <button id="alsendo-carrier-map-btn" class="btn btn-primary" type="button" style="margin-top: 10px;">
        {l s='Select pickup point' mod='alsendo'}
    </button>
</div>

<script>
    var ALSENDO_MSG_SELECT_PICKUP = "{l s='Please select a pickup point' mod='alsendo' js=1}";
    window.ALSENDO_MAP_TRANSLATIONS = {ldelim}
        loading: "{l s='Loading map...' mod='alsendo' js=1}",
        failed: "{l s='Map widget failed to load' mod='alsendo' js=1}",
        close: "{l s='Close' mod='alsendo' js=1}",
        retry: "{l s='Try again' mod='alsendo' js=1}"
    {rdelim};
    document.addEventListener('DOMContentLoaded', function() {
        var mapContainerEl = document.getElementById('alsendo-carrier-map-container');
        var alsendoCarriers;
        try {
            alsendoCarriers = mapContainerEl ? JSON.parse(mapContainerEl.dataset.shippingMethods || '[]') : [];
        } catch (e) {
            alsendoCarriers = [];
        }
        try {
            window.ALSENDO_MAP_CONFIG = mapContainerEl ? JSON.parse(mapContainerEl.dataset.mapConfig || '{}') : {};
        } catch (e) {
            window.ALSENDO_MAP_CONFIG = {};
        }
        var alsendoShippingCountry = '{$alsendo_shipping_country|escape:'javascript':'UTF-8'}';

        function moveMapBelowCarrier(mapContainer, selectedRadio) {
            if (!mapContainer || !selectedRadio) {
                return;
            }
            var deliveryOption = selectedRadio.closest('.delivery-option');
            if (!deliveryOption) {
                return;
            }
            var insertAfter = deliveryOption;
            var next = deliveryOption.nextElementSibling;
            while (next && !next.classList.contains('delivery-option') && !next.querySelector('input[type="radio"][name^="delivery_option"]')) {
                insertAfter = next;
                next = next.nextElementSibling;
            }
            insertAfter.insertAdjacentElement('afterend', mapContainer);
        }

        function updateMapVisibility() {
            var selectedCarrierId = null;
            var selectedRadio = null;
            var deliveryOptions = document.querySelectorAll('input[type="radio"][name^="delivery_option"]');
            deliveryOptions.forEach(function(input) {
                if (input.checked) {
                    selectedRadio = input;
                    var parts = input.value.split(',');
                    selectedCarrierId = parseInt(parts[0], 10);
                }
            });

            var showMap = false;
            var mapOperator = null;
            if (selectedCarrierId && alsendoCarriers && Array.isArray(alsendoCarriers)) {
                alsendoCarriers.forEach(function(carrier) {
                    if (parseInt(carrier.id_carrier, 10) === selectedCarrierId && carrier.map === true) {
                        showMap = true;
                        mapOperator = carrier.operator || null;
                    }
                });
            }

            var mapContainer = document.getElementById('alsendo-carrier-map-container');
            if (mapContainer) {
                if (showMap && selectedRadio) {
                    moveMapBelowCarrier(mapContainer, selectedRadio);
                    mapContainer.style.display = 'block';
                } else {
                    mapContainer.style.display = 'none';
                }
            }

            updateConfirmButtonState(showMap);
            return { showMap: showMap, mapOperator: mapOperator };
        }

        function updateConfirmButtonState(mapRequired) {
            var confirmBtn = document.querySelector('button[name="confirmDeliveryOption"]');
            var pickupPointInput = document.getElementById('alsendo_pickup_point');

            if (confirmBtn && mapRequired && pickupPointInput) {
                if (!pickupPointInput.value) {
                    confirmBtn.disabled = true;
                    confirmBtn.style.opacity = '0.5';
                    confirmBtn.style.cursor = 'not-allowed';

                    var msgEl = document.getElementById('alsendo-pickup-required-message');
                    if (!msgEl) {
                        msgEl = document.createElement('div');
                        msgEl.id = 'alsendo-pickup-required-message';
                        msgEl.className = 'alert alert-warning';
                        msgEl.textContent = ALSENDO_MSG_SELECT_PICKUP;
                        if (confirmBtn.parentNode) {
                            confirmBtn.parentNode.insertBefore(msgEl, confirmBtn);
                        }
                    }
                } else {
                    confirmBtn.disabled = false;
                    confirmBtn.style.opacity = '';
                    confirmBtn.style.cursor = 'pointer';

                    var msgEl = document.getElementById('alsendo-pickup-required-message');
                    if (msgEl) {
                        msgEl.remove();
                    }
                }
            } else if (confirmBtn && !mapRequired) {
                confirmBtn.disabled = false;
                confirmBtn.style.opacity = '';
                confirmBtn.style.cursor = 'pointer';

                var msgEl = document.getElementById('alsendo-pickup-required-message');
                if (msgEl) {
                    msgEl.remove();
                }
            }
        }

        // Expose for map-widget.js callback after pickup point selection.
        window.alsendoUpdateConfirmButtonState = updateConfirmButtonState;

        updateMapVisibility();

        var deliveryOptions = document.querySelectorAll('input[type="radio"][name^="delivery_option"]');
        deliveryOptions.forEach(function(radio) {
            radio.addEventListener('change', function() {
                var ppInput = document.getElementById('alsendo_pickup_point');
                var ppDisplay = document.getElementById('alsendo_pickup_point_display');
                if (ppInput) {
                    ppInput.value = '';
                }
                if (ppDisplay) {
                    ppDisplay.value = '';
                }
                updateMapVisibility();
            });
        });

        var mapBtn = document.getElementById('alsendo-carrier-map-btn');
        if (mapBtn) {
            mapBtn.addEventListener('click', function(e) {
                e.preventDefault();
                var state = updateMapVisibility();
                var selectedCarrierCountry = alsendoShippingCountry;
                if (state.mapOperator && alsendoCarriers && Array.isArray(alsendoCarriers)) {
                    alsendoCarriers.forEach(function(carrier) {
                        if (carrier.operator === state.mapOperator && carrier.country) {
                            selectedCarrierCountry = carrier.country;
                        }
                    });
                }
                if (state.mapOperator) {
                    window.openPointMap('pickup', [state.mapOperator], 'DELIVERY', selectedCarrierCountry);
                } else {
                    window.openPointMap('pickup', null, 'DELIVERY', selectedCarrierCountry);
                }
            });
        }

        var pickupPointInput = document.getElementById('alsendo_pickup_point');
        if (pickupPointInput) {
            pickupPointInput.addEventListener('change', function() {
                updateConfirmButtonState(true);
            });
        }

        if (typeof prestashop !== 'undefined') {
            prestashop.on('updatedDeliveryForm', function() {
                setTimeout(function() {
                    var radios = document.querySelectorAll('input[type="radio"][name^="delivery_option"]');
                    radios.forEach(function(radio) {
                        radio.addEventListener('change', function() {
                            updateMapVisibility();
                        });
                    });
                    updateMapVisibility();
                }, 50);
            });
        }
    });
</script>
