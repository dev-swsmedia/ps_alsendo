<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'alsendo/src/Services/PickupHoursService.php';
require_once _PS_MODULE_DIR_ . 'alsendo/src/Services/MapBridge.php';
require_once _PS_MODULE_DIR_ . 'alsendo/src/Services/AddressParser.php';
require_once _PS_MODULE_DIR_ . 'alsendo/src/Services/OrderDetailsService.php';
require_once _PS_MODULE_DIR_ . 'alsendo/controllers/admin/AdminAlsendoModuleConfigurationController.php';

use Alsendo\DTO\FullOrderDTO;
use Alsendo\DTO\OrderShipmentSubmitDTO;
use Alsendo\Services\AddressParser;
use Alsendo\Services\MapBridge;
use Alsendo\Services\OrderDetailsService;
use Alsendo\Services\OrderValidator;
use Alsendo\Services\PickupHoursService;
use Alsendo\Services\WrapperService;

class AdminAlsendoOrderController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initContent()
    {
        $ajaxAction = Tools::getValue('ajax_action');

        if (Tools::getIsset('ajax')) {
            $this->handleAjax();

            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $ajaxAction === 'submitOrderShipment') {
            $this->handleDirectPostSubmission();

            return;
        }

        $idOrder = (int) Tools::getValue('id_order');
        $order = new Order($idOrder);
        $orderTotal = (float) $order->total_paid_tax_incl;
        $isCodOrder = in_array($order->module, ['ps_cashondelivery', 'cashondelivery'], true);
        $address = new Address($order->id_address_delivery);
        $customer = new Customer($order->id_customer);

        $shipment = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'alsendo_order_shipment` WHERE id_order=' . (int) $idOrder
        );

        $shipmentData = [];
        if ($shipment) {
            $shipmentData = [
                'status' => $shipment['status'] ?? '',
                'waybill_number' => $shipment['waybill_number'] ?? '',
                'shipping_method' => $shipment['shipping_method'] ?? '',
                'courier_service' => $shipment['courier_service'] ?? '',
                'price' => $shipment['price'] ?? '',
                'tracking_url' => $shipment['tracking_url'] ?? '',
            ];
            $jsonData = json_decode($shipment['data'] ?? '{}', true);
            if (is_array($jsonData)) {
                $shipmentData = array_merge($shipmentData, $jsonData);
            }
        }

        $savedOrderDetails = [];
        $orderDetailsRow = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'alsendo_order_details` WHERE id_order=' . (int) $idOrder
        );
        if ($orderDetailsRow) {
            $savedOrderDetails = json_decode($orderDetailsRow['data'], true) ?: [];
        }

        if (!$shipmentData || empty($shipmentData['status'] ?? '')) {
            if (!empty($savedOrderDetails['shipment'])) {
                $shipmentData = array_merge($shipmentData, $savedOrderDetails['shipment']);
            }
        }

        $region = Configuration::get('ALSENDO_REGION') ?: 'pl';
        $regionCountryMap = ['pl' => 'PL', 'cz' => 'CZ', 'ro' => 'RO'];
        $regionCountry = $regionCountryMap[$region] ?? 'PL';

        $defaultData = [
            'data' => [
                'sender' => [
                    'template_name' => '',
                    'full_name' => '',
                    'first_name' => '',
                    'last_name' => '',
                    'company_name' => '',
                    'email' => '',
                    'street' => '',
                    'building_number' => '',
                    'apartment_number' => '',
                    'block' => '',
                    'entrance' => '',
                    'floor' => '',
                    'flat' => '',
                    'postal_code' => '',
                    'city' => '',
                    'country' => $regionCountry,
                    'phone_number' => '',
                    'address_type' => 'company',
                    'contact_person' => '',
                    'bank_account_number' => '',
                ],
                'package' => [
                    'template_name' => '',
                    'width' => '',
                    'length' => '',
                    'height' => '',
                    'weight' => '',
                    'package_content' => '',
                    'cod_value' => '',
                    'declared_value' => '',
                    'pickup_type' => OrderDetailsService::getConfiguredPickupType($region),
                    'package_type' => ($region === 'cz' ? 'PACKAGE' : ($region === 'ro' ? 'package' : 'PACZKA')),
                    'shipment_packaging' => ($region === 'cz' ? 'PACKAGE' : ($region === 'ro' ? 'package' : 'PACZKA')),
                    'shipment_type' => ($region === 'cz' ? 'PACKAGE' : ($region === 'ro' ? 'package' : 'PACZKA')),
                    'is_nstd' => 0,
                ],
                'shipment' => [
                    'preferred_pickup_date' => '',
                    'preferred_pickup_hours_from' => Configuration::get('ALSENDO_DEFAULT_PICKUP_HOURS_FROM', null, null, null, '08:00'),
                    'preferred_pickup_hours_to' => Configuration::get('ALSENDO_DEFAULT_PICKUP_HOURS_TO', null, null, null, '17:00'),
                    'shipment_pickup_point' => '',
                    'shipment_pickup_point_display' => '',
                ],
                'recipient' => [
                    'phone' => '',
                    'email' => '',
                ],
            ],
        ];

        $alsendoOrderDetails = $defaultData;
        if (!empty($savedOrderDetails)) {
            $alsendoOrderDetails = array_replace_recursive($alsendoOrderDetails, ['data' => $savedOrderDetails]);
        }
        if ($shipmentData) {
            $alsendoOrderDetails = array_replace_recursive($alsendoOrderDetails, $shipmentData);
        }

        if (!empty($alsendoOrderDetails['data']['package']['pickup_type'])) {
            $alsendoOrderDetails['data']['package']['pickup_type'] = str_replace('_', '', strtoupper($alsendoOrderDetails['data']['package']['pickup_type']));
        }

        $countries = [];
        $countriesObj = Country::getCountries($this->context->language->id, true);
        foreach ($countriesObj as $country) {
            $countries[$country['iso_code']] = $country['name'];
        }

        $orderInfo = (array) $order;
        $savedRecipient = $savedOrderDetails['recipient'] ?? [];
        $orderInfo['firstname'] = !empty($savedRecipient['first_name']) ? $savedRecipient['first_name'] : $address->firstname;
        $orderInfo['lastname'] = !empty($savedRecipient['last_name']) ? $savedRecipient['last_name'] : $address->lastname;
        $orderInfo['postcode'] = !empty($savedRecipient['postal_code']) ? $savedRecipient['postal_code'] : $address->postcode;
        $orderInfo['city'] = !empty($savedRecipient['city']) ? $savedRecipient['city'] : $address->city;
        $orderInfo['iso_code'] = !empty($savedRecipient['country']) ? $savedRecipient['country'] : Country::getIsoById($address->id_country);

        if (!empty($savedRecipient['street'])) {
            $orderInfo['street'] = $savedRecipient['street'];
            $orderInfo['building_number'] = $savedRecipient['building_number'] ?? '';
            $orderInfo['apartment_number'] = $savedRecipient['apartment_number'] ?? '';
            $orderInfo['shipping_block'] = $savedRecipient['block'] ?? '';
            $orderInfo['shipping_entrance'] = $savedRecipient['entrance'] ?? '';
            $orderInfo['shipping_floor'] = $savedRecipient['floor'] ?? '';
            $orderInfo['shipping_flat'] = $savedRecipient['flat'] ?? '';
        } elseif (!empty($savedRecipient['address'])) {
            $parsed = AddressParser::parseAddressToComponents($savedRecipient['address'], $savedRecipient['address2'] ?? '');
            $orderInfo['street'] = $parsed['street'];
            $orderInfo['building_number'] = $parsed['building_number'];
            $orderInfo['apartment_number'] = $parsed['apartment_number'];
            if ($regionCountry === 'RO') {
                $roSource = $savedRecipient['address2'] ?? '';
                if (empty($roSource)) {
                    $roSource = $parsed['apartment_number'];
                }
                $roComponents = AddressParser::parseRomanianComponents($roSource);
                $orderInfo['shipping_block'] = $roComponents['block'];
                $orderInfo['shipping_entrance'] = $roComponents['entrance'];
                $orderInfo['shipping_floor'] = $roComponents['floor'];
                $orderInfo['shipping_flat'] = $roComponents['flat'];
                $orderInfo['apartment_number'] = '';
            }
        } else {
            $parsed = AddressParser::parseAddressToComponents($address->address1, $address->address2);
            $orderInfo['street'] = $parsed['street'];
            $orderInfo['building_number'] = $parsed['building_number'];
            $orderInfo['apartment_number'] = $parsed['apartment_number'];
            if ($regionCountry === 'RO') {
                $roSource = $address->address2;
                if (empty($roSource)) {
                    $roSource = $parsed['apartment_number'];
                }
                $roComponents = AddressParser::parseRomanianComponents($roSource);
                $orderInfo['shipping_block'] = $roComponents['block'];
                $orderInfo['shipping_entrance'] = $roComponents['entrance'];
                $orderInfo['shipping_floor'] = $roComponents['floor'];
                $orderInfo['shipping_flat'] = $roComponents['flat'];
                $orderInfo['apartment_number'] = '';
            }
        }

        $carrier = new Carrier($order->id_carrier);
        $orderInfo['carrier_name'] = $carrier->name;
        $carrierSkipMerchantPickup = WrapperService::isCarrierWithoutMerchantPickup($carrier->name);

        $pkg = &$alsendoOrderDetails['data']['package'];
        $sender = &$alsendoOrderDetails['data']['sender'];

        foreach (['template_name', 'package_type', 'width', 'length', 'height', 'weight', 'package_content', 'cod_value', 'declared_value', 'pickup_type', 'shipment_packaging', 'shipment_type', 'is_nstd'] as $key) {
            if (!array_key_exists($key, $pkg)) {
                $pkg[$key] = '';
            }
        }
        foreach (['template_name', 'full_name', 'first_name', 'last_name', 'company_name', 'email', 'street', 'building_number', 'apartment_number', 'block', 'entrance', 'floor', 'flat', 'postal_code', 'city', 'country', 'phone_number', 'address_type', 'contact_person', 'bank_account_number'] as $key) {
            if (!array_key_exists($key, $sender)) {
                $sender[$key] = '';
            }
        }

        $senderData = $alsendoOrderDetails['data']['sender'] ?? [];
        $packageData = $alsendoOrderDetails['data']['package'] ?? [];
        $shipmentData = $alsendoOrderDetails['data']['shipment'] ?? [];

        $composedAddress = trim($orderInfo['street']
            . (!empty($orderInfo['building_number']) ? ' ' . $orderInfo['building_number'] : '')
            . (!empty($orderInfo['apartment_number']) ? '/' . $orderInfo['apartment_number'] : ''));
        $shippingAddress = [
            'first_name' => $address->firstname,
            'last_name' => $address->lastname,
            'company' => $address->company,
            'address' => $composedAddress,
            'address2' => '',
            'town' => $address->city,
            'postal_code' => $address->postcode,
            'country' => Country::getIsoById($address->id_country),
        ];

        $alsendo_sender = array_merge([
            'template_name' => '',
            'first_name' => '',
            'last_name' => '',
            'company_name' => '',
            'street' => '',
            'building_number' => '',
            'apartment_number' => '',
            'block' => '',
            'entrance' => '',
            'floor' => '',
            'flat' => '',
            'postal_code' => '',
            'city' => '',
            'country' => $regionCountry,
            'phone_number' => '',
            'email' => '',
            'address_type' => 'company',
            'contact_person' => '',
            'bank_account_number' => '',
            'bank_code' => '',
            'additional_bank_account_number' => '',
            'external_id' => '',
        ], $senderData);

        $defaultPkgType = ($region === 'cz' ? 'PACKAGE' : ($region === 'ro' ? 'package' : 'PACZKA'));
        $alsendo_package = array_merge([
            'template_name' => '',
            'width' => '',
            'length' => '',
            'height' => '',
            'weight' => '',
            'package_type' => $defaultPkgType,
            'shipment_packaging' => $defaultPkgType,
            'shipment_type' => $defaultPkgType,
            'is_nstd' => 0,
            'package_content' => '',
            'cod_value' => '',
            'declared_value' => '',
            'pickup_type' => '',
        ], $packageData);

        $alsendo_shipping = array_merge([
            'shipping_method' => '',
            'courier_service' => '',
            'price' => '',
            'waybill_number' => '',
            'status' => '',
            'pickup_point' => $shipmentData['shipment_pickup_point'] ?? '',
        ], $shipmentData);

        $senderTemplatesRaw = json_decode(Configuration::get('ALSENDO_SENDER_LIST', null, null, null, ''), true) ?: [];
        $packageTemplatesRaw = json_decode(Configuration::get('ALSENDO_SHIPPING_SETTINGS_LIST', null, null, null, ''), true) ?: [];

        $senderTemplates = array_values($senderTemplatesRaw);

        $packageTemplates = [];
        foreach ($packageTemplatesRaw as $tpl) {
            $packageTemplates[] = [
                'name' => $tpl['alsendo_template_name'] ?? '',
                'package_type' => $tpl['alsendo_package_type'] ?? $defaultPkgType,
                'is_nstd' => (int) ($tpl['alsendo_is_nstd'] ?? 0),
                'width' => (float) ($tpl['alsendo_width'] ?? 0),
                'length' => (float) ($tpl['alsendo_length'] ?? 0),
                'height' => (float) ($tpl['alsendo_height'] ?? 0),
                'weight' => (float) ($tpl['alsendo_weight'] ?? 0),
                'cod_value' => (float) ($tpl['alsendo_cod'] ?? 0),
                'declared_value' => (float) ($tpl['alsendo_declared_value'] ?? 0),
                'package_content' => $tpl['alsendo_shipment_content'] ?? '',
                'pickup_type' => str_replace('_', '', strtoupper($tpl['alsendo_pickup_type'] ?? ($region === 'cz' ? 'ONDEMAND' : 'COURIER'))),
                'main' => !empty($tpl['main']),
            ];
        }

        list($preselectedServiceId, $serviceRequiresPoint) = $this->getCarrierMapping((int) $order->id_carrier);

        $region = (Configuration::get('ALSENDO_REGION') ?: 'pl');
        $mapBridge = new MapBridge();
        $carrierOperator = null;
        if ($preselectedServiceId) {
            $servicesCfg = json_decode(Configuration::get('ALSENDO_AVAILABLE_SERVICES', null, null, null, ''), true) ?: [];
            foreach ($servicesCfg as $svc) {
                if (!empty($svc['service_id']) && (string) $svc['service_id'] === (string) $preselectedServiceId) {
                    $carrierOperator = $mapBridge->resolveMapOperator($region, $svc['supplier'] ?? '', $svc['name'] ?? '');
                    break;
                }
            }
        }

        if (empty($alsendo_package['pickup_type'])) {
            $hasCheckoutPickupPoint = false;
            if (!empty($alsendoOrderDetails['data']['shipment']['shipment_pickup_point'])) {
                $hasCheckoutPickupPoint = true;
            } elseif (!empty($alsendoOrderDetails['data']['recipient']['pickup_point'])) {
                $hasCheckoutPickupPoint = true;
            } elseif ((int) $order->id_cart > 0) {
                $ppCheck = Db::getInstance()->getValue(
                    'SELECT pickup_point FROM `' . _DB_PREFIX_ . 'alsendo_order_pickup` WHERE id_cart=' . (int) $order->id_cart
                );
                if (!empty($ppCheck)) {
                    $hasCheckoutPickupPoint = true;
                }
            }
            if ($region === 'cz') {
                $alsendo_package['pickup_type'] = OrderDetailsService::getConfiguredPickupType($region);
            } else {
                $alsendo_package['pickup_type'] = $hasCheckoutPickupPoint ? 'SELF' : OrderDetailsService::getConfiguredPickupType($region);
            }
        }

        if (empty($alsendoOrderDetails['data']['shipment']['shipment_pickup_point'])) {
            $pickupPointFromRecipient = $alsendoOrderDetails['data']['recipient']['pickup_point'] ?? null;
            if (!empty($pickupPointFromRecipient)) {
                $alsendoOrderDetails['data']['shipment']['shipment_pickup_point'] = $pickupPointFromRecipient;
                $code = $pickupPointFromRecipient['code'] ?? '';
                $name = $pickupPointFromRecipient['description'] ?? ($pickupPointFromRecipient['street'] ?? '');
                $alsendoOrderDetails['data']['shipment']['shipment_pickup_point_display'] = trim($code . ' - ' . $name, ' -');
            } else {
                $pp = null;
                if ((int) $order->id_cart > 0) {
                    $pp = Db::getInstance()->getValue(
                        'SELECT pickup_point FROM `' . _DB_PREFIX_ . 'alsendo_order_pickup` WHERE id_cart=' . (int) $order->id_cart
                    );
                }
                if ($pp) {
                    $ppArr = json_decode($pp, true);
                    if (is_array($ppArr)) {
                        $alsendoOrderDetails['data']['shipment']['shipment_pickup_point'] = $ppArr;
                        $code = $ppArr['code'] ?? '';
                        $name = $ppArr['description'] ?? ($ppArr['street'] ?? '');
                        $alsendoOrderDetails['data']['shipment']['shipment_pickup_point_display'] = trim($code . ' - ' . $name, ' -');
                    }
                }
            }
        }

        $defaultSenderTpl = null;
        foreach ($senderTemplates as $tpl) {
            if (!empty($tpl['main'])) {
                $defaultSenderTpl = $tpl;
                break;
            }
        }
        if (!$defaultSenderTpl && !empty($senderTemplates)) {
            $defaultSenderTpl = $senderTemplates[0];
        }
        $defaultPackageTpl = null;
        foreach ($packageTemplates as $ptpl) {
            if (!empty($ptpl['main'])) {
                $defaultPackageTpl = $ptpl;
                break;
            }
        }
        if (!$defaultPackageTpl && !empty($packageTemplates)) {
            $defaultPackageTpl = $packageTemplates[0];
        }

        $shouldApplySender = empty($alsendo_sender['full_name']) && empty($alsendo_sender['street']) && empty($alsendo_sender['postal_code']);
        if ($defaultSenderTpl && $shouldApplySender) {
            $alsendo_sender['company_name'] = $defaultSenderTpl['company'] ?? '';
            $alsendo_sender['first_name'] = $defaultSenderTpl['firstname'] ?? '';
            $alsendo_sender['last_name'] = $defaultSenderTpl['lastname'] ?? '';
            $alsendo_sender['full_name'] = trim(($defaultSenderTpl['firstname'] ?? '') . ' ' . ($defaultSenderTpl['lastname'] ?? ''));
            $alsendo_sender['street'] = trim(($defaultSenderTpl['street'] ?? '') . ' ' . ($defaultSenderTpl['building'] ?? ''));
            $alsendo_sender['building_number'] = $defaultSenderTpl['building'] ?? '';
            $alsendo_sender['apartment_number'] = $defaultSenderTpl['apartment'] ?? '';
            $alsendo_sender['block'] = $defaultSenderTpl['block'] ?? '';
            $alsendo_sender['entrance'] = $defaultSenderTpl['entrance'] ?? '';
            $alsendo_sender['floor'] = $defaultSenderTpl['floor'] ?? '';
            $alsendo_sender['flat'] = $defaultSenderTpl['flat'] ?? '';
            $alsendo_sender['postal_code'] = $defaultSenderTpl['postal'] ?? '';
            $alsendo_sender['city'] = $defaultSenderTpl['city'] ?? '';
            $alsendo_sender['country'] = $regionCountry;
            $alsendo_sender['phone_number'] = $defaultSenderTpl['phone'] ?? '';
            $alsendo_sender['email'] = $defaultSenderTpl['email'] ?? '';
            $alsendo_sender['template_name'] = $defaultSenderTpl['template_name'] ?? '';
            $alsendo_sender['address_type'] = $defaultSenderTpl['address_type'] ?? 'company';
            $alsendo_sender['contact_person'] = $defaultSenderTpl['contact'] ?? '';
            $alsendo_sender['bank_account_number'] = $defaultSenderTpl['bank'] ?? '';
            $alsendo_sender['bank_code'] = $defaultSenderTpl['bank_code'] ?? '';
            $alsendo_sender['additional_bank_account_number'] = $defaultSenderTpl['additional_bank_account_number'] ?? '';
            $alsendo_sender['external_id'] = $defaultSenderTpl['external_id'] ?? '';
        }

        if ($defaultSenderTpl) {
            $senderFallbackMap = [
                'block' => 'block',
                'entrance' => 'entrance',
                'floor' => 'floor',
                'flat' => 'flat',
                'contact_person' => 'contact',
                'bank_account_number' => 'bank',
                'bank_code' => 'bank_code',
                'additional_bank_account_number' => 'additional_bank_account_number',
                'external_id' => 'external_id',
            ];
            foreach ($senderFallbackMap as $senderKey => $tplKey) {
                if (empty($alsendo_sender[$senderKey]) && !empty($defaultSenderTpl[$tplKey])) {
                    $alsendo_sender[$senderKey] = $defaultSenderTpl[$tplKey];
                }
            }
        }

        $shouldApplyPackage = (empty($alsendo_package['width']) && empty($alsendo_package['weight']));
        if ($defaultPackageTpl && $shouldApplyPackage) {
            $alsendo_package['template_name'] = $defaultPackageTpl['name'];
            $alsendo_package['package_type'] = $defaultPackageTpl['package_type'] ?: $defaultPkgType;
            $alsendo_package['shipment_packaging'] = $defaultPackageTpl['package_type'] ?: $defaultPkgType;
            $alsendo_package['shipment_type'] = $defaultPackageTpl['package_type'] ?: $defaultPkgType;
            $alsendo_package['is_nstd'] = (int) $defaultPackageTpl['is_nstd'];
            $alsendo_package['width'] = $defaultPackageTpl['width'];
            $alsendo_package['length'] = $defaultPackageTpl['length'];
            $alsendo_package['height'] = $defaultPackageTpl['height'];
            $alsendo_package['weight'] = $defaultPackageTpl['weight'];
            $alsendo_package['package_content'] = $defaultPackageTpl['package_content'];
            $alsendo_package['cod_value'] = $defaultPackageTpl['cod_value'];
            $alsendo_package['declared_value'] = $defaultPackageTpl['declared_value'];
            $alsendo_package['pickup_type'] = $defaultPackageTpl['pickup_type'] ?: OrderDetailsService::getConfiguredPickupType($region);
        }

        $autoDeclaredValue = (bool) Configuration::get('ALSENDO_AUTO_DECLARED_VALUE');
        if ($autoDeclaredValue) {
            $templateDeclVal = (float) ($alsendo_package['declared_value'] ?? 0);
            $alsendo_package['declared_value'] = ($templateDeclVal > $orderTotal)
                ? $templateDeclVal
                : $orderTotal;
        } else {
            if (empty($alsendo_package['declared_value'])) {
                $alsendo_package['declared_value'] = 0;
            }
        }
        if ($isCodOrder && (empty($alsendo_package['cod_value']) || (float) $alsendo_package['cod_value'] == 0)) {
            $alsendo_package['cod_value'] = $orderTotal;
        }

        if (empty($alsendoOrderDetails['data']['recipient']['phone'])) {
            $alsendoOrderDetails['data']['recipient']['phone'] = $address->phone_mobile ?: $address->phone ?: '';
        }
        if (empty($alsendoOrderDetails['data']['recipient']['email'])) {
            $alsendoOrderDetails['data']['recipient']['email'] = $customer->email ?: '';
        }

        $carrier = new Carrier($order->id_carrier);
        $carrierName = strtolower($carrier->name);

        $resolvedOperator = $mapBridge->resolveMapOperator($region, $carrierName, $carrierName);
        $pickupKey = null;
        if ($resolvedOperator) {
            $opMap = $mapBridge->getOperatorMapForRegion($region);
            foreach ($opMap as $alias => $bpOp) {
                if ($bpOp === $resolvedOperator) {
                    $pickupKey = $mapBridge->getPickupConfigKey($alias);
                    break;
                }
            }
        }

        if ($pickupKey) {
            $defaultPickup = Configuration::get($pickupKey, null, null, null, '');

            if ($defaultPickup) {
                $defaultPickup = stripslashes($defaultPickup);
                $ppArr = json_decode($defaultPickup, true);

                if (is_array($ppArr) && !empty($ppArr['code'])) {
                    $alsendoOrderDetails['data']['shipment']['merchant_pickup_point_json'] = json_encode($ppArr);
                    $code = $ppArr['code'];
                    $name = $ppArr['description'] ?? ($ppArr['name'] ?? ($ppArr['street'] ?? ''));
                    $alsendoOrderDetails['data']['shipment']['merchant_pickup_point_display'] = trim($code . ' - ' . $name, ' -');
                }
            }
        }

        $alsendoOrderDetails['data']['sender'] = $alsendo_sender;
        $alsendoOrderDetails['data']['package'] = $alsendo_package;

        if (isset($alsendoOrderDetails['data']['shipment']['shipment_pickup_point'])) {
            $customerPP = $alsendoOrderDetails['data']['shipment']['shipment_pickup_point'];
            if (is_array($customerPP)) {
                $alsendoOrderDetails['data']['shipment']['customer_pickup_point_json'] = json_encode($customerPP);
                $code = $customerPP['code'] ?? '';
                $name = $customerPP['description'] ?? ($customerPP['name'] ?? ($customerPP['street'] ?? ''));
                $alsendoOrderDetails['data']['shipment']['customer_pickup_point_display'] = trim($code . ' - ' . $name, ' -');
            } elseif (is_string($customerPP)) {
                $alsendoOrderDetails['data']['shipment']['customer_pickup_point_json'] = $customerPP;
                $ppArr = json_decode($customerPP, true);
                if (is_array($ppArr)) {
                    $code = $ppArr['code'] ?? '';
                    $name = $ppArr['description'] ?? ($ppArr['name'] ?? ($ppArr['street'] ?? ''));
                    $alsendoOrderDetails['data']['shipment']['customer_pickup_point_display'] = trim($code . ' - ' . $name, ' -');
                }
            }
        }

        $autoselectCourier = ($preselectedServiceId !== null && $preselectedServiceId !== 0 && $preselectedServiceId !== '');

        $region = (Configuration::get('ALSENDO_REGION') ?: 'pl');

        $pickupTypes = [];
        if ($region === 'cz') {
            $pickupTypes = ['OCCASIONAL', 'ONDEMAND'];
        } elseif ($region === 'ro') {
            $pickupTypes = ['COURIER', 'SELF'];
        } else {
            $pickupTypes = ['COURIER', 'SELF', 'NO_PICKUP'];
        }
        $pickupTypeLabels = [
            'SELF' => $this->module->l('Deliver to point', 'AdminAlsendoOrderController'),
            'COURIER' => $this->module->l('Courier pickup', 'AdminAlsendoOrderController'),
            'NO_PICKUP' => $this->module->l('No pickup', 'AdminAlsendoOrderController'),
            'OCCASIONAL' => $this->module->l('Label provided by courier', 'AdminAlsendoOrderController'),
            'ONDEMAND' => $this->module->l('Self-printed label (drop-off)', 'AdminAlsendoOrderController'),
        ];

        $servicesCapabilitiesJson = '[]';
        if ($region === 'cz') {
            $servicesData = Configuration::get('ALSENDO_AVAILABLE_SERVICES');
            if ($servicesData) {
                $servicesCapabilitiesJson = $servicesData;
            }
        }

        $addressBook = [];
        $senderExternalIdPreselect = $defaultSenderTpl['external_id'] ?? '';
        if ($region === 'cz') {
            try {
                $wrapperService = new WrapperService();
                $result = $wrapperService->getAddressBookList();
                if ($result->isSuccess()) {
                    $addressBook = $result->getData()['addresses'] ?? [];
                }
            } catch (Throwable $e) {
            }
        }

        $shippingMethodShipVia = 0;
        $shippingMethodPickupRequest = 0;
        if ($region === 'cz' && (int) $order->id_carrier > 0) {
            $smRow = Db::getInstance()->getRow(
                'SELECT ship_via_pickup_point, pickup_request FROM `' . _DB_PREFIX_ . 'alsendo_shipping_methods`
                 WHERE id_carrier = ' . (int) $order->id_carrier . ' AND id_shop = ' . (int) $this->context->shop->id
            );
            if ($smRow) {
                $shippingMethodShipVia = (int) $smRow['ship_via_pickup_point'];
                $shippingMethodPickupRequest = (int) $smRow['pickup_request'];
            }
        }

        $availableTags = [
            'order_id' => 'Order ID',
            'product_id' => 'Product ID',
            'product_name' => 'Product Name',
            'invoice_number' => 'Invoice Number',
            'custom_text' => 'Custom text',
        ];

        $orderViewUrl = '';
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        if (defined('_PS_VERSION_') && version_compare(_PS_VERSION_, '1.7.0', '>=') && strpos($referer, '/index.php/') !== false) {
            try {
                $token = '';
                if (preg_match('/[?&]_token=([^&]+)/', $referer, $matches)) {
                    $token = $matches[1];
                }

                if ($token) {
                    $adminDir = str_replace(_PS_ROOT_DIR_ . '/', '', _PS_ADMIN_DIR_);
                    $baseUrl = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . $adminDir . '/';

                    $orderViewUrl = $baseUrl . 'index.php/sell/orders/' . (int) $idOrder . '/view?_token=' . $token;
                }
            } catch (Exception $e) {
            }
        }

        if (empty($orderViewUrl)) {
            $orderViewUrl = $this->context->link->getAdminLink('AdminOrders', true) . '&id_order=' . (int) $idOrder . '&vieworder';
        }

        $mapData = $mapBridge->getMapTemplateDataSafe($region);

        $currencyMap = ['pl' => 'PLN', 'cz' => 'CZK', 'ro' => 'RON'];
        $alsendoCurrency = $currencyMap[Configuration::get('ALSENDO_REGION') ?: 'pl'] ?? 'PLN';

        $this->context->smarty->assign([
            'order_id' => $idOrder,
            'order_info' => $orderInfo,
            'order_total' => $orderTotal,
            'is_cod_order' => $isCodOrder,
            'alsendo_currency' => $alsendoCurrency,
            'alsendo_order_details' => $alsendoOrderDetails,
            'alsendo_sender' => $alsendo_sender,
            'alsendo_package' => $alsendo_package,
            'alsendo_shipping' => $alsendo_shipping,
            'alsendo_shipping_address' => $shippingAddress,
            'user_token' => Tools::getAdminTokenLite('AdminAlsendoOrder'),
            'countries' => $countries,
            'sender_templates' => $senderTemplates,
            'package_templates' => $packageTemplates,
            'module_dir' => _MODULE_DIR_ . 'alsendo/',
            'preselected_service_id' => $preselectedServiceId,
            'service_requires_point' => $serviceRequiresPoint,
            'pickup_types' => $pickupTypes,
            'pickup_type_labels' => $pickupTypeLabels,
            'available_tags' => $availableTags,
            'alsendo_region' => $region,
            'order_view_url' => $orderViewUrl,
            'autoselect_courier' => $autoselectCourier,
            'carrier_operator' => $carrierOperator,
            'alsendo_map_css_url' => $mapData['css_url'],
            'alsendo_map_js_url' => $mapData['js_url'],
            'alsendo_map_modal_container_id' => $mapData['container_id'],
            'alsendo_map_config' => $mapData['config_json'],
            'alsendo_auto_declared_value' => (bool) Configuration::get('ALSENDO_AUTO_DECLARED_VALUE'),
            'alsendo_same_day_pickup' => (bool) Configuration::get('ALSENDO_SAME_DAY_PICKUP'),
            'alsendo_package_types' => AdminAlsendoModuleConfigurationController::getPackageTypesForRegion(Configuration::get('ALSENDO_REGION') ?: 'pl'),
            'alsendo_services_capabilities_json' => $servicesCapabilitiesJson,
            'alsendo_address_book' => $addressBook,
            'sender_address_external_id_preselect' => $senderExternalIdPreselect,
            'shipping_method_ship_via' => $shippingMethodShipVia,
            'shipping_method_pickup_request' => $shippingMethodPickupRequest,
            'carrier_skip_merchant_pickup' => $carrierSkipMerchantPickup,
        ]);

        $this->setTemplate('order_full_form.tpl');
    }

    public function handleAjax()
    {
        $action = Tools::getValue('ajax_action');

        switch ($action) {
            case 'getQuote':
                $dto = OrderShipmentSubmitDTO::fromRequest($_POST);
                if (empty($dto->sender_country)) {
                    $regionCountryMap = ['pl' => 'PL', 'cz' => 'CZ', 'ro' => 'RO'];
                    $dto->sender_country = $regionCountryMap[Configuration::get('ALSENDO_REGION') ?: 'pl'] ?? 'PL';
                }
                $order = new Order((int) $dto->order_id);

                $idCarrier = (int) Tools::getValue('id_carrier');
                if ($idCarrier) {
                    $order->id_carrier = $idCarrier;
                }

                $dto->package_content = $this->resolvePackageContent(
                    $dto->package_content,
                    (int) $dto->order_id
                );

                $validator = new OrderValidator();
                $errors = $validator->validateShipmentData($dto);

                unset($errors['shipment_selected_service']);
                unset($errors['shipment_pickup_point']);
                unset($errors['shipment_preferred_pickup_date']);
                unset($errors['shipment_preferred_pickup_hours_from']);
                unset($errors['shipment_preferred_pickup_hours_to']);

                if (!empty($errors)) {
                    $errors = $this->translateValidationErrors($errors);
                    $this->jsonResponse(['success' => false, 'errors' => $errors]);
                    break;
                }

                list($mappedServiceId, $hasMap) = $this->getCarrierMapping((int) $order->id_carrier);
                if (empty($dto->selected_pickup_type)) {
                    $region = (Configuration::get('ALSENDO_REGION') ?: 'pl');
                    if ($region === 'cz') {
                        $dto->selected_pickup_type = OrderDetailsService::getConfiguredPickupType($region);
                    } else {
                        $dto->selected_pickup_type = $hasMap ? 'SELF' : 'COURIER';
                    }
                }

                $pickupPoint = null;
                if ((int) $order->id_cart > 0) {
                    $pickupPoint = Db::getInstance()->getValue(
                        'SELECT pickup_point FROM ' . _DB_PREFIX_ . 'alsendo_order_pickup WHERE id_cart=' . (int) $order->id_cart
                    );
                }
                $pickupPoint = $pickupPoint ? json_decode($pickupPoint, true) : null;

                $savedDetails = Db::getInstance()->getRow(
                    'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_details` WHERE id_order=' . (int) $dto->order_id
                );
                $recipientOverride = null;
                if ($savedDetails) {
                    $savedData = json_decode($savedDetails['data'], true);
                    $recipientOverride = $savedData['recipient'] ?? null;
                }

                $region = (Configuration::get('ALSENDO_REGION') ?: 'pl');
                if ($region === 'cz' && empty($dto->shipment_selected_service) && $mappedServiceId) {
                    $dto->shipment_selected_service = $mappedServiceId;
                }

                $fullDto = FullOrderDTO::fromPrestaOrder($order, $dto, $pickupPoint, $recipientOverride);
                $w = new WrapperService();
                $res = $w->getOrderValuation($fullDto);

                if (!$res->isSuccess()) {
                    $this->jsonResponse(['success' => false, 'error' => $res->getError() ?: $res->getMessage()]);
                    break;
                }

                $payload = $res->getData();

                $valuation = json_decode(json_encode($payload), true);

                $priceTable = [];
                foreach ($valuation as $sid => $service) {
                    if (isset($service['priceTable'])) {
                        $priceTable[$sid] = $service['priceTable'];
                    }
                }
                $servicesCfg = json_decode(Configuration::get('ALSENDO_AVAILABLE_SERVICES', null, null, null, ''), true) ?: [];
                $svcIndex = [];
                foreach ($servicesCfg as $svc) {
                    if (!empty($svc['service_id'])) {
                        $svcIndex[(string) $svc['service_id']] = $svc;
                    }
                }

                $logoBase = _MODULE_DIR_ . 'alsendo/views/img/courier_logo/';
                $aliasMap = [
                    'pocztapolska' => 'poczta',
                    'orlenpaczka' => 'orlen',
                    'inpost' => 'inpost',
                    'dpd' => 'dpd',
                    'ups' => 'ups',
                    'dhl' => 'dhl',
                    'gls' => 'gls',
                    'ambro' => 'ambro',
                    'hellmann' => 'hellmann',
                    'pekaes' => 'pekaes',
                    'rhenus' => 'rhenus',
                    'dhlparcel' => 'dhl',
                    'wedo' => 'one_delivery',
                    'ppl' => 'ppl',
                    'zasilkovna' => 'zasilkovna',
                    'balikovna' => 'balikovna',
                    'toptrans' => 'toptrans',
                ];

                $quotes = [];
                $region = (Configuration::get('ALSENDO_REGION') ?: 'pl');

                $mappedSupplier = '';
                if ($mappedServiceId && isset($svcIndex[(string) $mappedServiceId])) {
                    $mappedSupplier = strtolower($svcIndex[(string) $mappedServiceId]['supplier'] ?? '');
                }

                if ($region === 'cz') {
                    foreach ($servicesCfg as $svc) {
                        if (empty($svc['service_id'])) {
                            continue;
                        }
                        $svcId = (string) $svc['service_id'];
                        $svcName = $svc['name'] ?? '';
                        $baseName = preg_replace('/\s*\(Pickup point\)\s*$/i', '', $svcName);

                        foreach ($valuation as $sid => $vData) {
                            if (empty($vData['priceTable']['price']) || empty($vData['priceTable']['priceGross'])) {
                                continue;
                            }

                            $apiCarrier = $vData['carrier'] ?? '';
                            $sameService = strtolower($baseName) === strtolower($apiCarrier);
                            if (!$sameService && $apiCarrier !== '') {
                                $baseId = strtolower(preg_replace('/_topoint$/i', '', $svcId));
                                $asciiCarrier = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $apiCarrier);
                                $sameService = ($baseId === strtolower($asciiCarrier ?: ''));
                            }

                            if ($sameService) {
                                if ($mappedServiceId && $mappedServiceId !== 0 && $mappedSupplier !== '') {
                                    $svcSupplier = strtolower($svc['supplier'] ?? '');
                                    if ($svcSupplier !== $mappedSupplier) {
                                        break;
                                    }
                                }

                                $gross = (float) $vData['priceTable']['priceGross'];
                                $net = (float) $vData['priceTable']['price'];
                                $apiCurrency = $vData['priceTable']['currency'] ?? '';

                                $logoUrl = $this->resolveServiceLogoUrl($svc, $logoBase, $aliasMap);

                                $quotes[] = [
                                    'service_id' => $svcId,
                                    'external_id' => $svcId,
                                    'service_name' => $svcName,
                                    'supplier' => $svc['supplier'] ?? '',
                                    'price_gross_display' => number_format($gross, 2, '.', '') . ' ' . $apiCurrency,
                                    'price_net_display' => number_format($net, 2, '.', '') . ' ' . $apiCurrency,
                                    'logo_url' => $logoUrl,
                                    'is_mapped' => ((string) $svcId === (string) $mappedServiceId),
                                    'is_delivery_to_point' => !empty($svc['to_point']) || !empty($svc['door_to_point']) || !empty($svc['point_to_point']),
                                ];

                                break;
                            }
                        }
                    }

                    if ($mappedServiceId && $mappedSupplier !== '') {
                        $quotedIds = array_column($quotes, 'service_id');
                        foreach ($servicesCfg as $svc) {
                            $svcId = (string) ($svc['service_id'] ?? '');
                            if (empty($svcId) || in_array($svcId, $quotedIds, true)) {
                                continue;
                            }
                            $svcSupplier = strtolower($svc['supplier'] ?? '');
                            if ($svcSupplier !== $mappedSupplier) {
                                continue;
                            }
                            $logoUrl = $this->resolveServiceLogoUrl($svc, $logoBase, $aliasMap);
                            $quotes[] = [
                                'service_id' => $svcId,
                                'external_id' => $svcId,
                                'service_name' => $svc['name'] ?? ('Service ' . $svcId),
                                'supplier' => $svc['supplier'] ?? '',
                                'price_gross_display' => '',
                                'price_net_display' => '',
                                'logo_url' => $logoUrl,
                                'is_mapped' => ((string) $svcId === (string) $mappedServiceId),
                                'is_delivery_to_point' => !empty($svc['to_point']) || !empty($svc['door_to_point']) || !empty($svc['point_to_point']),
                                'no_price' => true,
                            ];
                        }
                    }
                } else {
                    foreach ($priceTable as $sid => $row) {
                        $effectiveServiceId = $sid;

                        if ($mappedServiceId && $mappedServiceId !== 0) {
                            $currentSupplier = strtolower(($svcIndex[(string) $effectiveServiceId] ?? $svcIndex[$effectiveServiceId . '_topoint'] ?? [])['supplier'] ?? '');
                            $matchBySupplier = ($mappedSupplier !== '' && $currentSupplier === $mappedSupplier);
                            $matchById = ((string) $effectiveServiceId === (string) $mappedServiceId);

                            if (!$matchById && !$matchBySupplier) {
                                continue;
                            }
                        }

                        $svc = $svcIndex[(string) $effectiveServiceId] ?? $svcIndex[$effectiveServiceId . '_topoint'] ?? ['name' => 'Service ' . $effectiveServiceId, 'supplier' => ''];
                        $logoUrl = $this->resolveServiceLogoUrl($svc, $logoBase, $aliasMap);

                        $gross = isset($row['priceGross']) ? (float) $row['priceGross'] : 0.0;
                        $net = isset($row['price']) ? (float) $row['price'] : 0.0;
                        $apiCurrency = $row['currency'] ?? '';

                        if ($region === 'pl') {
                            $gross = $gross / 100;
                            $net = $net / 100;
                        }

                        $quotes[] = [
                            'service_id' => $effectiveServiceId,
                            'external_id' => $effectiveServiceId,
                            'service_name' => $svc['name'] ?? ('Service ' . $effectiveServiceId),
                            'supplier' => $svc['supplier'] ?? '',
                            'price_gross_display' => number_format($gross, 2, '.', '') . ' ' . $apiCurrency,
                            'price_net_display' => number_format($net, 2, '.', '') . ' ' . $apiCurrency,
                            'logo_url' => $logoUrl,
                            'is_mapped' => ((string) $effectiveServiceId === (string) $mappedServiceId),
                        ];
                    }

                    if ($mappedServiceId && $mappedSupplier !== '') {
                        $quotedIds = array_column($quotes, 'service_id');
                        foreach ($svcIndex as $svcId => $svcData) {
                            if (in_array($svcId, $quotedIds, true)) {
                                continue;
                            }
                            $svcSupplier = strtolower($svcData['supplier'] ?? '');
                            if ($svcSupplier !== $mappedSupplier) {
                                continue;
                            }
                            $logoUrl = $this->resolveServiceLogoUrl($svcData, $logoBase, $aliasMap);
                            $quotes[] = [
                                'service_id' => $svcId,
                                'external_id' => $svcId,
                                'service_name' => $svcData['name'] ?? ('Service ' . $svcId),
                                'supplier' => $svcData['supplier'] ?? '',
                                'price_gross_display' => '',
                                'price_net_display' => '',
                                'logo_url' => $logoUrl,
                                'is_mapped' => ((string) $svcId === (string) $mappedServiceId),
                                'no_price' => true,
                            ];
                        }
                    }
                }

                if (empty($quotes) && $mappedServiceId) {
                    $svcName = $svcIndex[(string) $mappedServiceId]['name'] ?? "Service $mappedServiceId";
                    $this->jsonResponse([
                        'success' => true,
                        'data' => [],
                        'warning' => "Usługa '$svcName' nie jest dostępna dla wybranego typu odbioru. Spróbuj zmienić typ odbioru.",
                    ]);
                    break;
                }

                usort($quotes, function ($a, $b) {
                    $ma = (int) $a['is_mapped'];
                    $mb = (int) $b['is_mapped'];
                    if ($ma !== $mb) {
                        return $mb - $ma;
                    }
                    $pa = empty($a['no_price']) ? 0 : 1;
                    $pb = empty($b['no_price']) ? 0 : 1;

                    return $pa - $pb;
                });

                $this->jsonResponse(['success' => true, 'data' => $quotes]);
                break;
            case 'validatePreOrderShipment':
                $dto = OrderShipmentSubmitDTO::fromRequest($_POST);
                if (empty($dto->sender_country)) {
                    $regionCountryMap = ['pl' => 'PL', 'cz' => 'CZ', 'ro' => 'RO'];
                    $dto->sender_country = $regionCountryMap[Configuration::get('ALSENDO_REGION') ?: 'pl'] ?? 'PL';
                }
                $order = new Order((int) $dto->order_id);

                $idCarrier = (int) Tools::getValue('id_carrier');
                if ($idCarrier) {
                    $order->id_carrier = $idCarrier;
                }

                $dto->package_content = $this->resolvePackageContent(
                    $dto->package_content,
                    (int) $dto->order_id
                );

                list($mappedServiceId, $hasMap) = $this->getCarrierMapping((int) $order->id_carrier);

                if (empty($dto->selected_pickup_type)) {
                    $region = (Configuration::get('ALSENDO_REGION') ?: 'pl');
                    if ($region === 'cz') {
                        $dto->selected_pickup_type = OrderDetailsService::getConfiguredPickupType($region);
                    } else {
                        $dto->selected_pickup_type = $hasMap ? 'SELF' : 'COURIER';
                    }
                }

                if ($dto->selected_pickup_type === 'COURIER') {
                    $userDate = $dto->shipment_preferred_pickup_date ?? null;
                    if (empty($userDate) || $userDate < date('Y-m-d')) {
                        $dto->shipment_preferred_pickup_date = PickupHoursService::getDefaultPickupDate();
                    }

                    $defaultHours = PickupHoursService::getDefaultPickupHours();
                    if (empty($dto->shipment_preferred_pickup_hours_from)) {
                        $dto->shipment_preferred_pickup_hours_from = $defaultHours['from'];
                    }
                    if (empty($dto->shipment_preferred_pickup_hours_to)) {
                        $dto->shipment_preferred_pickup_hours_to = $defaultHours['to'];
                    }
                }

                $this->savePreFormDetails((int) $dto->order_id);

                if ($dto->selected_pickup_type === 'COURIER' && !empty($dto->merchant_pickup_point)) {
                    $dto->merchant_pickup_point = null;
                }

                $region = (Configuration::get('ALSENDO_REGION') ?: 'pl');
                if ($region === 'pl') {
                    $requiresPoint = (bool) $hasMap;
                } else {
                    $requiresPoint = false;
                    if (!empty($dto->shipment_selected_service)) {
                        $requiresPoint = $this->requiresPickupForService($dto->shipment_selected_service);
                    } else {
                        $requiresPoint = (bool) $hasMap;
                    }
                }

                $validator = new OrderValidator();
                $errors = $validator->validateShipmentData($dto);

                if (!$requiresPoint && isset($errors['shipment_pickup_point'])) {
                    unset($errors['shipment_pickup_point']);
                }

                if (empty($dto->shipment_selected_service) && (int) $order->id_carrier > 0) {
                    unset($errors['shipment_selected_service']);
                }

                if (empty($errors)) {
                    $pickupPoint = null;
                    if ((int) $order->id_cart > 0) {
                        $pickupPoint = Db::getInstance()->getValue(
                            'SELECT pickup_point FROM ' . _DB_PREFIX_ . 'alsendo_order_pickup WHERE id_cart=' . (int) $order->id_cart
                        );
                    }
                    $pickupPoint = $pickupPoint ? json_decode($pickupPoint, true) : null;

                    $savedDetails = Db::getInstance()->getRow(
                        'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_details` WHERE id_order=' . (int) $dto->order_id
                    );
                    $recipientOverride = null;
                    if ($savedDetails) {
                        $savedData = json_decode($savedDetails['data'], true);
                        $recipientOverride = $savedData['recipient'] ?? null;
                    }

                    $fullDto = FullOrderDTO::fromPrestaOrder($order, $dto, $pickupPoint, $recipientOverride);
                    $w = new WrapperService();
                    $res = $w->getOrderValuation($fullDto);

                    if ($res->isSuccess()) {
                        $payload = $res->getData();
                        $valuation = json_decode(json_encode($payload), true);

                        $priceTable = [];
                        foreach ($valuation as $sid => $service) {
                            if (isset($service['priceTable'])) {
                                $priceTable[$sid] = $service['priceTable'];
                                if (!empty($service['carrier'])) {
                                    $priceTable[$service['carrier']] = $service['priceTable'];
                                }
                            }
                        }

                        $selectedServiceId = !empty($dto->shipment_selected_service) ? $dto->shipment_selected_service : null;
                        if (!$selectedServiceId) {
                            list($mappedServiceId, $hasMap) = $this->getCarrierMapping((int) $order->id_carrier);
                            $selectedServiceId = $mappedServiceId;
                        }

                        $estimatedPrice = null;
                        if ($selectedServiceId && isset($priceTable[$selectedServiceId])) {
                            $estimatedPrice = isset($priceTable[$selectedServiceId]['priceGross'])
                                ? (float) $priceTable[$selectedServiceId]['priceGross']
                                : null;
                            if ($estimatedPrice !== null && $region === 'pl') {
                                $estimatedPrice /= 100;
                            }
                        }

                        if ($estimatedPrice !== null) {
                            $this->saveEstimatedPrice((int) $dto->order_id, $estimatedPrice, $selectedServiceId);
                        }
                    }
                }

                $errors = $this->translateValidationErrors($errors);
                $this->jsonResponse(['success' => empty($errors), 'errors' => $errors]);
                break;
            case 'saveOrderDetails':
                $orderId = (int) Tools::getValue('order_id');
                if (!$orderId) {
                    $this->jsonResponse(['success' => false, 'message' => 'Missing order ID']);
                    break;
                }

                $this->savePreFormDetails($orderId);

                $this->jsonResponse(['success' => true, 'message' => 'Order details saved']);
                break;
            case 'submitOrderShipment':
                $dto = OrderShipmentSubmitDTO::fromRequest($_POST);
                if (empty($dto->sender_country)) {
                    $regionCountryMap = ['pl' => 'PL', 'cz' => 'CZ', 'ro' => 'RO'];
                    $dto->sender_country = $regionCountryMap[Configuration::get('ALSENDO_REGION') ?: 'pl'] ?? 'PL';
                }
                $order = new Order($dto->order_id);

                $idCarrier = (int) Tools::getValue('id_carrier');
                if ($idCarrier) {
                    $order->id_carrier = $idCarrier;
                }

                $dto->package_content = $this->resolvePackageContent(
                    $dto->package_content,
                    (int) $dto->order_id
                );

                list($mappedServiceId, $hasMap) = $this->getCarrierMapping((int) $order->id_carrier);

                if (empty($dto->selected_pickup_type)) {
                    $region = (Configuration::get('ALSENDO_REGION') ?: 'pl');
                    if ($region === 'cz') {
                        $dto->selected_pickup_type = OrderDetailsService::getConfiguredPickupType($region);
                    } else {
                        $dto->selected_pickup_type = $hasMap ? 'SELF' : 'COURIER';
                    }
                }

                if ($dto->selected_pickup_type === 'COURIER') {
                    $userDate = $dto->shipment_preferred_pickup_date ?? null;
                    if (empty($userDate) || $userDate < date('Y-m-d')) {
                        $dto->shipment_preferred_pickup_date = PickupHoursService::getDefaultPickupDate();
                    }

                    $defaultHours = PickupHoursService::getDefaultPickupHours();
                    if (empty($dto->shipment_preferred_pickup_hours_from)) {
                        $dto->shipment_preferred_pickup_hours_from = $defaultHours['from'];
                    }
                    if (empty($dto->shipment_preferred_pickup_hours_to)) {
                        $dto->shipment_preferred_pickup_hours_to = $defaultHours['to'];
                    }
                }

                $this->savePreFormDetails((int) $dto->order_id);

                if ($dto->selected_pickup_type === 'COURIER' && !empty($dto->merchant_pickup_point)) {
                    $dto->merchant_pickup_point = null;
                }

                $region = (Configuration::get('ALSENDO_REGION') ?: 'pl');
                if ($region === 'pl') {
                    $requiresPoint = (bool) $hasMap;
                } else {
                    $requiresPoint = false;
                    if (!empty($dto->shipment_selected_service)) {
                        $requiresPoint = $this->requiresPickupForService($dto->shipment_selected_service);
                    } else {
                        $requiresPoint = (bool) $hasMap;
                    }
                }

                $pickupPoint = null;

                if (!empty($dto->shipment_pickup_point)) {
                    $pickupPoint = $dto->shipment_pickup_point;
                } elseif ((int) $order->id_cart > 0) {
                    $pickupPointData = Db::getInstance()->getValue(
                        'SELECT pickup_point FROM ' . _DB_PREFIX_ . 'alsendo_order_pickup 
             WHERE id_cart=' . (int) $order->id_cart
                    );
                    if ($pickupPointData) {
                        $pickupPoint = json_decode($pickupPointData, true);
                    }
                }

                $validator = new OrderValidator();
                $errors = $validator->validateShipmentData($dto);

                if (!$requiresPoint && isset($errors['shipment_pickup_point'])) {
                    unset($errors['shipment_pickup_point']);
                }

                if (empty($dto->shipment_selected_service) && (int) $order->id_carrier > 0) {
                    unset($errors['shipment_selected_service']);
                }

                if (!empty($errors)) {
                    $errors = $this->translateValidationErrors($errors);
                    $this->jsonResponse(['success' => false, 'error' => 'Validation failed', 'errors' => $errors]);
                    break;
                }

                $savedDetails = Db::getInstance()->getRow(
                    'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_details` WHERE id_order=' . (int) $dto->order_id
                );
                $recipientOverride = null;
                if ($savedDetails) {
                    $savedData = json_decode($savedDetails['data'], true);
                    $recipientOverride = $savedData['recipient'] ?? null;
                }

                $fullDto = FullOrderDTO::fromPrestaOrder($order, $dto, $pickupPoint, $recipientOverride);

                $w = new WrapperService();
                $selectedService = $dto->shipment_selected_service;
                if (empty($selectedService)) {
                    list($mappedServiceId, $hasMap) = $this->getCarrierMapping((int) $order->id_carrier);
                    $selectedService = $mappedServiceId;
                }
                $res = $w->sendOrder($fullDto, $selectedService);

                if ($res->isSuccess()) {
                    $this->saveOrderShipmentToDatabase((int) $dto->order_id, $res);
                }

                $this->jsonResponse($res->toArray());
                break;
            case 'getPackageTemplates':
                $packageTemplates = json_decode(Configuration::get('ALSENDO_SHIPPING_SETTINGS_LIST', null, null, null, '[]'), true) ?: [];
                $this->jsonResponse(['success' => true, 'templates' => $packageTemplates]);
                break;
            case 'quickSendOrderShipment':
                $orderId = (int) Tools::getValue('order_id');
                $templateIndex = (int) Tools::getValue('template_index', 0);
                $order = new Order($orderId);

                if (!Validate::isLoadedObject($order)) {
                    $this->jsonResponse(['success' => false, 'error' => 'Order not found']);

                    return;
                }

                $senderTemplates = json_decode(Configuration::get('ALSENDO_SENDER_LIST', null, null, null, '[]'), true) ?: [];
                $defaultSender = null;
                foreach ($senderTemplates as $tpl) {
                    if (!empty($tpl['main'])) {
                        $defaultSender = $tpl;
                        break;
                    }
                }
                if (!$defaultSender && !empty($senderTemplates)) {
                    $defaultSender = $senderTemplates[0];
                }

                if (!$defaultSender) {
                    $this->jsonResponse(['success' => false, 'error' => 'No default sender configured']);

                    return;
                }

                $packageTemplates = json_decode(Configuration::get('ALSENDO_SHIPPING_SETTINGS_LIST', null, null, null, '[]'), true) ?: [];

                $defaultPackage = null;
                if (isset($packageTemplates[$templateIndex])) {
                    $defaultPackage = $packageTemplates[$templateIndex];
                } else {
                    foreach ($packageTemplates as $tpl) {
                        if (!empty($tpl['main'])) {
                            $defaultPackage = $tpl;
                            break;
                        }
                    }
                    if (!$defaultPackage && !empty($packageTemplates)) {
                        $defaultPackage = $packageTemplates[0];
                    }
                }

                if (!$defaultPackage) {
                    $this->jsonResponse(['success' => false, 'error' => 'No default package configured']);

                    return;
                }

                list($mappedServiceId, $hasMap) = $this->getCarrierMapping((int) $order->id_carrier);

                $savedDetailsRow = Db::getInstance()->getRow(
                    'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_details` WHERE id_order=' . (int) $orderId
                );
                $savedData = $savedDetailsRow ? json_decode($savedDetailsRow['data'], true) : [];
                $finalServiceId = null;

                $address = new Address($order->id_address_delivery);
                $customer = new Customer($order->id_customer);

                $region = (Configuration::get('ALSENDO_REGION') ?: 'pl');
                if ($region === 'cz') {
                    $pickupType = 'ONDEMAND';
                    if (!empty($defaultPackage['alsendo_pickup_type'])) {
                        $val = strtoupper($defaultPackage['alsendo_pickup_type']);
                        if (in_array($val, ['ONDEMAND', 'OCCASIONAL'])) {
                            $pickupType = $val;
                        }
                    }
                } else {
                    $pickupType = 'COURIER';
                    if (!empty($defaultPackage['alsendo_pickup_type'])) {
                        $pickupType = strtoupper($defaultPackage['alsendo_pickup_type']);
                    }
                }

                $pickupDate = '';
                if ($pickupType === 'COURIER') {
                    $pickupDate = PickupHoursService::getDefaultPickupDate();
                }

                $defaultHours = PickupHoursService::getDefaultPickupHours();

                $region = (Configuration::get('ALSENDO_REGION') ?: 'pl');
                $defaultCountryMap = ['pl' => 'PL', 'cz' => 'CZ', 'ro' => 'RO'];
                $defaultCountry = $defaultCountryMap[$region] ?? 'PL';

                $orderTotal = (float) $order->total_paid_tax_incl;
                $isCodOrder = in_array($order->module, ['ps_cashondelivery', 'cashondelivery'], true);
                $savedCod = (float) ($savedData['package']['cod_value'] ?? 0);
                $templateCod = (float) ($defaultPackage['alsendo_cod'] ?? 0);
                $savedDeclared = (float) ($savedData['package']['declared_value'] ?? 0);
                $templateDeclared = (float) ($defaultPackage['alsendo_declared_value'] ?? 0);

                $recipientFirstName = $address->firstname;
                $recipientLastName = $address->lastname;
                $recipientPostalCode = $address->postcode;
                $recipientCity = $address->city;
                $recipientCountry = Country::getIsoById($address->id_country) ?: $defaultCountry;

                $recipientBlock = '';
                $recipientEntrance = '';
                $recipientFloor = '';
                $recipientFlat = '';

                if (!empty($savedData['recipient']['street'])) {
                    $recipientStreet = $savedData['recipient']['street'];
                    $recipientBuilding = $savedData['recipient']['building_number'] ?? '';
                    $recipientBlock = $savedData['recipient']['block'] ?? '';
                    $recipientEntrance = $savedData['recipient']['entrance'] ?? '';
                    $recipientFloor = $savedData['recipient']['floor'] ?? '';
                    $recipientFlat = $savedData['recipient']['flat'] ?? '';
                } elseif (!empty($savedData['recipient']['address'])) {
                    $parsed = AddressParser::parseAddressToComponents($savedData['recipient']['address'], $savedData['recipient']['address2'] ?? '');
                    $recipientStreet = $parsed['street'];
                    $recipientBuilding = $parsed['building_number'];
                    if ($region === 'ro') {
                        $roSource = $savedData['recipient']['address2'] ?? '';
                        if (empty($roSource)) {
                            $roSource = $parsed['apartment_number'];
                        }
                        $roComponents = AddressParser::parseRomanianComponents($roSource);
                        $recipientBlock = $roComponents['block'];
                        $recipientEntrance = $roComponents['entrance'];
                        $recipientFloor = $roComponents['floor'];
                        $recipientFlat = $roComponents['flat'];
                    }
                } else {
                    $parsed = AddressParser::parseAddressToComponents($address->address1, $address->address2);
                    $recipientStreet = $parsed['street'];
                    $recipientBuilding = $parsed['building_number'];
                    if ($region === 'ro') {
                        $roSource = $address->address2;
                        if (empty($roSource)) {
                            $roSource = $parsed['apartment_number'];
                        }
                        $roComponents = AddressParser::parseRomanianComponents($roSource);
                        $recipientBlock = $roComponents['block'];
                        $recipientEntrance = $roComponents['entrance'];
                        $recipientFloor = $roComponents['floor'];
                        $recipientFlat = $roComponents['flat'];
                    }
                }

                $postData = [
                    'order_id' => $orderId,
                    'sender_address_type' => $defaultSender['address_type'] ?? 'company',
                    'sender_company_name' => $defaultSender['company'] ?? '',
                    'sender_full_name' => trim(($defaultSender['firstname'] ?? '') . ' ' . ($defaultSender['lastname'] ?? '')),
                    'sender_street' => $defaultSender['street'] ?? '',
                    'sender_building_number' => $defaultSender['building'] ?? '',
                    'sender_apartment_number' => $defaultSender['apartment'] ?? '',
                    'sender_block' => $defaultSender['block'] ?? '',
                    'sender_entrance' => $defaultSender['entrance'] ?? '',
                    'sender_floor' => $defaultSender['floor'] ?? '',
                    'sender_flat' => $defaultSender['flat'] ?? '',
                    'sender_postal_code' => $defaultSender['postal'] ?? '',
                    'sender_city' => $defaultSender['city'] ?? '',
                    'sender_country' => $defaultSender['country'] ?? $defaultCountry,
                    'sender_phone_number' => $defaultSender['phone'] ?? '',
                    'sender_email' => $defaultSender['email'] ?? '',
                    'sender_contact_person' => $defaultSender['contact'] ?? '',
                    'sender_bank_account_number' => $defaultSender['bank'] ?? '',
                    'sender_bank_code' => $defaultSender['bank_code'] ?? '',
                    'sender_additional_bank_account_number' => $defaultSender['additional_bank_account_number'] ?? '',
                    'sender_external_id' => $defaultSender['external_id'] ?? '',
                    'package_shipment_type' => $defaultPackage['alsendo_package_type'] ?? ($region === 'cz' ? 'PACKAGE' : ($region === 'ro' ? 'package' : 'PACZKA')),
                    'package_is_nstd' => (int) ($defaultPackage['alsendo_is_nstd'] ?? 0),
                    'package_width' => (float) ($defaultPackage['alsendo_width'] ?? 0),
                    'package_length' => (float) ($defaultPackage['alsendo_length'] ?? 0),
                    'package_height' => (float) ($defaultPackage['alsendo_height'] ?? 0),
                    'package_weight' => (float) ($defaultPackage['alsendo_weight'] ?? 0),
                    'package_content' => $defaultPackage['alsendo_shipment_content'] ?? 'Order #' . $orderId,
                    'package_cod' => $savedCod > 0 ? $savedCod
                        : ($templateCod > 0 ? $templateCod
                        : ($isCodOrder ? $orderTotal : 0)),
                    'package_declared_value' => $savedDeclared > 0 ? $savedDeclared
                        : ((bool) Configuration::get('ALSENDO_AUTO_DECLARED_VALUE')
                            ? (($templateDeclared > $orderTotal) ? $templateDeclared : $orderTotal)
                            : ($templateDeclared > 0 ? $templateDeclared : 0)),
                    'selected_pickup_type' => $pickupType,
                    'shipment_preferred_pickup_date' => $pickupDate,
                    'shipment_preferred_pickup_hours_from' => $defaultHours['from'],
                    'shipment_preferred_pickup_hours_to' => $defaultHours['to'],
                    'shipment_selected_service' => $finalServiceId,
                    'shipping_first_name' => $recipientFirstName,
                    'shipping_last_name' => $recipientLastName,
                    'shipping_street' => $recipientStreet,
                    'shipping_building_number' => $recipientBuilding,
                    'shipping_block' => $recipientBlock,
                    'shipping_entrance' => $recipientEntrance,
                    'shipping_floor' => $recipientFloor,
                    'shipping_flat' => $recipientFlat,
                    'shipping_postal_code' => $recipientPostalCode,
                    'shipping_city' => $recipientCity,
                    'shipping_country' => $recipientCountry,
                    'shipping_phone_number' => $address->phone_mobile ?: $address->phone,
                    'shipping_email' => $customer->email,
                ];

                if ($region === 'cz') {
                    $smRow = Db::getInstance()->getRow(
                        'SELECT ship_via_pickup_point, pickup_request FROM `' . _DB_PREFIX_ . 'alsendo_shipping_methods`
                         WHERE id_carrier = ' . (int) $order->id_carrier . ' AND id_shop = ' . (int) $this->context->shop->id
                    );
                    if ($smRow) {
                        $postData['shipping_via_pickup_point'] = (int) $smRow['ship_via_pickup_point'];
                        $postData['pickup_request'] = (int) $smRow['pickup_request'];
                    }
                }

                $pickupPoint = null;
                if ($hasMap && (int) $order->id_cart > 0) {
                    $pickupPointData = Db::getInstance()->getValue(
                        'SELECT pickup_point FROM ' . _DB_PREFIX_ . 'alsendo_order_pickup WHERE id_cart=' . (int) $order->id_cart
                    );
                    if ($pickupPointData) {
                        $pickupPoint = json_decode($pickupPointData, true);
                        $postData['shipment_pickup_point'] = json_encode($pickupPoint);
                    }
                }

                $mapBridge = new MapBridge();
                $qsCarrier = new Carrier($order->id_carrier);
                $qsCarrierName = strtolower($qsCarrier->name);

                if ($hasMap && !$pickupPoint) {
                    $pickupKey = null;
                    foreach ($mapBridge->getDefaultPickupOperators($region) as $opKey) {
                        if (strpos($qsCarrierName, $opKey) !== false) {
                            $pickupKey = $mapBridge->getPickupConfigKey($opKey);
                            break;
                        }
                    }

                    if ($pickupKey) {
                        $defaultPickup = Configuration::get($pickupKey, null, null, null, '');
                        if ($defaultPickup) {
                            $postData['shipment_pickup_point'] = $defaultPickup;
                        }
                    }
                }

                $merchantPickupKey = null;
                foreach ($mapBridge->getDefaultPickupOperators($region) as $opKey) {
                    if (strpos($qsCarrierName, $opKey) !== false) {
                        $merchantPickupKey = $mapBridge->getPickupConfigKey($opKey);
                        break;
                    }
                }

                if ($merchantPickupKey) {
                    $merchantPickupConfig = Configuration::get($merchantPickupKey, null, null, null, '');

                    if ($merchantPickupConfig) {
                        $postData['merchant_pickup_point'] = $merchantPickupConfig;
                    }
                }

                $_POST = array_merge($_POST, $postData);
                $dto = OrderShipmentSubmitDTO::fromRequest($_POST);

                $dto->package_content = $this->resolvePackageContent($dto->package_content, $orderId);

                $validator = new OrderValidator();
                $errors = $validator->validateShipmentData($dto);

                if (!$hasMap && isset($errors['shipment_pickup_point'])) {
                    unset($errors['shipment_pickup_point']);
                }

                if (isset($errors['shipment_selected_service'])) {
                    unset($errors['shipment_selected_service']);
                }

                if (!empty($errors)) {
                    $this->jsonResponse(['success' => false, 'error' => 'Validation failed', 'errors' => $errors]);

                    return;
                }

                $savedDetails = Db::getInstance()->getRow(
                    'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_details` WHERE id_order=' . (int) $orderId
                );
                $recipientOverride = null;
                if ($savedDetails) {
                    $savedData = json_decode($savedDetails['data'], true);
                    $recipientOverride = $savedData['recipient'] ?? null;
                }

                $fullDto = FullOrderDTO::fromPrestaOrder($order, $dto, $pickupPoint, $recipientOverride);
                $w = new WrapperService();

                $estimatedPrice = 0;
                $quoteRes = $w->getOrderValuation($fullDto);
                if ($quoteRes->isSuccess()) {
                    $quoteData = $quoteRes->getData();
                    $valuation = json_decode(json_encode($quoteData), true);

                    $priceTable = [];
                    foreach ($valuation as $sid => $service) {
                        if (isset($service['priceTable'])) {
                            $priceTable[$sid] = $service['priceTable'];
                            if (!empty($service['carrier'])) {
                                $priceTable[$service['carrier']] = $service['priceTable'];
                            }
                        }
                    }

                    $cheapest = WrapperService::selectCheapestService($valuation, $region, $hasMap, $mappedServiceId);
                    if (!$cheapest) {
                        $carrier = new Carrier($order->id_carrier);
                        $carrierName = Validate::isLoadedObject($carrier) ? $carrier->name : 'Unknown';
                        $this->jsonResponse([
                            'success' => false,
                            'error' => "Nie udało się wybrać najtańszego serwisu dla \"{$carrierName}\": brak serwisów z ceną",
                        ]);

                        return;
                    }
                    $finalServiceId = $cheapest['service_id'];
                    $estimatedPrice = $cheapest['price_gross'];
                    $postData['shipment_selected_service'] = $finalServiceId;
                } else {
                    $carrier = new Carrier($order->id_carrier);
                    $carrierName = Validate::isLoadedObject($carrier) ? $carrier->name : 'Unknown';
                    $this->jsonResponse([
                        'success' => false,
                        'error' => "Nie udało się pobrać wyceny dla \"{$carrierName}\": " . ($quoteRes->getError() ?: 'API error'),
                    ]);

                    return;
                }

                $dto->shipment_selected_service = $finalServiceId;
                $_POST['shipment_selected_service'] = $finalServiceId;
                $fullDto = FullOrderDTO::fromPrestaOrder($order, $dto, $pickupPoint, $recipientOverride);

                $res = $w->sendOrder($fullDto, $finalServiceId);

                if ($res->isSuccess()) {
                    $this->saveOrderShipmentToDatabase($orderId, $res, $estimatedPrice);
                    $this->saveQuickSendOrderDetails($orderId, $defaultSender, $defaultPackage, $pickupType, $finalServiceId, $postData);
                }

                $this->jsonResponse($res->toArray());
                break;
            case 'cancelOrderShipment':
            case 'cancel':
                $orderId = (int) Tools::getValue('order_id');

                $row = Db::getInstance()->getRow(
                    'SELECT * FROM `' . _DB_PREFIX_ . 'alsendo_order_shipment` WHERE id_order=' . (int) $orderId
                );

                if (!$row) {
                    $this->jsonResponse(['success' => false, 'error' => 'Shipment not found']);

                    return;
                }

                $data = json_decode($row['data'], true);
                $region = Configuration::get('ALSENDO_REGION') ?: 'pl';
                if ($region === 'cz') {
                    $externalId = $data['carrier_tracking_number']
                        ?? $data['waybill_number']
                        ?? $data['id']
                        ?? null;
                } else {
                    $externalId = $data['id'] ?? null;
                }

                if (!$externalId) {
                    $this->jsonResponse(['success' => false, 'error' => 'No external ID']);

                    return;
                }

                $w = new WrapperService();
                $res = $w->cancelOrder((string) $externalId);

                if ($res->isSuccess()) {
                    $this->updatePrestaShopOrderStatus($orderId, 'cancelled');

                    Db::getInstance()->delete(
                        'alsendo_order_shipment',
                        'id_order=' . (int) $orderId
                    );

                    OrderDetailsService::syncTrackingNumber($orderId, '');

                    $detailsRow = Db::getInstance()->getRow(
                        'SELECT * FROM `' . _DB_PREFIX_ . 'alsendo_order_details` WHERE id_order=' . (int) $orderId
                    );

                    if ($detailsRow) {
                        $formData = json_decode($detailsRow['data'], true);
                        if (!is_array($formData)) {
                            $formData = [];
                        }

                        if (isset($formData['shipment'])) {
                            unset($formData['shipment']['estimated_price']);
                            unset($formData['shipment']['estimated_service_id']);
                        }

                        Db::getInstance()->update(
                            'alsendo_order_details',
                            ['data' => pSQL(json_encode($formData)), 'updated_at' => ['type' => 'sql', 'value' => 'NOW()']],
                            'id_order=' . (int) $orderId
                        );
                    }
                }

                $this->jsonResponse($res->toArray());
                break;
            case 'downloadShipmentWaybill':
            case 'downloadWaybill':
                $orderId = (int) Tools::getValue('order_id');

                $row = Db::getInstance()->getRow(
                    'SELECT * FROM `' . _DB_PREFIX_ . 'alsendo_order_shipment` WHERE id_order=' . (int) $orderId
                );

                if (!$row) {
                    $this->jsonResponse(['success' => false, 'error' => 'Shipment not found']);

                    return;
                }

                $data = json_decode($row['data'], true);
                $region = Configuration::get('ALSENDO_REGION') ?: 'pl';
                if ($region === 'cz') {
                    $externalId = $data['carrier_tracking_number']
                        ?? $data['waybill_number']
                        ?? $data['id']
                        ?? null;
                } else {
                    $externalId = $data['id'] ?? null;
                }

                if (!$externalId) {
                    $this->jsonResponse(['success' => false, 'error' => 'No external ID']);

                    return;
                }

                $w = new WrapperService();
                $res = $w->getWaybill((string) $externalId);

                if ($region === 'cz' && !$res->isSuccess()) {
                    $altId = null;
                    if ($externalId === ($data['carrier_tracking_number'] ?? null)) {
                        $altId = $data['waybill_number'] ?? $data['id'] ?? null;
                    } elseif ($externalId === ($data['waybill_number'] ?? null)) {
                        $altId = $data['id'] ?? null;
                    }
                    if ($altId && $altId !== $externalId) {
                        $res = $w->getWaybill((string) $altId);
                        if ($res->isSuccess()) {
                            $externalId = $altId;
                        }
                    }
                }

                if ($res->isSuccess() && !empty($res->getData()['waybill'])) {
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="waybill_' . $externalId . '.pdf"');
                    $waybill = $res->getData()['waybill'];
                    $waybill = preg_replace('/^data:application\/pdf;base64,/', '', $waybill);
                    echo base64_decode($waybill);
                    exit;
                }

                $this->jsonResponse($res->toArray());
                break;
            default:
                $this->jsonResponse(['success' => false, 'error' => 'Unknown action']);
        }
    }

    private function requiresPickupForService($serviceId): bool
    {
        $services = json_decode(Configuration::get('ALSENDO_AVAILABLE_SERVICES', null, null, null, ''), true);
        if (!is_array($services)) {
            return false;
        }
        foreach ($services as $svc) {
            if ((string) ($svc['service_id'] ?? '') === (string) $serviceId) {
                $name = strtolower($svc['name'] ?? '');
                if ($name === '') {
                    return false;
                }
                if (preg_match('/(paczkomat|punkt|point|locker|parcelshop|pop)/i', $name)) {
                    return true;
                }

                return false;
            }
        }

        return false;
    }

    // NOT USED COMMENTED FOR PRESTASHOP VALIDATION
    /*
    private function resolveMapOperator(?array $serviceInfo): ?string
    {
        if (!$serviceInfo) {
            return null;
        }

        $region = (\Configuration::get('ALSENDO_REGION') ?: 'pl');
        $mapBridge = new MapBridge();

        return $mapBridge->resolveMapOperator($region, $serviceInfo['supplier'] ?? '', $serviceInfo['name'] ?? '');
    }
    */

    private function getCarrierMapping(int $carrierId): array
    {
        if ($carrierId <= 0) {
            return [null, false];
        }

        $db = Db::getInstance();
        $shopId = (int) $this->context->shop->id;

        try {
            $sql = 'SELECT id_service, has_map AS map
                FROM `' . _DB_PREFIX_ . 'alsendo_shipping_methods`
                WHERE id_carrier = ' . (int) $carrierId . '
                  AND id_shop = ' . $shopId;
            $rows = $db->executeS($sql);
            $row = is_array($rows) && count($rows) ? $rows[0] : false;

            if (!$row || !isset($row['id_service'])) {
                $carrier = new Carrier($carrierId);
                $name = trim((string) $carrier->name);
                if ($name !== '') {
                    $sql = 'SELECT id_service, has_map AS map
                        FROM `' . _DB_PREFIX_ . 'alsendo_shipping_methods`
                        WHERE method_name = \'' . pSQL($name) . '\'
                          AND id_shop = ' . $shopId;
                    $rows = $db->executeS($sql);
                    $row = is_array($rows) && count($rows) ? $rows[0] : false;
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('[ALSENDO SQL ERROR getCarrierMapping] ' . $e->getMessage());
            $row = false;
        }

        if (is_array($row)) {
            $serviceId = (!empty($row['id_service'])) ? $row['id_service'] : null;
            $serviceId = $serviceId !== null && is_numeric($serviceId) ? (int) $serviceId : $serviceId;

            return [$serviceId, (bool) ($row['map'] ?? false)];
        }

        return [null, false];
    }

    private function translateValidationErrors(array $errors): array
    {
        $map = [
            'Sender full name is required' => $this->module->l('Sender full name is required', 'AdminAlsendoOrderController'),
            'Sender street is required' => $this->module->l('Sender street is required', 'AdminAlsendoOrderController'),
            'Sender postal code is required' => $this->module->l('Sender postal code is required', 'AdminAlsendoOrderController'),
            'Sender city is required' => $this->module->l('Sender city is required', 'AdminAlsendoOrderController'),
            'Valid sender country code is required' => $this->module->l('Valid sender country code is required', 'AdminAlsendoOrderController'),
            'Valid sender phone number is required' => $this->module->l('Valid sender phone number is required', 'AdminAlsendoOrderController'),
            'Valid sender email is required' => $this->module->l('Valid sender email is required', 'AdminAlsendoOrderController'),
            'Recipient first name is required' => $this->module->l('Recipient first name is required', 'AdminAlsendoOrderController'),
            'Recipient last name is required' => $this->module->l('Recipient last name is required', 'AdminAlsendoOrderController'),
            'Recipient street is required' => $this->module->l('Recipient street is required', 'AdminAlsendoOrderController'),
            'Recipient building number is required' => $this->module->l('Recipient building number is required', 'AdminAlsendoOrderController'),
            'Recipient postal code is required' => $this->module->l('Recipient postal code is required', 'AdminAlsendoOrderController'),
            'Recipient city is required' => $this->module->l('Recipient city is required', 'AdminAlsendoOrderController'),
            'Valid recipient country code is required' => $this->module->l('Valid recipient country code is required', 'AdminAlsendoOrderController'),
            'Recipient phone number is required' => $this->module->l('Recipient phone number is required', 'AdminAlsendoOrderController'),
            'Valid recipient email is required' => $this->module->l('Valid recipient email is required', 'AdminAlsendoOrderController'),
            'Bank account number is required for COD' => $this->module->l('Bank account number is required for COD', 'AdminAlsendoOrderController'),
            'Invalid IBAN format' => $this->module->l('Invalid IBAN format', 'AdminAlsendoOrderController'),
            'Package width must be greater than 0' => $this->module->l('Package width must be greater than 0', 'AdminAlsendoOrderController'),
            'Package length must be greater than 0' => $this->module->l('Package length must be greater than 0', 'AdminAlsendoOrderController'),
            'Package height must be greater than 0' => $this->module->l('Package height must be greater than 0', 'AdminAlsendoOrderController'),
            'Package weight must be greater than 0' => $this->module->l('Package weight must be greater than 0', 'AdminAlsendoOrderController'),
            'Shipping service must be selected' => $this->module->l('Shipping service must be selected', 'AdminAlsendoOrderController'),
            'Pickup point must be selected' => $this->module->l('Pickup point must be selected', 'AdminAlsendoOrderController'),
            'Pickup date is required for courier pickup' => $this->module->l('Pickup date is required for courier pickup', 'AdminAlsendoOrderController'),
            'Pickup date cannot be in the past. Please select today or a future date.' => $this->module->l('Pickup date cannot be in the past. Please select today or a future date.', 'AdminAlsendoOrderController'),
            'Pickup start time is required for courier pickup' => $this->module->l('Pickup start time is required for courier pickup', 'AdminAlsendoOrderController'),
            'Pickup end time is required for courier pickup' => $this->module->l('Pickup end time is required for courier pickup', 'AdminAlsendoOrderController'),
            'Pickup time cannot be earlier than 08:00' => $this->module->l('Pickup time cannot be earlier than 08:00', 'AdminAlsendoOrderController'),
            'Pickup time cannot be later than 17:00 due to courier operational hours' => $this->module->l('Pickup time cannot be later than 17:00 due to courier operational hours', 'AdminAlsendoOrderController'),
            'Pickup end time must be after start time' => $this->module->l('Pickup end time must be after start time', 'AdminAlsendoOrderController'),
            'Minimum pickup time window is 2 hours to ensure courier availability' => $this->module->l('Minimum pickup time window is 2 hours to ensure courier availability', 'AdminAlsendoOrderController'),
            'Pickup end time has already passed. Please select a future time or choose a later date.' => $this->module->l('Pickup end time has already passed. Please select a future time or choose a later date.', 'AdminAlsendoOrderController'),
        ];

        $translated = [];
        foreach ($errors as $field => $message) {
            $translated[$field] = $map[$message] ?? $message;
        }

        return $translated;
    }

    private function resolveServiceLogoUrl(array $svc, string $logoBase, array $aliasMap): string
    {
        $cachedLogoUrl = $svc['logo_url'] ?? '';
        if (!empty($cachedLogoUrl)) {
            return $cachedLogoUrl;
        }
        $supp = strtolower($svc['supplier'] ?? '');
        $suppSlug = preg_replace('/[^a-z0-9]+/', '', $supp);
        if (isset($aliasMap[$suppSlug])) {
            return $logoBase . $aliasMap[$suppSlug] . '.png';
        }

        return $logoBase . $suppSlug . '.png';
    }

    private function jsonResponse($data)
    {
        if (!empty($data['error'])) {
            $data['error'] = $this->translateApiError($data['error']);
        }
        header('Content-Type: application/json');
        exit(json_encode($data));
    }

    private function translateApiError(string $error): string
    {
        $translations = [
            'carrier rejected the pickup order' => $this->module->l(
                'The carrier rejected the pickup order — the configured pickup hours are outside the carrier\'s schedule for this service. Please adjust the default pickup hours in module settings (Settings → Default pickup hours) or use the full form to set a later pickup time.',
                'AdminAlsendoOrderController'
            ),
        ];

        foreach ($translations as $needle => $translated) {
            if (strpos($error, $needle) !== false) {
                return $translated;
            }
        }

        return $error;
    }

    private function resolvePackageContent(
        string $value,
        int $orderId
    ): string {
        $value = trim($value);

        if ($value === 'order_id' || $value === '{order_id}') {
            return (string) $orderId;
        }

        if ($value === 'product_id' || $value === '{product_id}') {
            $order = new Order($orderId);
            $rows = $order->getProducts();
            $ids = [];
            foreach ($rows as $r) {
                if (isset($r['product_id'])) {
                    $ids[] = (string) $r['product_id'];
                }
            }

            return implode(', ', $ids);
        }

        if ($value === 'product_name' || $value === '{product_name}') {
            $order = new Order($orderId);
            $rows = $order->getProducts();
            $names = [];
            foreach ($rows as $r) {
                if (isset($r['product_name'])) {
                    $names[] = $r['product_name'];
                }
            }

            return implode(', ', $names);
        }

        if ($value === 'invoice_number' || $value === '{invoice_number}') {
            $order = new Order($orderId);
            $invoices = $order->getInvoicesCollection();
            if ($invoices->count() > 0) {
                $firstInvoice = $invoices->getFirst();
                if ($firstInvoice instanceof OrderInvoice) {
                    return (string) $firstInvoice->number;
                }
            }

            return '';
        }

        if ($value === 'custom_text') {
            $custom = Tools::getValue('package_content_custom_text');

            return (string) $custom;
        }

        return $value;
    }

    private function savePreFormDetails(int $orderId): void
    {
        $row = Db::getInstance()->getRow(
            'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_details` WHERE id_order=' . (int) $orderId
        );
        $data = $row ? json_decode($row['data'], true) : [];
        if (!is_array($data)) {
            $data = [];
        }

        if (!isset($data['sender'])) {
            $data['sender'] = [];
        }
        if (!isset($data['package'])) {
            $data['package'] = [];
        }
        if (!isset($data['shipment'])) {
            $data['shipment'] = [];
        }
        if (!isset($data['recipient'])) {
            $data['recipient'] = [];
        }

        $data['sender']['address_type'] = Tools::getValue('sender_address_type', $data['sender']['address_type'] ?? 'company');
        $data['sender']['company_name'] = Tools::getValue('sender_company_name', $data['sender']['company_name'] ?? '');
        $data['sender']['full_name'] = Tools::getValue('sender_full_name', $data['sender']['full_name'] ?? '');
        $data['sender']['street'] = Tools::getValue('sender_street', $data['sender']['street'] ?? '');
        $data['sender']['building_number'] = Tools::getValue('sender_building_number', $data['sender']['building_number'] ?? '');
        $data['sender']['apartment_number'] = Tools::getValue('sender_apartment_number', $data['sender']['apartment_number'] ?? '');
        $data['sender']['block'] = Tools::getValue('sender_block', $data['sender']['block'] ?? '');
        $data['sender']['entrance'] = Tools::getValue('sender_entrance', $data['sender']['entrance'] ?? '');
        $data['sender']['floor'] = Tools::getValue('sender_floor', $data['sender']['floor'] ?? '');
        $data['sender']['flat'] = Tools::getValue('sender_flat', $data['sender']['flat'] ?? '');
        $data['sender']['postal_code'] = Tools::getValue('sender_postal_code', $data['sender']['postal_code'] ?? '');
        $data['sender']['city'] = Tools::getValue('sender_city', $data['sender']['city'] ?? '');
        $regionCountryMap = ['pl' => 'PL', 'cz' => 'CZ', 'ro' => 'RO'];
        $defaultCountry = $regionCountryMap[Configuration::get('ALSENDO_REGION') ?: 'pl'] ?? 'PL';
        $data['sender']['country'] = Tools::getValue('sender_country', $data['sender']['country'] ?? $defaultCountry);
        $data['sender']['phone_number'] = Tools::getValue('sender_phone_number', $data['sender']['phone_number'] ?? '');
        $data['sender']['email'] = Tools::getValue('sender_email', $data['sender']['email'] ?? '');
        $data['sender']['contact_person'] = Tools::getValue('sender_contact_person', $data['sender']['contact_person'] ?? '');
        $data['sender']['bank_account_number'] = Tools::getValue('sender_bank_account_number', $data['sender']['bank_account_number'] ?? '');
        $data['sender']['bank_code'] = Tools::getValue('sender_bank_code', $data['sender']['bank_code'] ?? '');
        $data['sender']['additional_bank_account_number'] = Tools::getValue('sender_additional_bank_account_number', $data['sender']['additional_bank_account_number'] ?? '');
        $data['sender']['external_id'] = Tools::getValue('sender_external_id', $data['sender']['external_id'] ?? '');

        $defaultPkgTypeForSave = ((Configuration::get('ALSENDO_REGION') ?: 'pl') === 'cz') ? 'PACKAGE' : (((Configuration::get('ALSENDO_REGION') ?: 'pl') === 'ro') ? 'package' : 'PACZKA');
        $data['package']['shipment_type'] = Tools::getValue('package_shipment_type', $data['package']['shipment_type'] ?? $data['package']['shipment_packaging'] ?? $defaultPkgTypeForSave);
        $data['package']['shipment_packaging'] = $data['package']['shipment_type'];
        $data['package']['package_type'] = $data['package']['shipment_type'];
        $data['package']['is_nstd'] = (int) Tools::getValue('package_is_nstd', $data['package']['is_nstd'] ?? 0);
        $data['package']['width'] = Tools::getValue('package_width', $data['package']['width'] ?? '');
        $data['package']['length'] = Tools::getValue('package_length', $data['package']['length'] ?? '');
        $data['package']['height'] = Tools::getValue('package_height', $data['package']['height'] ?? '');
        $data['package']['weight'] = Tools::getValue('package_weight', $data['package']['weight'] ?? '');
        $data['package']['package_content'] = $this->resolvePackageContent(Tools::getValue('package_content', $data['package']['package_content'] ?? ''), $orderId);
        $data['package']['cod_value'] = Tools::getValue('package_cod', $data['package']['cod_value'] ?? '');
        $data['package']['declared_value'] = Tools::getValue('package_declared_value', $data['package']['declared_value'] ?? '');
        $defaultPickupType = OrderDetailsService::getConfiguredPickupType();
        $rawPickupType = Tools::getValue('selected_pickup_type', $data['package']['pickup_type'] ?? $defaultPickupType);
        $data['package']['pickup_type'] = str_replace('_', '', strtoupper($rawPickupType));

        $data['shipment']['preferred_pickup_date'] = Tools::getValue('shipment_preferred_pickup_date', $data['shipment']['preferred_pickup_date'] ?? '');
        $data['shipment']['preferred_pickup_hours_from'] = Tools::getValue('shipment_preferred_pickup_hours_from', $data['shipment']['preferred_pickup_hours_from'] ?? '08:00');
        $data['shipment']['preferred_pickup_hours_to'] = Tools::getValue('shipment_preferred_pickup_hours_to', $data['shipment']['preferred_pickup_hours_to'] ?? '17:00');
        $data['shipment']['selected_service'] = Tools::getValue('shipment_selected_service', $data['shipment']['selected_service'] ?? null);
        $ppVal = Tools::getValue('shipment_pickup_point', '');
        if (!empty($ppVal)) {
            $ppDecoded = json_decode($ppVal, true);
            if (is_array($ppDecoded)) {
                $data['shipment']['shipment_pickup_point'] = $ppDecoded;
                $c = $ppDecoded['code'] ?? '';
                $n = $ppDecoded['description'] ?? ($ppDecoded['street'] ?? '');
                $data['shipment']['shipment_pickup_point_display'] = trim($c . ' - ' . $n, ' -');
            }
        } else {
            unset($data['shipment']['shipment_pickup_point']);
            unset($data['shipment']['shipment_pickup_point_display']);
            if ((int) $orderId > 0) {
                $order = new Order((int) $orderId);
                if ((int) $order->id_cart > 0) {
                    Db::getInstance()->delete('alsendo_order_pickup', 'id_cart=' . (int) $order->id_cart);
                }
            }
        }

        $data['recipient']['phone'] = Tools::getValue('shipping_phone_number', $data['recipient']['phone'] ?? '');
        $data['recipient']['email'] = Tools::getValue('shipping_email', $data['recipient']['email'] ?? '');
        $data['recipient']['first_name'] = Tools::getValue('shipping_first_name', $data['recipient']['first_name'] ?? '');
        $data['recipient']['last_name'] = Tools::getValue('shipping_last_name', $data['recipient']['last_name'] ?? '');
        $data['recipient']['company'] = Tools::getValue('shipping_company', $data['recipient']['company'] ?? '');
        $data['recipient']['street'] = Tools::getValue('shipping_street', $data['recipient']['street'] ?? '');
        $data['recipient']['building_number'] = Tools::getValue('shipping_building_number', $data['recipient']['building_number'] ?? '');
        $data['recipient']['apartment_number'] = Tools::getValue('shipping_apartment_number', $data['recipient']['apartment_number'] ?? '');
        $data['recipient']['block'] = Tools::getValue('shipping_block', $data['recipient']['block'] ?? '');
        $data['recipient']['entrance'] = Tools::getValue('shipping_entrance', $data['recipient']['entrance'] ?? '');
        $data['recipient']['floor'] = Tools::getValue('shipping_floor', $data['recipient']['floor'] ?? '');
        $data['recipient']['flat'] = Tools::getValue('shipping_flat', $data['recipient']['flat'] ?? '');
        unset($data['recipient']['address'], $data['recipient']['address2']);
        $data['recipient']['city'] = Tools::getValue('shipping_city', $data['recipient']['city'] ?? '');
        $data['recipient']['postal_code'] = Tools::getValue('shipping_postal_code', $data['recipient']['postal_code'] ?? '');
        $data['recipient']['country'] = Tools::getValue('shipping_country', $data['recipient']['country'] ?? '');

        if ($row) {
            Db::getInstance()->update(
                'alsendo_order_details',
                ['data' => pSQL(json_encode($data)), 'updated_at' => ['type' => 'sql', 'value' => 'NOW()']],
                'id_order=' . (int) $orderId
            );
        } else {
            Db::getInstance()->insert(
                'alsendo_order_details',
                [
                    'id_order' => (int) $orderId,
                    'data' => pSQL(json_encode($data)),
                    'created_at' => ['type' => 'sql', 'value' => 'NOW()'],
                    'updated_at' => ['type' => 'sql', 'value' => 'NOW()'],
                ]
            );
        }
    }

    private function saveQuickSendOrderDetails(int $orderId, $sender, $package, $pickupType, $serviceId, $postData): void
    {
        $row = Db::getInstance()->getRow(
            'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_details` WHERE id_order=' . (int) $orderId
        );
        $data = $row ? json_decode($row['data'], true) : [];
        if (!is_array($data)) {
            $data = [];
        }

        if (!isset($data['sender'])) {
            $data['sender'] = [];
        }
        if (!isset($data['package'])) {
            $data['package'] = [];
        }
        if (!isset($data['shipment'])) {
            $data['shipment'] = [];
        }

        $data['sender']['address_type'] = $sender['address_type'] ?? 'company';
        $data['sender']['company_name'] = $sender['company'] ?? '';
        $data['sender']['full_name'] = trim(($sender['firstname'] ?? '') . ' ' . ($sender['lastname'] ?? ''));
        $data['sender']['street'] = $sender['street'] ?? '';
        $data['sender']['building_number'] = $sender['building'] ?? '';
        $data['sender']['apartment_number'] = $sender['apartment'] ?? '';
        $data['sender']['block'] = $sender['block'] ?? '';
        $data['sender']['entrance'] = $sender['entrance'] ?? '';
        $data['sender']['floor'] = $sender['floor'] ?? '';
        $data['sender']['flat'] = $sender['flat'] ?? '';
        $data['sender']['postal_code'] = $sender['postal'] ?? '';
        $data['sender']['city'] = $sender['city'] ?? '';
        $regionCountryMap = ['pl' => 'PL', 'cz' => 'CZ', 'ro' => 'RO'];
        $data['sender']['country'] = $regionCountryMap[Configuration::get('ALSENDO_REGION') ?: 'pl'] ?? 'PL';
        $data['sender']['phone_number'] = $sender['phone'] ?? '';
        $data['sender']['email'] = $sender['email'] ?? '';
        $data['sender']['contact_person'] = $sender['contact'] ?? '';
        $data['sender']['bank_account_number'] = $sender['bank'] ?? '';
        $data['sender']['bank_code'] = $sender['bank_code'] ?? '';
        $data['sender']['additional_bank_account_number'] = $sender['additional_bank_account_number'] ?? '';
        $data['sender']['external_id'] = $sender['external_id'] ?? '';

        $qsDefaultPkgType = ((Configuration::get('ALSENDO_REGION') ?: 'pl') === 'cz') ? 'PACKAGE' : (((Configuration::get('ALSENDO_REGION') ?: 'pl') === 'ro') ? 'package' : 'PACZKA');
        $data['package']['shipment_packaging'] = $package['alsendo_package_type'] ?? $qsDefaultPkgType;
        $data['package']['package_type'] = $package['alsendo_package_type'] ?? $qsDefaultPkgType;
        $data['package']['shipment_type'] = $package['alsendo_package_type'] ?? $qsDefaultPkgType;
        $data['package']['is_nstd'] = (int) ($package['alsendo_is_nstd'] ?? 0);
        $data['package']['template_name'] = $package['alsendo_template_name'] ?? '';
        $data['package']['width'] = (float) ($package['alsendo_width'] ?? 0);
        $data['package']['length'] = (float) ($package['alsendo_length'] ?? 0);
        $data['package']['height'] = (float) ($package['alsendo_height'] ?? 0);
        $data['package']['weight'] = (float) ($package['alsendo_weight'] ?? 0);
        $data['package']['package_content'] = $package['alsendo_shipment_content'] ?? 'Order #' . $orderId;
        $data['package']['cod_value'] = (float) ($package['alsendo_cod'] ?? 0);
        $data['package']['declared_value'] = (float) ($package['alsendo_declared_value'] ?? 0);
        $data['package']['pickup_type'] = $pickupType;

        $data['shipment']['preferred_pickup_date'] = $postData['shipment_preferred_pickup_date'] ?? '';
        $data['shipment']['preferred_pickup_hours_from'] = $postData['shipment_preferred_pickup_hours_from'] ?? '08:00';
        $data['shipment']['preferred_pickup_hours_to'] = $postData['shipment_preferred_pickup_hours_to'] ?? '17:00';
        $data['shipment']['selected_service'] = $serviceId;

        if (isset($postData['shipment_pickup_point'])) {
            $ppDecoded = json_decode($postData['shipment_pickup_point'], true);
            if (is_array($ppDecoded)) {
                $data['shipment']['shipment_pickup_point'] = $ppDecoded;
            }
        }

        if ($row) {
            Db::getInstance()->update(
                'alsendo_order_details',
                ['data' => pSQL(json_encode($data)), 'updated_at' => ['type' => 'sql', 'value' => 'NOW()']],
                'id_order=' . (int) $orderId
            );
        } else {
            Db::getInstance()->insert(
                'alsendo_order_details',
                [
                    'id_order' => (int) $orderId,
                    'data' => pSQL(json_encode($data)),
                    'created_at' => ['type' => 'sql', 'value' => 'NOW()'],
                    'updated_at' => ['type' => 'sql', 'value' => 'NOW()'],
                ]
            );
        }
    }

    private function saveEstimatedPrice(int $orderId, float $price, $serviceId): void
    {
        $row = Db::getInstance()->getRow(
            'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_details` WHERE id_order=' . (int) $orderId
        );
        $data = $row ? json_decode($row['data'], true) : [];
        if (!is_array($data)) {
            $data = [];
        }

        if (!isset($data['shipment'])) {
            $data['shipment'] = [];
        }

        $data['shipment']['estimated_price'] = $price;
        $data['shipment']['estimated_service_id'] = $serviceId;

        if ($row) {
            Db::getInstance()->update(
                'alsendo_order_details',
                ['data' => pSQL(json_encode($data)), 'updated_at' => ['type' => 'sql', 'value' => 'NOW()']],
                'id_order=' . (int) $orderId
            );
        } else {
            Db::getInstance()->insert(
                'alsendo_order_details',
                [
                    'id_order' => (int) $orderId,
                    'data' => pSQL(json_encode($data)),
                    'created_at' => ['type' => 'sql', 'value' => 'NOW()'],
                    'updated_at' => ['type' => 'sql', 'value' => 'NOW()'],
                ]
            );
        }
    }

    private function saveOrderShipmentToDatabase(int $orderId, $apiResponse, float $fallbackPrice = 0): bool
    {
        if (!$apiResponse->isSuccess()) {
            return false;
        }

        $order = new Order($orderId);
        $previousState = Validate::isLoadedObject($order) ? (int) $order->current_state : null;

        $apiData = $apiResponse->getData();
        $apiJson = json_encode($apiData);

        $waybill = pSQL($apiData['waybill_number'] ?? '');
        $trackingUrl = pSQL($apiData['tracking_url'] ?? '');
        $serviceName = pSQL($apiData['service_name'] ?? '');
        $serviceId = pSQL($apiData['service_id'] ?? '');
        $status = pSQL($apiData['status'] ?? 'submitted');
        $price = isset($apiData['price']) ? (float) $apiData['price'] : 0;

        if ($price == 0) {
            $detailsRow = Db::getInstance()->getRow(
                'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_details` WHERE id_order=' . (int) $orderId
            );
            if ($detailsRow) {
                $detailsData = json_decode($detailsRow['data'], true);
                if (is_array($detailsData) && isset($detailsData['shipment']['estimated_price'])) {
                    $price = (float) $detailsData['shipment']['estimated_price'];
                }
            }
            if ($price == 0 && $fallbackPrice > 0) {
                $price = $fallbackPrice;
            }
        }

        $sql = 'REPLACE INTO `' . _DB_PREFIX_ . 'alsendo_order_shipment`
            (id_order, status, waybill_number, shipping_method, courier_service, price, tracking_url, previous_order_state, data)
            VALUES (
                ' . (int) $orderId . ',
                "' . $status . '",
                "' . $waybill . '",
                "' . $serviceName . '",
                "' . $serviceId . '",
                "' . $price . '",
                "' . $trackingUrl . '",
                ' . ($previousState !== null ? (int) $previousState : 'NULL') . ',
                "' . pSQL($apiJson) . '"
            )';

        $result = Db::getInstance()->execute($sql);
        if (!$result) {
            return false;
        }

        $this->updatePrestaShopOrderStatus($orderId, $status);

        OrderDetailsService::syncTrackingNumber($orderId, $waybill);

        return true;
    }

    private function updatePrestaShopOrderStatus(int $orderId, string $alsendoStatus): void
    {
        try {
            $alsendoPreparingStatus = Configuration::get('ALSENDO_OS_PREPARING');

            if (strtolower($alsendoStatus) === 'cancelled') {
                $shipmentRow = Db::getInstance()->getRow(
                    'SELECT previous_order_state FROM `' . _DB_PREFIX_ . 'alsendo_order_shipment`
                     WHERE id_order = ' . (int) $orderId
                );
                if ($shipmentRow && !empty($shipmentRow['previous_order_state'])) {
                    $newStateId = (int) $shipmentRow['previous_order_state'];
                } else {
                    $newStateId = (int) Configuration::get('PS_OS_CANCELED');
                }
            } else {
                $statusMap = [
                    'submitted' => $alsendoPreparingStatus ?: Configuration::get('PS_OS_SHIPPING'),
                    'new' => $alsendoPreparingStatus ?: Configuration::get('PS_OS_SHIPPING'),
                    'success' => $alsendoPreparingStatus ?: Configuration::get('PS_OS_SHIPPING'),
                    'ordered' => $alsendoPreparingStatus ?: Configuration::get('PS_OS_SHIPPING'),
                    'created' => $alsendoPreparingStatus ?: Configuration::get('PS_OS_SHIPPING'),
                    'exported' => $alsendoPreparingStatus ?: Configuration::get('PS_OS_SHIPPING'),
                    'onpickup' => $alsendoPreparingStatus ?: Configuration::get('PS_OS_SHIPPING'),
                    'intransit' => Configuration::get('PS_OS_SHIPPING'),
                    'ondelivery' => Configuration::get('PS_OS_SHIPPING'),
                    'delivered' => Configuration::get('PS_OS_DELIVERED'),
                ];

                $newStateId = $statusMap[strtolower($alsendoStatus)] ?? null;
            }

            if (!$newStateId) {
                PrestaShopLogger::addLog(
                    '[Alsendo] Unknown shipment status: ' . $alsendoStatus . ' for order ' . $orderId,
                    2,
                    null,
                    'Order',
                    $orderId
                );

                return;
            }

            $order = new Order($orderId);
            if (!Validate::isLoadedObject($order)) {
                return;
            }

            if ((int) $order->current_state === (int) $newStateId) {
                return;
            }

            $history = new OrderHistory();
            $history->id_order = $orderId;
            $history->changeIdOrderState((int) $newStateId, $order, true);
            $history->addWithemail(true);
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                '[Alsendo] Failed to update order status: ' . $e->getMessage(),
                3,
                null,
                'Order',
                $orderId
            );
        }
    }

    private function handleDirectPostSubmission(): void
    {
        try {
            $dto = OrderShipmentSubmitDTO::fromRequest($_POST);
            $order = new Order($dto->order_id);

            $idCarrier = (int) Tools::getValue('id_carrier');
            if ($idCarrier) {
                $order->id_carrier = $idCarrier;
            }

            $this->savePreFormDetails((int) $dto->order_id);

            $dto->package_content = $this->resolvePackageContent(
                $dto->package_content,
                (int) $dto->order_id
            );

            $requiresPoint = false;
            if (!empty($dto->shipment_selected_service)) {
                $requiresPoint = $this->requiresPickupForService($dto->shipment_selected_service);
            } else {
                list($mappedServiceId, $hasMap) = $this->getCarrierMapping((int) $order->id_carrier);
                $requiresPoint = (bool) $hasMap;
            }

            if (!$requiresPoint) {
                $dto->selected_pickup_type = 'NO_PICKUP';
            }

            $pickupPoint = null;

            if (!empty($dto->shipment_pickup_point)) {
                $pickupPoint = $dto->shipment_pickup_point;
            } elseif ((int) $order->id_cart > 0) {
                $pickupPointData = Db::getInstance()->getValue(
                    'SELECT pickup_point FROM ' . _DB_PREFIX_ . 'alsendo_order_pickup 
         WHERE id_cart=' . (int) $order->id_cart
                );
                if ($pickupPointData) {
                    $pickupPoint = json_decode($pickupPointData, true);
                }
            }

            $validator = new OrderValidator();
            $errors = $validator->validateShipmentData($dto);

            if (!$requiresPoint && isset($errors['shipment_pickup_point'])) {
                unset($errors['shipment_pickup_point']);
            }

            if (!empty($errors)) {
                $this->jsonResponse(['success' => false, 'error' => 'Validation failed', 'errors' => $errors]);

                return;
            }

            $savedDetails = Db::getInstance()->getRow(
                'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_details` WHERE id_order=' . (int) $dto->order_id
            );
            $recipientOverride = null;
            if ($savedDetails) {
                $savedData = json_decode($savedDetails['data'], true);
                $recipientOverride = $savedData['recipient'] ?? null;
            }

            $fullDto = FullOrderDTO::fromPrestaOrder($order, $dto, $pickupPoint, $recipientOverride);
            $w = new WrapperService();
            $res = $w->sendOrder($fullDto, $dto->shipment_selected_service);

            if ($res->isSuccess()) {
                $this->saveOrderShipmentToDatabase((int) $dto->order_id, $res);
            }

            $this->jsonResponse($res->toArray());
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function postProcess()
    {
        if (Tools::getValue('submitBulkActionAslendobulk')) {
            $orderIds = array_filter(array_map('intval', Tools::getValue('orderBox', [])));

            if (empty($orderIds)) {
                $this->errors[] = $this->module->l('Please select at least one order', 'AdminAlsendoOrderController');

                return;
            }

            $_SESSION['alsendo_bulk_order_ids'] = $orderIds;

            Tools::redirect(
                $this->context->link->getAdminLink('AdminAlsendoBulkSend')
            );
        }

        parent::postProcess();
    }
}
