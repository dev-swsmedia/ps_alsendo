/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

var currentPickupTarget = null;

function alsendoEnsureSpinStyle() {
    if (document.getElementById('alsendo-spin-style')) {
        return;
    }
    var style = document.createElement('style');
    style.id = 'alsendo-spin-style';
    style.textContent = '@keyframes alsendo-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
    document.head.appendChild(style);
}

function alsendoGetOrCreateOverlay() {
    // Prefer a pre-rendered overlay if a template still ships it (legacy support);
    // otherwise build the modal HTML on the fly so the map works regardless of
    // whether the host template included a server-side partial.
    var overlay = document.getElementById('alsendo-map-widget-overlay');
    if (overlay) {
        return overlay;
    }

    overlay = document.createElement('div');
    overlay.id = 'alsendo-map-widget-overlay';
    overlay.style.cssText = 'display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999;';

    var modal = document.createElement('div');
    modal.id = 'alsendo-map-widget-modal';
    modal.style.cssText = 'position:absolute; top:5%; left:5%; width:90%; height:90%; background:#fff; border-radius:8px; overflow:hidden;';

    var closeBtn = document.createElement('button');
    closeBtn.id = 'alsendo-map-widget-close';
    closeBtn.type = 'button';
    closeBtn.innerHTML = '&times;';
    closeBtn.style.cssText = 'position:absolute; top:10px; right:10px; z-index:10000; background:#fff; border:1px solid #ccc; border-radius:50%; width:40px; height:40px; cursor:pointer; font-size:24px; line-height:1; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 5px rgba(0,0,0,0.3);';

    var widget = document.createElement('div');
    widget.id = 'alsendo-map-widget';
    widget.style.cssText = 'width:100%; height:100%;';

    modal.appendChild(closeBtn);
    modal.appendChild(widget);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    return overlay;
}

window.openPointMap = function(pickupInputGroup, operatorNames, posType, countryCode, initialCity) {
    currentPickupTarget = pickupInputGroup;
    var config = window.ALSENDO_MAP_CONFIG || {};
    var operators = resolveOperators(operatorNames, config.operatorMap || {});
    if (!operators || !operators.length) {
        operators = (config.defaultOperators || []).map(function(op) {
            return { operator: op };
        });
    }

    var overlay = alsendoGetOrCreateOverlay();
    var modal = document.getElementById('alsendo-map-widget-modal');

    var oldWidget = document.getElementById('alsendo-map-widget');
    if (oldWidget) {
        oldWidget.remove();
    }
    var newWidget = document.createElement('div');
    newWidget.id = 'alsendo-map-widget';
    newWidget.style.width = '100%';
    newWidget.style.height = '100%';
    modal.appendChild(newWidget);

    var translations = window.ALSENDO_MAP_TRANSLATIONS || {};
    var msgLoading = translations.loading || 'Loading map...';
    var msgFailed = translations.failed || 'Map widget failed to load';
    var msgClose = translations.close || 'Close';
    var msgRetry = translations.retry || 'Try again';

    alsendoEnsureSpinStyle();
    overlay.style.display = 'block';
    newWidget.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;"><div style="text-align:center"><div style="border:4px solid #f3f3f3;border-top:4px solid #2196F3;border-radius:50%;width:40px;height:40px;animation:alsendo-spin 1s linear infinite;margin:0 auto 16px"></div><p style="color:#666">' + msgLoading + '</p></div></div>';

    var initRetries = 0;
    var maxRetries = 100;
    var initMap = function() {
        if (typeof BPWidget === 'undefined') {
            initRetries++;
            if (initRetries >= maxRetries) {
                newWidget.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;"><div style="text-align:center;padding:20px;"><p style="color:#d32f2f;font-size:16px;margin-bottom:12px;">' + msgFailed + '</p><button id="alsendo-map-retry-btn" style="margin-top:12px;padding:8px 24px;background:#2196F3;color:#fff;border:none;border-radius:4px;cursor:pointer;margin-right:8px;">' + msgRetry + '</button><button id="alsendo-map-close-fail-btn" style="margin-top:12px;padding:8px 24px;background:#757575;color:#fff;border:none;border-radius:4px;cursor:pointer;">' + msgClose + '</button></div></div>';
                var retryBtn = document.getElementById('alsendo-map-retry-btn');
                if (retryBtn) {
                    retryBtn.addEventListener('click', function() {
                        initRetries = 0;
                        initMap();
                    });
                }
                var closeFailBtn = document.getElementById('alsendo-map-close-fail-btn');
                if (closeFailBtn) {
                    closeFailBtn.addEventListener('click', function() {
                        overlay.style.display = 'none';
                    });
                }
                return;
            }
            setTimeout(initMap, 100);
            return;
        }

        newWidget.innerHTML = '';

        var widgetConfig = {
            operators: operators,
            posType: posType || 'DELIVERY',
            language: config.language || 'pl',
            codeSearch: true,
            operatorMarkers: config.operatorMarkers || false,
            testMode: false,
            callback: function(point) {
                alsendoOnPointSelected(point);
            }
        };

        if (countryCode) {
            widgetConfig.countryCodes = countryCode;
        } else if (config.countryCodes) {
            widgetConfig.countryCodes = config.countryCodes;
        }

        if (initialCity) {
            widgetConfig.initialAddress = initialCity;
        } else if (config.initialAddress) {
            widgetConfig.initialAddress = config.initialAddress;
        }
        BPWidget.init(newWidget, widgetConfig);
    };
    initMap();

    var closeBtn = document.getElementById('alsendo-map-widget-close');
    if (closeBtn) {
        closeBtn.onclick = function() {
            overlay.style.display = 'none';
        };
    }

    overlay.onclick = function(e) {
        if (e.target === overlay) {
            overlay.style.display = 'none';
        }
    };
};

function alsendoOnPointSelected(point) {
    var pointData = {
        code: point.code,
        name: point.description || point.street || 'Point',
        operator: point.operator,
        street: point.street,
        address: point.street + ', ' + point.postalCode + ' ' + point.city,
        latitude: point.latitude,
        longitude: point.longitude,
        description: point.description,
        city: point.city,
        postalCode: point.postalCode
    };
    var target = currentPickupTarget;

    var inputs = document.querySelectorAll('[data-map-selector="' + target + '"]');
    if (inputs.length) {
        inputs.forEach(function(input) {
            if (input.type === 'hidden') {
                input.value = JSON.stringify(pointData);
            } else if (input.type === 'text') {
                input.value = point.code + ' - ' + (point.description || point.street || 'Point');
            }
        });
    } else {
        var hidden = document.getElementById('alsendo_pickup_' + target);
        var display = document.getElementById('alsendo_pickup_' + target + '_display');
        if (hidden) {
            hidden.value = JSON.stringify(pointData);
        }
        if (display) {
            display.value = point.code + ' - ' + (point.description || point.street || 'Point');
        }
    }

    var cartElement = document.querySelector('[data-cart-id]');
    if (cartElement && cartElement.dataset.cartId) {
        alsendoSavePickupPointAjax(pointData, cartElement.dataset.cartId);
    }

    var overlay = document.getElementById('alsendo-map-widget-overlay');
    if (overlay) {
        overlay.style.display = 'none';
    }

    if (inputs.length) {
        inputs.forEach(function(input) {
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    if (typeof window.alsendoUpdateConfirmButtonState === 'function') {
        window.alsendoUpdateConfirmButtonState(true);
    }
}

function resolveOperators(operatorNames, operatorMap) {
    if (!operatorNames || !operatorNames.length) {
        return null;
    }

    operatorNames = operatorNames.filter(function(op) {
        return !op.toLowerCase().includes('fedex');
    });

    if (!operatorNames.length) {
        return null;
    }

    var result = operatorNames
        .map(function(op) {
            var opLower = op.toLowerCase().trim();

            for (var vKey in operatorMap) {
                if (opLower === operatorMap[vKey].toLowerCase()) {
                    return operatorMap[vKey];
                }
            }

            for (var key in operatorMap) {
                if (opLower === key) {
                    return operatorMap[key];
                }
            }

            for (var key2 in operatorMap) {
                var keyRegex = new RegExp('\\b' + key2 + '\\b|^' + key2, 'i');
                if (keyRegex.test(opLower)) {
                    return operatorMap[key2];
                }
            }

            for (var key3 in operatorMap) {
                if (opLower.indexOf(key3) !== -1 && key3.length > 3) {
                    return operatorMap[key3];
                }
            }

            return op.toUpperCase();
        })
        .filter(Boolean)
        .map(function(op) {
            return { operator: op };
        });

    return result.length ? result : null;
}

function alsendoSavePickupPointAjax(pointData, cartId) {
    if (!cartId) {
        return;
    }

    var pickupPointJson = JSON.stringify(pointData);

    fetch('/index.php?fc=module&module=alsendo&controller=ajax', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=save_pickup_point&id_cart=' + encodeURIComponent(cartId) +
            '&pickup_point=' + encodeURIComponent(pickupPointJson)
    })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success && typeof window.alsendoUpdateConfirmButtonState === 'function') {
                window.alsendoUpdateConfirmButtonState(true);
            }
        })
        .catch(function() {
            // Silent — Ajax persistence is best-effort; form submit will retry on order confirmation.
        });
}
