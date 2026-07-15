{*
* Alsendo - PrestaShop shipping module
*
* @author    Innovation Software
* @copyright 2026 Innovation Software
* @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
*}

<div id="alsendo-payment-pickup-point-info">
    <p>{$alsendo_pickup_point_display|escape:'html':'UTF-8'}</p>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var infoDiv = document.getElementById('alsendo-payment-pickup-point-info');
        var carrierNameSpan = document.querySelector('.summary-selected-carrier span.carrier-name');
        if (infoDiv && carrierNameSpan && carrierNameSpan.parentNode) {
            carrierNameSpan.parentNode.insertBefore(infoDiv, carrierNameSpan.nextSibling);
        }
    });
</script>