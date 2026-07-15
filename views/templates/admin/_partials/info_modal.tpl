{*
* Alsendo - PrestaShop shipping module
*
* @author    Innovation Software
* @copyright 2026 Innovation Software
* @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
*}

<link rel="stylesheet" href="{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}alsendo/views/css/alsendo-admin.css">

<div id="info-modal">
    <div>
        <p id="info-modal-message"></p>
        <div class="text-end">
            <button type="button" class="btn btn-secondary" id="info-modal-ok">{l s='OK' mod='alsendo'}</button>
        </div>
    </div>
</div>

{literal}
    <script>
        function showInfoModal(msg){
            var el = document.getElementById('info-modal-message');
            if (el) el.innerText = msg;
            document.getElementById('info-modal').style.display='flex';
        }
        document.addEventListener('DOMContentLoaded',function(){
            var ok = document.getElementById('info-modal-ok');
            if (ok) ok.addEventListener('click',function(){
                document.getElementById('info-modal').style.display='none';
            });
        });
    </script>
{/literal}