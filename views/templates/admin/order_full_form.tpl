{*
* Alsendo - PrestaShop shipping module
*
* @author    Innovation Software
* @copyright 2026 Innovation Software
* @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
*}

<link rel="stylesheet" href="{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}alsendo/views/css/alsendo-admin.css">

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>{l s='Alsendo Shipment for order' mod='alsendo'} #{$order_id|escape:'html':'UTF-8'}</h4>
        </div>

        <div class="card-body">
            <form id="alsendo-order-form" method="post" data-token="{$user_token|escape:'html':'UTF-8'}">
                <input type="hidden" name="order_id" value="{$order_id|escape:'html':'UTF-8'}">
                <input type="hidden" name="id_carrier" value="{$order_info.id_carrier|default:0|escape:'html':'UTF-8'}">
                <input type="hidden" id="order-view-url" value="{$order_view_url|escape:'html':'UTF-8'}">
                <input type="hidden" name="ajax" value="1">

                {if $alsendo_region != 'cz'}
                <div class="card">
                    <div class="card-header"><h4>{l s='Sender Template' mod='alsendo'}</h4></div>
                    <div class="card-body">
                        <select id="sender-template-select" class="form-control">
                            {if empty($sender_templates)}
                                <option value="">{l s='(No templates available)' mod='alsendo'}</option>
                            {else}
                                {assign var=currentSenderTemplate value=$alsendo_order_details.data.sender.template_name|default:''}
                                <option value="">{l s='Select a sender template...' mod='alsendo'}</option>
                                {foreach $sender_templates as $idx => $tpl name=senderLoop}
                                    {assign var=tplName value=$tpl.template_name|default:($tpl.company|default:'Template')}
                                    <option value="{$idx|escape:'html':'UTF-8'}" {if $currentSenderTemplate && $currentSenderTemplate == $tpl.template_name}selected{/if}>{$tplName|escape:'html':'UTF-8'}</option>
                                {/foreach}
                            {/if}
                        </select>
                    </div>
                </div>
                {/if}

                {if $alsendo_region == 'cz' && !empty($alsendo_address_book)}
                <div class="card">
                    <div class="card-header"><h4>{l s='Address Book' mod='alsendo'}</h4></div>
                    <div class="card-body">
                        <select id="sender-address-book-select" class="form-control">
                            <option value="">{l s='Select address from address book...' mod='alsendo'}</option>
                            {foreach $alsendo_address_book as $abId => $ab}
                                <option value="{$abId|escape:'html':'UTF-8'}" data-address='{$ab|json_encode|escape:'htmlall':'UTF-8'}'
                                    {if $sender_address_external_id_preselect == $ab.id}selected{/if}>
                                    {if $ab.company}{$ab.company|escape:'html':'UTF-8'} - {/if}
                                    {$ab.firstname|escape:'html':'UTF-8'} {$ab.surname|escape:'html':'UTF-8'} ({$ab.street|escape:'html':'UTF-8'}, {$ab.city|escape:'html':'UTF-8'})
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                {/if}

                <div class="card">
                    <div class="card-header"><h4>{l s='Sender Address' mod='alsendo'}</h4></div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">{l s='Address type' mod='alsendo'}</label>
                                <div class="btn-group w-100" role="group">
                                    {assign var=addrType value=$alsendo_order_details.data.sender.address_type|default:'company'}
                                    <button type="button" class="btn btn-outline-secondary sender-type-btn {if $addrType=='company'}active{/if}" data-value="company">{l s='Company' mod='alsendo'}</button>
                                    <button type="button" class="btn btn-outline-secondary sender-type-btn {if $addrType=='home'}active{/if}" data-value="home">{l s='Home' mod='alsendo'}</button>
                                </div>
                                <input type="hidden" name="sender_address_type" id="sender-address-type" value="{$addrType|escape:'html':'UTF-8'}">
                            </div>
                        </div>

                        <div class="row mb-3" id="sender-company-row" {if $addrType=='home'}style="display:none"{/if}>
                            <div class="col">
                                <label class="form-label">{l s='Company name' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="sender_company_name" value="{$alsendo_order_details.data.sender.company_name|default:''|escape:'html':'UTF-8'}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">{l s='Full name' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="sender_full_name" value="{$alsendo_order_details.data.sender.full_name|default:''|escape:'html':'UTF-8'}">
                            </div>
                        </div>

                        {if $alsendo_region == 'ro'}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{l s='Street' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="sender_street" value="{$alsendo_order_details.data.sender.street|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{l s='Building number' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="sender_building_number" value="{$alsendo_order_details.data.sender.building_number|default:''|escape:'html':'UTF-8'}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">{l s='Bloc' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="sender_block" value="{$alsendo_order_details.data.sender.block|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{l s='Scara' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="sender_entrance" value="{$alsendo_order_details.data.sender.entrance|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{l s='Etaj' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="sender_floor" value="{$alsendo_order_details.data.sender.floor|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{l s='Apartament' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="sender_flat" value="{$alsendo_order_details.data.sender.flat|default:''|escape:'html':'UTF-8'}">
                            </div>
                        </div>
                        {else}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">{l s='Street' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="sender_street" value="{$alsendo_order_details.data.sender.street|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{l s='Building number' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="sender_building_number" value="{$alsendo_order_details.data.sender.building_number|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{l s='Apartment number' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="sender_apartment_number" value="{$alsendo_order_details.data.sender.apartment_number|default:''|escape:'html':'UTF-8'}">
                            </div>
                        </div>
                        {/if}

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">{l s='Country' mod='alsendo'}</label>
                                {assign var=senderCountry value=$alsendo_order_details.data.sender.country|default:$alsendo_region|upper}
                                <select class="form-control" name="sender_country">
                                    {foreach from=$countries key=iso item=name}
                                        <option value="{$iso|escape:'html':'UTF-8'}" {if $senderCountry == $iso}selected{/if}>{$name|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{l s='Postal code' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="sender_postal_code" value="{$alsendo_order_details.data.sender.postal_code|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{l s='City' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="sender_city" value="{$alsendo_order_details.data.sender.city|default:''|escape:'html':'UTF-8'}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{l s='Contact person' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="sender_contact_person" value="{$alsendo_order_details.data.sender.contact_person|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{l s='Phone' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="sender_phone_number" value="{$alsendo_order_details.data.sender.phone_number|default:''|escape:'html':'UTF-8'}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{l s='Email' mod='alsendo'}</label>
                                <input type="email" class="form-control" name="sender_email" value="{$alsendo_order_details.data.sender.email|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{l s='Bank account number' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="sender_bank_account_number" value="{$alsendo_order_details.data.sender.bank_account_number|default:$alsendo_sender.bank_account_number|default:''|escape:'html':'UTF-8'}">
                            </div>
                        </div>
                        {if $alsendo_region == 'cz'}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{l s='Bank code' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="sender_bank_code" maxlength="4" value="{$alsendo_order_details.data.sender.bank_code|default:$alsendo_sender.bank_code|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{l s='IBAN bank account number' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="sender_additional_bank_account_number" value="{$alsendo_order_details.data.sender.additional_bank_account_number|default:$alsendo_sender.additional_bank_account_number|default:''|escape:'html':'UTF-8'}">
                            </div>
                        </div>
                        <input type="hidden" name="sender_external_id" value="{$alsendo_order_details.data.sender.external_id|default:$alsendo_sender.external_id|default:''|escape:'html':'UTF-8'}">
                        {/if}
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h4>{l s='Recipient Address' mod='alsendo'}</h4></div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">{l s='First Name' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="shipping_first_name" value="{$order_info.firstname|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col">
                                <label class="form-label">{l s='Last Name' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="shipping_last_name" value="{$order_info.lastname|default:''|escape:'html':'UTF-8'}">
                            </div>
                        </div>
                        {if $alsendo_region == 'ro'}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{l s='Street' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="shipping_street" value="{$order_info.street|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{l s='Building number' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="shipping_building_number" value="{$order_info.building_number|default:''|escape:'html':'UTF-8'}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">{l s='Bloc' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="shipping_block" value="{$order_info.shipping_block|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{l s='Scara' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="shipping_entrance" value="{$order_info.shipping_entrance|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{l s='Etaj' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="shipping_floor" value="{$order_info.shipping_floor|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{l s='Apartament' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="shipping_flat" value="{$order_info.shipping_flat|default:''|escape:'html':'UTF-8'}">
                            </div>
                        </div>
                        {else}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">{l s='Street' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="shipping_street" value="{$order_info.street|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{l s='Building number' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="shipping_building_number" value="{$order_info.building_number|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{l s='Apartment number' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="shipping_apartment_number" value="{$order_info.apartment_number|default:''|escape:'html':'UTF-8'}">
                            </div>
                        </div>
                        {/if}
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">{l s='Postal Code' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="shipping_postal_code" value="{$order_info.postcode|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col">
                                <label class="form-label">{l s='City' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="shipping_city" value="{$order_info.city|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col">
                                <label class="form-label">{l s='Country' mod='alsendo'}</label>
                                <select class="form-control" name="shipping_country">
                                    {foreach $countries as $iso=>$name}
                                        <option value="{$iso|escape:'html':'UTF-8'}" {if $order_info.iso_code == $iso}selected{/if}>{$name|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">{l s='Phone' mod='alsendo'}</label>
                                <input type="text" class="form-control" id="shipping-phone-number" name="shipping_phone_number" value="{$alsendo_order_details.data.recipient.phone|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">{l s='Email' mod='alsendo'}</label>
                                <input type="email" class="form-control" id="shipping-email" name="shipping_email" value="{$alsendo_order_details.data.recipient.email|default:''|escape:'html':'UTF-8'}">
                            </div>
                        </div>

                        {assign var=custPP value=$alsendo_order_details.data.shipment.customer_pickup_point_display|default:''}
                        <input type="hidden" name="shipment_pickup_point" value="{$alsendo_order_details.data.shipment.customer_pickup_point_json|default:''|escape:'html':'UTF-8'}" data-map-selector="customer-pickup-point">
                        <input type="text" data-map-selector="customer-pickup-point" value="{$custPP|escape:'html':'UTF-8'}" style="display:none">

                        <div id="customer-pickup-point-selected" class="alert alert-info d-flex align-items-center justify-content-between mb-0" style="{if empty($custPP)}display:none!important{/if}">
                            <div>
                                <strong>{l s='Customer Pickup Point (Where customer receives)' mod='alsendo'}:</strong><br>
                                <span id="customer-pickup-point-display-text">{$custPP|escape:'html':'UTF-8'}</span>
                            </div>
                            <div class="ms-3" style="white-space:nowrap">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="change-customer-pickup-point-btn">{l s='Change' mod='alsendo'}</button>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="clear-customer-pickup-point-btn">{l s='Clear' mod='alsendo'}</button>
                            </div>
                        </div>

                        <div id="customer-pickup-point-empty" class="alert alert-secondary d-flex align-items-center justify-content-between mb-0" style="{if !empty($custPP)}display:none!important{/if}">
                            <span>{l s='No customer pickup point selected' mod='alsendo'}</span>
                            <button type="button" class="btn btn-sm btn-outline-primary ms-3" id="select-customer-pickup-point-btn">{l s='Select pickup point' mod='alsendo'}</button>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h4>{l s='Package Template' mod='alsendo'}</h4></div>
                    <div class="card-body">
                        <select id="package-template-select" class="form-control">
                            {if empty($package_templates)}
                                <option value="">{l s='(No templates available)' mod='alsendo'}</option>
                            {else}
                                {assign var=currentPackageTemplate value=$alsendo_order_details.data.package.template_name|default:''}
                                <option value="">{l s='Select a package template...' mod='alsendo'}</option>
                                {foreach $package_templates as $pidx => $ptpl name=packageLoop}
                                    <option value="{$pidx|escape:'html':'UTF-8'}" {if $currentPackageTemplate && $currentPackageTemplate == $ptpl.name}selected{/if}>{$ptpl.name|default:'Template'}</option>
                                {/foreach}
                            {/if}
                        </select>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h4>{l s='Package Details' mod='alsendo'}</h4></div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">{l s='Package type' mod='alsendo'}</label>
                                {if $alsendo_region == 'cz'}
                                    {assign var=defaultPkgFallback value='PACKAGE'}
                                {elseif $alsendo_region == 'ro'}
                                    {assign var=defaultPkgFallback value='package'}
                                {else}
                                    {assign var=defaultPkgFallback value='PACZKA'}
                                {/if}
                                {assign var=pkgType value=$alsendo_order_details.data.package.shipment_type|default:$alsendo_order_details.data.package.shipment_packaging|default:$defaultPkgFallback}
                                <select class="form-control" name="package_shipment_type">
                                    {if !empty($alsendo_package_types)}
                                        {foreach $alsendo_package_types as $pt}
                                            <option value="{$pt.type|escape:'html':'UTF-8'}" {if $pkgType==$pt.type}selected{/if}>{$pt.desc|default:$pt.type|escape:'html':'UTF-8'}</option>
                                        {/foreach}
                                    {else}
                                        {if $alsendo_region == 'cz'}
                                            <option value="PACKAGE" {if $pkgType=='PACKAGE'}selected{/if}>PACKAGE</option>
                                        {elseif $alsendo_region == 'ro'}
                                            <option value="package" {if $pkgType=='package'}selected{/if}>package</option>
                                        {else}
                                            <option value="PACZKA" {if $pkgType=='PACZKA'}selected{/if}>PACZKA</option>
                                        {/if}
                                    {/if}
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                {assign var=isNstd value=$alsendo_order_details.data.package.is_nstd|default:0}
                                <div class="form-check mb-2">
                                    <input type="hidden" name="package_is_nstd" value="0">
                                    <input class="form-check-input" type="checkbox" name="package_is_nstd" id="package-is-nstd" value="1" {if $isNstd}checked{/if}>
                                    <label class="form-check-label" for="package-is-nstd">{l s='Non-standard shipment' mod='alsendo'}</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label class="form-label">{l s='Width (cm)' mod='alsendo'}</label>
                                <input type="number" step="0.01" class="form-control" name="package_width" value="{$alsendo_order_details.data.package.width|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col">
                                <label class="form-label">{l s='Length (cm)' mod='alsendo'}</label>
                                <input type="number" step="0.01" class="form-control" name="package_length" value="{$alsendo_order_details.data.package.length|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col">
                                <label class="form-label">{l s='Height (cm)' mod='alsendo'}</label>
                                <input type="number" step="0.01" class="form-control" name="package_height" value="{$alsendo_order_details.data.package.height|default:''|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col">
                                <label class="form-label">{l s='Weight (kg)' mod='alsendo'}</label>
                                <input type="number" step="0.01" class="form-control" name="package_weight" value="{$alsendo_order_details.data.package.weight|default:''|escape:'html':'UTF-8'}">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-8">
                                <label class="form-label">{l s='Content' mod='alsendo'}</label>
                                {assign var=currentContent value=$alsendo_order_details.data.package.package_content|default:''}
                                <input type="text" class="form-control" name="package_content" id="package-content-input" value="{$currentContent|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{l s='Insert tag' mod='alsendo'}</label>
                                <select class="form-control" id="package-content-tags">
                                    <option value="">{l s='Select tag...' mod='alsendo'}</option>
                                    {foreach from=$available_tags key=tagKey item=tagLabel}
                                        <option value="{$tagKey|escape:'html':'UTF-8'}">{$tagLabel|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>

                        {assign var=isCustomText value=($currentContent=='custom_text')}
                        <div class="row mt-2" id="package-content-custom-col" style="display:{if $isCustomText}block{else}none{/if};">
                            <div class="col-md-12">
                                <label class="form-label">{l s='Custom text value' mod='alsendo'}</label>
                                <input type="text" class="form-control" name="package_content_custom_text" placeholder="{l s='Enter custom text' mod='alsendo'}">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col">
                                <label class="form-label">{l s='Cash on Delivery' mod='alsendo'}</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" name="package_cod" value="{$alsendo_order_details.data.package.cod_value|default:''|escape:'html':'UTF-8'}">
                                    <span class="input-group-text">{$alsendo_currency|default:'PLN'|escape:'html':'UTF-8'}</span>
                                </div>
                            </div>
                            <div class="col">
                                <label class="form-label">{l s='Declared Value' mod='alsendo'}</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" name="package_declared_value" value="{$alsendo_order_details.data.package.declared_value|default:''|escape:'html':'UTF-8'}">
                                    <span class="input-group-text">{$alsendo_currency|default:'PLN'|escape:'html':'UTF-8'}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h4>{l s='Shipment Details' mod='alsendo'}</h4></div>
                    <div class="card-body">
                        <p class="mb-2">{l s='Shipping Method' mod='alsendo'}: <strong>{$order_info.carrier_name|default:'-'|escape:'html':'UTF-8'}</strong></p>

                        <div id="get-a-quote-container" class="mb-2">
                            <span class="alsendo-muted">{l s='Price' mod='alsendo'}: </span>
                            <button type="button" class="alsendo-link" id="get-quote-button">{l s='Get a quote' mod='alsendo'}</button>
                        </div>

                        <div id="courier-services-container" class="mt-2 d-none">
                            <div id="courier-services-container-inner" class="alsendo-quote-grid"></div>
                            <input type="hidden" id="shipment-selected-service" name="shipment_selected_service" value="">
                        </div>

                        <div class="form-group mt-3">
                            <label class="form-label">{l s='Pickup Type' mod='alsendo'}</label>
                            <select class="form-control" id="selected_pickup_type" name="selected_pickup_type">
                                {foreach from=$pickup_types item=pt}
                                    <option value="{$pt|escape:'html':'UTF-8'}" {if $alsendo_order_details.data.package.pickup_type==$pt}selected{/if}>{$pickup_type_labels[$pt]|escape:'html':'UTF-8'}</option>
                                {/foreach}
                            </select>
                        </div>

                        {if $alsendo_region == 'cz'}
                        <div class="form-check mt-3" id="ship-via-pickup-point-container" style="display:none;">
                            <input type="hidden" name="shipping_via_pickup_point" value="0">
                            <input class="form-check-input" type="checkbox" name="shipping_via_pickup_point" id="shipping-via-pickup-point" value="1"
                                {if $shipping_method_ship_via}checked{/if}>
                            <label class="form-check-label" for="shipping-via-pickup-point">
                                {l s='Ship via pickup point' mod='alsendo'}
                                <small class="text-muted">({l s='Drop off package at a branch instead of courier pickup' mod='alsendo'})</small>
                            </label>
                        </div>
                        <div class="form-check mt-3" id="pickup-request-container" style="display:none;">
                            <input type="hidden" name="pickup_request" value="0">
                            <input class="form-check-input" type="checkbox" name="pickup_request" id="pickup-request" value="1"
                                {if $shipping_method_pickup_request}checked{/if}>
                            <label class="form-check-label" for="pickup-request">
                                {l s='Request courier pickup' mod='alsendo'}
                            </label>
                        </div>
                        {/if}

                        {if $alsendo_region != 'cz'}
                        <div class="mt-3" id="shipment-preferred-pickup-date-container" style="{if $alsendo_order_details.data.package.pickup_type=='COURIER'}display:block{else}display:none{/if}">
                            <label class="form-label">
                                {l s='Preferred Pickup Date' mod='alsendo'}
                                <small class="text-muted">({l s='Cannot be in the past' mod='alsendo'})</small>
                            </label>
                            <input type="date" class="form-control" name="shipment_preferred_pickup_date" value="{$alsendo_order_details.data.shipment.preferred_pickup_date|default:''|escape:'html':'UTF-8'}" min="{$smarty.now|date_format:'Y-m-d'}"{if $alsendo_order_details.data.package.pickup_type != 'COURIER'} disabled{/if}>
                            <small class="form-text text-muted">
                                {l s='Select today or a future date for courier pickup' mod='alsendo'}
                            </small>
                        </div>

                        <div class="mt-3" id="shipment-preferred-pickup-hours-container" style="{if $alsendo_order_details.data.package.pickup_type=='COURIER'}display:block{else}display:none{/if}">
                            <label class="form-label">{l s='Preferred Pickup Hours' mod='alsendo'}</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">{l s='From' mod='alsendo'}</label>
                                    <input type="time" class="form-control" name="shipment_preferred_pickup_hours_from" value="{$alsendo_order_details.data.shipment.preferred_pickup_hours_from|default:'08:00'|escape:'html':'UTF-8'}"{if $alsendo_order_details.data.package.pickup_type != 'COURIER'} disabled{/if}>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{l s='To' mod='alsendo'}</label>
                                    <input type="time" class="form-control" name="shipment_preferred_pickup_hours_to" value="{$alsendo_order_details.data.shipment.preferred_pickup_hours_to|default:'17:00'|escape:'html':'UTF-8'}"{if $alsendo_order_details.data.package.pickup_type != 'COURIER'} disabled{/if}>
                                </div>
                            </div>
                        </div>
                        {/if}

                        <div class="mt-3" id="shipment-pickup-point-container" style="{if !$carrier_skip_merchant_pickup && ($alsendo_order_details.data.package.pickup_type=='SELF' || ($alsendo_region == 'cz' && $shipping_method_ship_via))}display:block{else}display:none{/if}">
                            <label class="form-label">{l s='Sender Pickup Point (Where YOU drop off)' mod='alsendo'}</label>
                            <input type="hidden" name="merchant_pickup_point" value="{$alsendo_order_details.data.shipment.merchant_pickup_point_json|default:''|escape:'html':'UTF-8'}" data-map-selector="merchant-pickup-point">
                            <input type="text" readonly class="form-control" name="merchant_pickup_point_display" value="{$alsendo_order_details.data.shipment.merchant_pickup_point_display|default:''|escape:'html':'UTF-8'}" data-map-selector="merchant-pickup-point">
                            <button type="button" class="btn btn-outline-primary mt-2" id="select-sender-pickup-point-btn">{l s='Select from map' mod='alsendo'}</button>
                        </div>

                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-success" id="save-order-details-btn" style="margin-right: 8px;">{l s='Save' mod='alsendo'}</button>
                            <button type="submit" class="btn btn-primary" id="order-shipment-btn">{l s='Submit Shipment' mod='alsendo'}</button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    var ALSENDO_LOGO_BASE = '{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}alsendo/views/img/courier_logo/';
    var ALSENDO_PRESELECTED_SERVICE_ID = '{$preselected_service_id|default:""|escape:"javascript":'UTF-8'}';
    var ALSENDO_SERVICE_REQUIRES_POINT = {if $service_requires_point}true{else}false{/if};
    var ALSENDO_AUTOSELECT_COURIER = {if $autoselect_courier}true{else}false{/if};
    var ALSENDO_SAME_DAY_PICKUP = {if $alsendo_same_day_pickup}true{else}false{/if};
    var ALSENDO_CARRIER_OPERATOR = '{$carrier_operator|default:""|escape:"javascript":'UTF-8'}';
    var ALSENDO_CARRIER_SKIP_MERCHANT_PICKUP = {if $carrier_skip_merchant_pickup}true{else}false{/if};
    var ORDER_VIEW_URL = '{$order_view_url|escape:"javascript":'UTF-8'}';
    var ALSENDO_SENDER_TEMPLATES = JSON.parse('{if $sender_templates}{$sender_templates|json_encode|escape:'javascript':'UTF-8'}{else}[]{/if}');
    var ALSENDO_PACKAGE_TEMPLATES = JSON.parse('{if $package_templates}{$package_templates|json_encode|escape:'javascript':'UTF-8'}{else}[]{/if}');
    var ALSENDO_ORDER_TOTAL = {$order_total|default:0|floatval};
    var ALSENDO_IS_COD_ORDER = {if $is_cod_order}true{else}false{/if};
    var ALSENDO_AUTO_DECLARED_VALUE = {if $alsendo_auto_declared_value}true{else}false{/if};
    {if $alsendo_region == 'cz'}
    var ALSENDO_SERVICES_CAPABILITIES = JSON.parse('{$alsendo_services_capabilities_json|escape:'javascript':'UTF-8'}');
    {/if}
    console.log('Templates loaded - Sender:', ALSENDO_SENDER_TEMPLATES, 'Package:', ALSENDO_PACKAGE_TEMPLATES);

    // Handler for sender pickup point map selection
    document.addEventListener('DOMContentLoaded', function() {
        const selectBtn = document.getElementById('select-sender-pickup-point-btn');
        if (selectBtn) {
            selectBtn.addEventListener('click', function() {
                // Priority: 1) selected quote card, 2) carrier operator from PHP, 3) null (all)
                let courierOperator = ALSENDO_CARRIER_OPERATOR || null;

                // Override with selected quote service if available
                const selectedServiceId = document.getElementById('shipment-selected-service')?.value;
                if (selectedServiceId) {
                    const selectedCard = document.querySelector('.alsendo-quote-card.selected');
                    if (selectedCard) {
                        const courierName = selectedCard.dataset.courierName || '';
                        if (courierName) {
                            courierOperator = courierName;
                        }
                    }
                }

                // Open map filtered to specific operator, centered on sender city
                var senderCityInput = document.querySelector('[name="sender_city"]');
                var senderCity = (senderCityInput && senderCityInput.value) ? senderCityInput.value : null;
                if (courierOperator) {
                    openPointMap('merchant-pickup-point', [courierOperator], 'POSTING', null, senderCity);
                } else {
                    openPointMap('merchant-pickup-point', null, 'POSTING', null, senderCity);
                }
            });
        }

        // Customer pickup point handlers
        var countryCode = '{$alsendo_region|default:"pl"|upper|escape:'javascript':'UTF-8'}';

        var changeBtn = document.getElementById('change-customer-pickup-point-btn');
        var clearBtn = document.getElementById('clear-customer-pickup-point-btn');
        var selectBtn2 = document.getElementById('select-customer-pickup-point-btn');
        var hiddenPP = document.querySelector('input[name="shipment_pickup_point"]');
        var selectedDiv = document.getElementById('customer-pickup-point-selected');
        var emptyDiv = document.getElementById('customer-pickup-point-empty');

        function openCustomerMap() {
            var recipientCity = document.querySelector('[name="shipping_city"]');
            var city = (recipientCity && recipientCity.value) ? recipientCity.value : null;
            openPointMap('customer-pickup-point', null, 'DELIVERY', countryCode, city);
        }

        if (changeBtn) changeBtn.addEventListener('click', openCustomerMap);
        if (selectBtn2) selectBtn2.addEventListener('click', openCustomerMap);

        var displaySpan = document.getElementById('customer-pickup-point-display-text');

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                hiddenPP.value = '';
                if (displaySpan) displaySpan.textContent = '';
                selectedDiv.style.display = 'none';
                emptyDiv.style.display = '';
                // Cichy zapis bez modala
                var form = document.getElementById('alsendo-full-form');
                var token = form.getAttribute('data-token') || document.querySelector("input[name='token']").value;
                var silentAjaxUrl = 'index.php?controller=AdminAlsendoOrder&ajax=1&token=' + token + '&ajax_action=';
                var formData = new FormData(form);
                fetch(silentAjaxUrl + 'saveOrderDetails', {
                    method: 'POST',
                    body: new URLSearchParams(formData),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
            });
        }

        var displayInput = document.querySelector('input[type="text"][data-map-selector="customer-pickup-point"]');

        if (hiddenPP) {
            hiddenPP.addEventListener('change', function() {
                if (hiddenPP.value) {
                    if (displayInput && displaySpan) {
                        displaySpan.textContent = displayInput.value;
                    }
                    selectedDiv.style.display = '';
                    emptyDiv.style.display = 'none';
                }
            });
        }

    });
</script>

<link rel="stylesheet" href="{$alsendo_map_css_url|escape:'html':'UTF-8'}" media="screen">
<script type="text/javascript" src="{$alsendo_map_js_url|escape:'html':'UTF-8'}"></script>
<div id="alsendo-admin-map-data" style="display:none;" data-map-config="{$alsendo_map_config|escape:'html':'UTF-8'}"></div>
<script>
    (function () {
        var cfgEl = document.getElementById('alsendo-admin-map-data');
        try {
            window.ALSENDO_MAP_CONFIG = cfgEl ? JSON.parse(cfgEl.dataset.mapConfig || '{}') : {};
        } catch (e) {
            window.ALSENDO_MAP_CONFIG = {};
        }
    })();
</script>
<script src="{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}alsendo/views/js/map-widget.js?v={$smarty.now|escape:'html':'UTF-8'}"></script>
<script src="{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}alsendo/views/js/admin/alsendo-modal.js?v={$smarty.now|escape:'html':'UTF-8'}"></script>
<script>
    window.ALSENDO_MSG_VALIDATION_ERRORS = "{l s='Validation errors. Fix fields marked in red.' mod='alsendo' js=1}";
    window.ALSENDO_MSG_QUOTE_ERROR = "{l s='Error fetching quote' mod='alsendo' js=1}";
    window.ALSENDO_MSG_QUOTE_FAILED = "{l s='Quote request failed:' mod='alsendo' js=1}";
    window.ALSENDO_MSG_PICKUP_TIME_PASSED = "{l s='Pickup end time has already passed. Please select a future time or choose a later date.' mod='alsendo' js=1}";
    window.ALSENDO_MSG_PICKUP_DATE_PAST = "{l s='Pickup date cannot be in the past. Please select today or a future date.' mod='alsendo' js=1}";
    window.ALSENDO_MSG_SHIPMENT_CREATED = "{l s='Shipment has been successfully created. Redirecting...' mod='alsendo' js=1}";
    window.ALSENDO_MSG_REQUEST_FAILED = "{l s='Request failed:' mod='alsendo' js=1}";
    window.ALSENDO_MSG_SAVED = "{l s='Order details saved successfully.' mod='alsendo' js=1}";
    window.ALSENDO_MSG_SAVE_ERROR = "{l s='Error saving:' mod='alsendo' js=1}";
    window.ALSENDO_MSG_QUICK_SEND_CONFIRM = "{l s='Send this order with default settings?' mod='alsendo' js=1}";
    window.ALSENDO_MSG_SENT_OK = "{l s='Shipment sent successfully!' mod='alsendo' js=1}";
    window.ALSENDO_MSG_QUICK_SEND_FAILED = "{l s='Quick send failed:' mod='alsendo' js=1}";
    window.ALSENDO_MSG_CANCEL_CONFIRM = "{l s='Cancel this shipment?' mod='alsendo' js=1}";
    window.ALSENDO_MSG_CANCEL_FAILED = "{l s='Cancellation failed' mod='alsendo' js=1}";
    window.ALSENDO_MSG_NO_SERVICES = "{l s='No services available for this configuration.' mod='alsendo' js=1}";
    window.ALSENDO_MSG_SENDING = "{l s='Sending...' mod='alsendo' js=1}";
    window.ALSENDO_MSG_PICKUP_TIME_PASSED_SHORT = "{l s='Pickup end time has already passed' mod='alsendo' js=1}";
    window.ALSENDO_MSG_DATE_PAST_SHORT = "{l s='Date cannot be in the past' mod='alsendo' js=1}";
    window.ALSENDO_MSG_DETAILED_ERRORS = "{l s='Detailed errors:' mod='alsendo' js=1}";
</script>
<script src="{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}alsendo/views/js/admin/alsendo-shipment.js?v={$smarty.now|escape:'html':'UTF-8'}"></script>

<style>
    .alsendo-quote-grid { display: flex; flex-wrap: wrap; gap: 12px; }
    .alsendo-quote-card { width: 200px; border: 1px solid #e0e0e0; border-radius: 6px; padding: 12px; background:#fff; cursor: pointer; text-align: center; }
    .alsendo-quote-card.selected { border-color: #1e91cf; box-shadow: 0 0 0 2px rgba(30,145,207,.18); }
    .alsendo-quote-card.fixed { cursor: default; pointer-events: none; }
    .alsendo-quote-logo { height: 40px; width: auto; margin-bottom: 8px; display: block; margin-left: auto; margin-right: auto; }
    .alsendo-quote-name { font-weight: 600; margin-bottom: 4px; font-size: 13px; }
    .alsendo-quote-price { color: #333; font-weight: 500; }
    .alsendo-quote-price small { color: #777; }
    .alsendo-quote-card.alsendo-quote-mapped { border: 2px solid #28a745; position: relative; }
    .alsendo-quote-card.alsendo-quote-mapped.selected { border-color: #28a745; box-shadow: 0 0 0 2px rgba(40,167,69,.18); }
    .alsendo-quote-badge { position: absolute; top: 4px; right: 4px; font-size: 9px; background: #28a745; color: #fff; padding: 1px 6px; border-radius: 3px; text-transform: uppercase; }
    .input-validation-error { background-color: #fae4e4; border-color: #d9534f !important; }
    .validation-error-message { color: #a94442; display:block; margin-top: 4px; font-size: 12px; }

</style>