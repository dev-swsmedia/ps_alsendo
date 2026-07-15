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

// PS 8.x routes admin controllers directly without always instantiating the
// Module class for the current request — meaning Alsendo::__construct() (and
// thus bootstrapAutoloader()) may not fire. Pull the main module file here and
// run bootstrap explicitly so vendor isolation is applied before any class
// from alsendo/vendor/ can leak into PrestaShop's global ClassLoader (most
// notably nikic/php-parser v5, which would otherwise shadow PS's v4 API).
$_alsendoModulePath = _PS_MODULE_DIR_ . 'alsendo/alsendo.php';
if (file_exists($_alsendoModulePath)) {
    require_once $_alsendoModulePath;
}
if (class_exists('Alsendo', false) && method_exists('Alsendo', 'bootstrapAutoloader')) {
    Alsendo::bootstrapAutoloader();
}

require_once _PS_MODULE_DIR_ . 'alsendo/src/Services/MapBridge.php';

use Alsendo\AlsendoWrapper\Api\Ecolet\EcoletOAuthClient;
use Alsendo\Services\MapBridge;
use Alsendo\Services\WrapperService;

class AdminAlsendoModuleConfigurationController extends ModuleAdminController
{
    public function displayAjax()
    {
        $this->postProcess();
    }

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function initContent()
    {
        parent::initContent();

        $region = (Configuration::get('ALSENDO_REGION') ?: 'pl');
        $token = Configuration::get('ALSENDO_TOKEN', null, null, null, '');
        $secret = Configuration::get('ALSENDO_SECRET', null, null, null, '');
        $app_id = Configuration::get('ALSENDO_APP_ID', null, null, null, '');
        $api_key = Configuration::get('ALSENDO_API_KEY', null, null, null, '');
        $sender_list = Configuration::get('ALSENDO_SENDER_LIST', null, null, null, '');
        $shipping_settings_list = Configuration::get('ALSENDO_SHIPPING_SETTINGS_LIST', null, null, null, '');

        $senderData = json_decode($sender_list, true);
        if (is_array($senderData) && !empty($senderData)) {
            $hasMain = false;
            foreach ($senderData as $sender) {
                if (!empty($sender['main'])) {
                    $hasMain = true;
                    break;
                }
            }
            if (!$hasMain) {
                $senderData[0]['main'] = true;
                $sender_list = json_encode($senderData);
                Configuration::updateValue('ALSENDO_SENDER_LIST', $sender_list);
            }
        }

        $shippingData = json_decode($shipping_settings_list, true);
        if (is_array($shippingData) && !empty($shippingData)) {
            $hasMain = false;
            foreach ($shippingData as $shipping) {
                if (!empty($shipping['main'])) {
                    $hasMain = true;
                    break;
                }
            }
            if (!$hasMain) {
                $shippingData[0]['main'] = true;
                $shipping_settings_list = json_encode($shippingData);
                Configuration::updateValue('ALSENDO_SHIPPING_SETTINGS_LIST', $shipping_settings_list);
            }
        }

        $mapBridge = new MapBridge();
        $mapData = $mapBridge->getMapTemplateDataSafe($region);
        $pickupOperators = $mapBridge->getPickupOperatorsForTemplate($region);

        $roClientId = Configuration::get('ALSENDO_RO_CLIENT_ID') ?: '';
        $roClientSecret = Configuration::get('ALSENDO_RO_CLIENT_SECRET') ?: '';
        $roOauthAccessToken = Configuration::get('ALSENDO_RO_OAUTH_ACCESS_TOKEN') ?: '';

        $scheme = (Tools::usingSecureMode() ? 'https' : 'http');
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $ecoletBaseUrl = $scheme . '://' . $host;
        $ecoletRedirectUrl = $ecoletBaseUrl . '/index.php?fc=module&module=alsendo&controller=ecoletauth';
        $ecoletIsLocalhost = str_contains($ecoletBaseUrl, 'localhost') || str_contains($ecoletBaseUrl, '127.0.0.1');

        $addressBook = [];
        if ($region === 'cz') {
            try {
                require_once _PS_MODULE_DIR_ . 'alsendo/src/Services/WrapperService.php';
                $wrapperService = new WrapperService();
                $result = $wrapperService->getAddressBookList();
                if ($result->isSuccess()) {
                    $addressBook = $result->getData()['addresses'] ?? [];
                }
            } catch (Throwable $e) {
            }
        }

        $this->context->smarty->assign([
            'alsendo_region' => $region,
            'alsendo_address_book' => $addressBook,
            'alsendo_token' => $token,
            'alsendo_secret' => $secret,
            'alsendo_app_id' => $app_id,
            'alsendo_api_key' => $api_key,
            'alsendo_sender_list' => $sender_list,
            'alsendo_shipping_settings_list' => $shipping_settings_list,
            'available_tags' => $this->getAvailableTags(),
            'alsendo_pickup_operators' => $pickupOperators,
            'alsendo_map_css_url' => $mapData['css_url'],
            'alsendo_map_js_url' => $mapData['js_url'],
            'alsendo_map_modal_container_id' => $mapData['container_id'],
            'alsendo_map_config' => $mapData['config_json'],
            'alsendo_test_mode' => (bool) Configuration::get('ALSENDO_TEST_MODE', null, null, null, true),
            'alsendo_default_pickup_hours_from' => Configuration::get('ALSENDO_DEFAULT_PICKUP_HOURS_FROM', null, null, null, '08:00'),
            'alsendo_default_pickup_hours_to' => Configuration::get('ALSENDO_DEFAULT_PICKUP_HOURS_TO', null, null, null, '17:00'),
            'alsendo_msg_saved' => $this->trans('Saved!'),
            'alsendo_msg_error' => $this->trans('Error'),
            'alsendo_msg_set_default' => $this->trans('Set as default!'),
            'alsendo_ro_client_id' => $roClientId,
            'alsendo_ro_client_secret' => $roClientSecret,
            'alsendo_ecolet_redirect_url' => $ecoletRedirectUrl,
            'alsendo_ecolet_is_localhost' => $ecoletIsLocalhost,
            'alsendo_ecolet_authorized' => !empty($roOauthAccessToken),
            'alsendo_auto_declared_value' => (bool) Configuration::get('ALSENDO_AUTO_DECLARED_VALUE'),
            'alsendo_same_day_pickup' => (bool) Configuration::get('ALSENDO_SAME_DAY_PICKUP'),
            'alsendo_package_types' => self::getPackageTypesForRegion(Configuration::get('ALSENDO_REGION') ?: 'pl'),
        ]);

        $this->setTemplate('module_configuration.tpl');
    }

    public function postProcess()
    {
        if (Tools::getIsset('ajax') && Tools::getIsset('action')) {
            $action = Tools::getValue('action');
            $result = ['error' => false, 'message' => $this->trans('Saved!')];

            switch ($action) {
                case 'save_region':
                    $region = Tools::getValue('region');
                    if (!in_array($region, ['pl', 'cz', 'ro'])) {
                        $result = [
                            'error' => true,
                            'message' => $this->trans('Invalid region'),
                            'errors' => ['region' => $this->trans('Invalid region')],
                        ];
                    } else {
                        Configuration::updateValue('ALSENDO_REGION', $region);
                    }
                    break;
                case 'save_apikeys':
                    $payload = [
                        'alsendo_token' => Tools::getValue('alsendo_token'),
                        'alsendo_secret' => Tools::getValue('alsendo_secret'),
                        'alsendo_app_id' => Tools::getValue('alsendo_app_id'),
                        'alsendo_api_key' => Tools::getValue('alsendo_api_key'),
                        'alsendo_ro_client_id' => Tools::getValue('alsendo_ro_client_id'),
                        'alsendo_ro_client_secret' => Tools::getValue('alsendo_ro_client_secret'),
                        'region' => (Configuration::get('ALSENDO_REGION') ?: 'pl'),
                    ];
                    list($errors, $clean) = $this->validate('apikeys', $payload);
                    if (!empty($errors)) {
                        $result = [
                            'error' => true,
                            'message' => $this->trans('Validation error'),
                            'errors' => $errors,
                        ];
                        break;
                    }
                    Configuration::updateValue(
                        'ALSENDO_TOKEN',
                        (string) $clean['alsendo_token']
                    );
                    Configuration::updateValue(
                        'ALSENDO_SECRET',
                        (string) $clean['alsendo_secret']
                    );
                    Configuration::updateValue(
                        'ALSENDO_APP_ID',
                        (string) $clean['alsendo_app_id']
                    );
                    Configuration::updateValue(
                        'ALSENDO_API_KEY',
                        (string) $clean['alsendo_api_key']
                    );
                    Configuration::updateValue(
                        'ALSENDO_RO_CLIENT_ID',
                        (string) $clean['alsendo_ro_client_id']
                    );
                    Configuration::updateValue(
                        'ALSENDO_RO_CLIENT_SECRET',
                        (string) $clean['alsendo_ro_client_secret']
                    );
                    break;
                case 'save_config':
                    $region = Tools::getValue('region');
                    if (!in_array($region, ['pl', 'cz', 'ro'])) {
                        $result = [
                            'error' => true,
                            'message' => $this->trans('Invalid region'),
                            'errors' => ['region' => $this->trans('Invalid region')],
                        ];
                        break;
                    }

                    $payload = [
                        'alsendo_token' => Tools::getValue('alsendo_token'),
                        'alsendo_secret' => Tools::getValue('alsendo_secret'),
                        'alsendo_app_id' => Tools::getValue('alsendo_app_id'),
                        'alsendo_api_key' => Tools::getValue('alsendo_api_key'),
                        'alsendo_ro_client_id' => Tools::getValue('alsendo_ro_client_id'),
                        'alsendo_ro_client_secret' => Tools::getValue('alsendo_ro_client_secret'),
                        'region' => $region,
                    ];

                    list($errors, $clean) = $this->validate('apikeys', $payload);
                    if (!empty($errors)) {
                        $result = [
                            'error' => true,
                            'message' => $this->trans('Validation error'),
                            'errors' => $errors,
                        ];
                        break;
                    }

                    Configuration::updateValue('ALSENDO_REGION', $region);
                    Configuration::updateValue('ALSENDO_TOKEN', (string) $clean['alsendo_token']);
                    Configuration::updateValue('ALSENDO_SECRET', (string) $clean['alsendo_secret']);
                    Configuration::updateValue('ALSENDO_APP_ID', (string) $clean['alsendo_app_id']);
                    Configuration::updateValue('ALSENDO_API_KEY', (string) $clean['alsendo_api_key']);
                    Configuration::updateValue('ALSENDO_RO_CLIENT_ID', (string) $clean['alsendo_ro_client_id']);
                    Configuration::updateValue('ALSENDO_RO_CLIENT_SECRET', (string) $clean['alsendo_ro_client_secret']);

                    $testMode = (int) Tools::getValue('alsendo_test_mode', 0);
                    Configuration::updateValue('ALSENDO_TEST_MODE', $testMode);
                    break;
                case 'save_settings':
                    $list = Tools::getValue('alsendo_sender_list');
                    $decoded = json_decode($list, true);
                    list($errors, $clean) = $this->validate(
                        'sender_templates',
                        is_array($decoded) ? $decoded : []
                    );
                    if (!empty($errors)) {
                        $result = [
                            'error' => true,
                            'message' => $this->trans('Validation error'),
                            'errors' => $errors,
                        ];
                        break;
                    }
                    Configuration::updateValue(
                        'ALSENDO_SENDER_LIST',
                        json_encode($clean)
                    );
                    break;
                case 'save_shipping_settings':
                    $shipping_list = Tools::getValue(
                        'alsendo_shipping_settings_list'
                    );
                    $decoded = json_decode($shipping_list, true);
                    list($errors, $clean) = $this->validate(
                        'shipping_templates',
                        is_array($decoded) ? $decoded : []
                    );
                    if (!empty($errors)) {
                        $result = [
                            'error' => true,
                            'message' => $this->trans('Validation error'),
                            'errors' => $errors,
                        ];
                        break;
                    }
                    Configuration::updateValue(
                        'ALSENDO_SHIPPING_SETTINGS_LIST',
                        json_encode($clean)
                    );
                    break;
                case 'save_additional_settings':
                    $hasPickupPoints = Tools::getIsset('pickup_operators');
                    $hasPickupHours = Tools::getIsset('default_pickup_hours_from');

                    if ($hasPickupPoints) {
                        $pickupRegion = (Configuration::get('ALSENDO_REGION') ?: 'pl');
                        $mb = new MapBridge();
                        $carriers = $mb->getDefaultPickupOperators($pickupRegion);
                        foreach ($carriers as $carrier) {
                            $key = 'pickup_' . $carrier;
                            $display_key = $key . '_display';

                            $value = Tools::getValue($key);
                            $display_value = Tools::getValue($display_key);

                            Configuration::updateValue(
                                $mb->getPickupConfigKey($carrier),
                                $value
                            );
                            Configuration::updateValue(
                                $mb->getPickupConfigKey($carrier) . '_DISPLAY',
                                $display_value
                            );
                        }
                    }

                    if (Tools::getIsset('auto_declared_value')) {
                        Configuration::updateValue('ALSENDO_AUTO_DECLARED_VALUE', (int) Tools::getValue('auto_declared_value', 0));
                    }

                    if (Tools::getIsset('same_day_pickup')) {
                        Configuration::updateValue('ALSENDO_SAME_DAY_PICKUP', (int) Tools::getValue('same_day_pickup', 0));
                    }

                    if ($hasPickupHours) {
                        $hoursFrom = Tools::getValue('default_pickup_hours_from', '08:00');
                        $hoursTo = Tools::getValue('default_pickup_hours_to', '17:00');

                        $pickupErrors = [];
                        if ($hoursFrom < '08:00') {
                            $pickupErrors['default_pickup_hours_from'] = $this->trans('Pickup start time cannot be earlier than 08:00');
                        }
                        if ($hoursTo > '17:00') {
                            $pickupErrors['default_pickup_hours_to'] = $this->trans('Pickup end time cannot be later than 17:00');
                        }
                        if ($hoursFrom >= $hoursTo) {
                            $pickupErrors['default_pickup_hours_to'] = $this->trans('Pickup end time must be after start time');
                        }

                        $fromParts = explode(':', $hoursFrom);
                        $toParts = explode(':', $hoursTo);
                        $fromMinutes = ((int) $fromParts[0] * 60) + (int) ($fromParts[1] ?? 0);
                        $toMinutes = ((int) $toParts[0] * 60) + (int) ($toParts[1] ?? 0);
                        if (($toMinutes - $fromMinutes) < 120) {
                            $pickupErrors['default_pickup_hours_from'] = $this->trans('Minimum pickup time window is 2 hours');
                        }

                        if (!empty($pickupErrors)) {
                            $result = [
                                'error' => true,
                                'message' => $this->trans('Validation failed'),
                                'errors' => $pickupErrors,
                            ];
                            break;
                        }

                        Configuration::updateValue('ALSENDO_DEFAULT_PICKUP_HOURS_FROM', pSQL($hoursFrom));
                        Configuration::updateValue('ALSENDO_DEFAULT_PICKUP_HOURS_TO', pSQL($hoursTo));
                    }

                    $result = [
                        'success' => true,
                        'message' => $hasPickupHours
                            ? $this->trans('Default pickup hours saved successfully')
                            : $this->trans('Saved!'),
                    ];
                    break;
                case 'get_pickup_operators_for_region':
                    $newRegion = Tools::getValue('region', 'pl');
                    if (!in_array($newRegion, ['pl', 'cz', 'ro'])) {
                        $newRegion = 'pl';
                    }
                    $mb = new MapBridge();
                    $operators = $mb->getPickupOperatorsForTemplate($newRegion);
                    $mapConfig = $mb->getRegionConfig($newRegion);

                    $operatorsHtml = '';
                    foreach ($operators as $op) {
                        $key = htmlspecialchars($op['key'], ENT_QUOTES, 'UTF-8');
                        $label = htmlspecialchars($op['label'], ENT_QUOTES, 'UTF-8');
                        $value = htmlspecialchars($op['value'], ENT_QUOTES, 'UTF-8');
                        $display = htmlspecialchars($op['display'], ENT_QUOTES, 'UTF-8');
                        $operatorsHtml .= '<div class="form-group alsendo-pickup-row" style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">';
                        $operatorsHtml .= '<label style="min-width:120px;">' . $label . '</label>';
                        $operatorsHtml .= '<input type="hidden" id="alsendo_pickup_' . $key . '" value="' . $value . '">';
                        $operatorsHtml .= '<input type="text" class="form-control" id="alsendo_pickup_' . $key . '_display" readonly value="' . $display . '">';
                        $operatorsHtml .= '<button class="btn btn-primary btn-sm alsendo-choose-pickup" data-carrier="' . $key . '" type="button">' . $this->trans('Select') . '</button>';
                        $operatorsHtml .= '<button class="btn btn-danger btn-sm alsendo-clear-pickup" data-carrier="' . $key . '" type="button">' . $this->trans('Remove') . '</button>';
                        $operatorsHtml .= '</div>';
                    }

                    $result = [
                        'success' => true,
                        'operators_html' => $operatorsHtml,
                        'map_config' => $mapConfig,
                    ];
                    break;
                case 'get_package_types_for_region':
                    $newRegion = Tools::getValue('region', 'pl');
                    if (!in_array($newRegion, ['pl', 'cz', 'ro'])) {
                        $newRegion = 'pl';
                    }
                    $result = [
                        'success' => true,
                        'package_types' => self::getPackageTypesForRegion($newRegion),
                    ];
                    break;
                case 'ecolet_authorize_url':
                    $roClientId = Configuration::get('ALSENDO_RO_CLIENT_ID');
                    $roClientSecret = Configuration::get('ALSENDO_RO_CLIENT_SECRET');
                    if (empty($roClientId) || empty($roClientSecret)) {
                        $result = [
                            'error' => true,
                            'message' => $this->trans('Ecolet Client ID and Client Secret are required. Save them first.'),
                        ];
                        break;
                    }

                    $scheme = (Tools::usingSecureMode() ? 'https' : 'http');
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    $baseUrl = $scheme . '://' . $host;
                    $redirectUri = $baseUrl . '/index.php?fc=module&module=alsendo&controller=ecoletauth';
                    if (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
                        $redirectUri = 'https://google.com';
                    }

                    $state = md5(substr($roClientSecret, 0, 10));
                    $isTestMode = (bool) Configuration::get('ALSENDO_TEST_MODE', null, null, null, false);

                    // Vendor already loaded via top-of-file bootstrapAutoloader().
                    $oauthConfig = [
                        'client_id' => $roClientId,
                        'client_secret' => $roClientSecret,
                        'redirect_uri' => $redirectUri,
                    ];
                    $oauthClient = new EcoletOAuthClient($oauthConfig, $isTestMode);
                    $url = $oauthClient->getAuthorizationUrl($state);

                    $result = ['url' => $url];
                    break;
                default:
                    $result = [
                        'error' => true,
                        'message' => $this->trans('Unknown action'),
                    ];
            }

            header('Content-Type: application/json');
            exit(json_encode($result));
        }
    }

    private function validate(string $action, $data): array
    {
        $errors = [];
        $clean = [];

        if ($action === 'apikeys') {
            $region = strtolower((string) ($data['region'] ?? 'pl'));
            $appId = trim((string) ($data['alsendo_app_id'] ?? ''));
            $appSecret = trim((string) ($data['alsendo_secret'] ?? ''));
            $token = trim((string) ($data['alsendo_token'] ?? ''));
            $apiKey = trim((string) ($data['alsendo_api_key'] ?? ''));
            $roClientId = trim((string) ($data['alsendo_ro_client_id'] ?? ''));
            $roClientSecret = trim((string) ($data['alsendo_ro_client_secret'] ?? ''));

            if ($region === 'pl') {
                if ($appId === '') {
                    $errors['alsendo_app_id'] = $this->trans('App ID is required');
                }
                if ($appSecret === '') {
                    $errors['alsendo_secret'] = $this->trans('App Secret is required');
                }
            } elseif ($region === 'ro') {
                if ($roClientId === '' || $roClientSecret === '') {
                    $errors['alsendo_ro_client_id'] = $this->trans('Client ID and Client Secret are required for Romania');
                }
            } elseif ($region === 'cz') {
                if ($apiKey === '') {
                    $errors['alsendo_api_key'] = $this->trans('API Key is required');
                }
            }

            $clean = [
                'alsendo_app_id' => $appId,
                'alsendo_secret' => $appSecret,
                'alsendo_token' => $token,
                'alsendo_api_key' => $apiKey,
                'alsendo_ro_client_id' => $roClientId,
                'alsendo_ro_client_secret' => $roClientSecret,
            ];
        }

        if ($action === 'sender_templates') {
            if (!is_array($data)) {
                $data = [];
            }
            $names = [];
            $out = [];
            foreach ($data as $i => $tpl) {
                $idx = "sender[$i]";

                $templateName = trim((string) ($tpl['template_name'] ?? ''));
                $company = trim((string) ($tpl['company'] ?? ''));
                $firstname = trim((string) ($tpl['firstname'] ?? ''));
                $lastname = trim((string) ($tpl['lastname'] ?? ''));
                $street = trim((string) ($tpl['street'] ?? ''));
                $building = trim((string) ($tpl['building'] ?? ''));
                $apartment = trim((string) ($tpl['apartment'] ?? ''));
                $block = trim((string) ($tpl['block'] ?? ''));
                $entrance = trim((string) ($tpl['entrance'] ?? ''));
                $floor = trim((string) ($tpl['floor'] ?? ''));
                $flat = trim((string) ($tpl['flat'] ?? ''));
                $postal = trim((string) ($tpl['postal'] ?? ''));
                $city = trim((string) ($tpl['city'] ?? ''));
                $regionCountryMap = ['pl' => 'PL', 'cz' => 'CZ', 'ro' => 'RO'];
                $regionFallback = $regionCountryMap[Configuration::get('ALSENDO_REGION') ?: 'pl'] ?? 'PL';
                $country = strtoupper(trim((string) ($tpl['country'] ?? $regionFallback)));
                $contact = trim((string) ($tpl['contact'] ?? ''));
                $phone = preg_replace(
                    '/\s+/',
                    '',
                    (string) ($tpl['phone'] ?? '')
                );
                $email = trim((string) ($tpl['email'] ?? ''));
                $bank = strtoupper(
                    str_replace(' ', '', (string) ($tpl['bank'] ?? ''))
                );
                $addressType = strtolower(
                    (string) ($tpl['address_type'] ?? 'company')
                );

                if ($templateName === '') {
                    $errors["$idx.template_name"] = $this->trans('Template name is required');
                }
                if (isset($names[$templateName])) {
                    $errors["$idx.template_name"] =
                        $this->trans('Template name must be unique');
                }
                $names[$templateName] = true;

                if (!in_array($addressType, ['company', 'home'])) {
                    $errors["$idx.address_type"] = $this->trans('Address type invalid');
                }
                if ($addressType === 'company') {
                    if ($company === '' || strlen($company) > 50) {
                        $errors["$idx.company"] =
                            $this->trans('Company required, max 50 characters');
                    }
                } elseif (strlen($company) > 50) {
                    $errors["$idx.company"] = $this->trans('Company name too long, max 50 characters');
                }
                if ($firstname === '' || strlen($firstname) > 50) {
                    $errors["$idx.firstname"] =
                        $this->trans('First name required, max 50 characters');
                }
                if ($lastname === '' || strlen($lastname) > 50) {
                    $errors["$idx.lastname"] =
                        $this->trans('Last name required, max 50 characters');
                }
                if ($street === '' || strlen($street) > 50) {
                    $errors["$idx.street"] =
                        $this->trans('Street required, max 50 characters');
                }
                if ($building === '' || strlen($building) > 10) {
                    $errors["$idx.building"] =
                        $this->trans('Building number required, max 10 characters');
                }
                if ($apartment !== '' && strlen($apartment) > 10) {
                    $errors["$idx.apartment"] = $this->trans('Apartment number too long');
                }
                if ($postal === '' || strlen($postal) > 50) {
                    $errors["$idx.postal"] =
                        $this->trans('Postal code required, max 50 characters');
                }
                if ($city === '' || strlen($city) > 50) {
                    $errors["$idx.city"] =
                        $this->trans('City required, max 50 characters');
                }
                if (!preg_match('/^[A-Z]{2}$/', $country)) {
                    $errors["$idx.country"] = $this->trans('Country must be ISO2');
                }
                if ($contact === '' || strlen($contact) > 50) {
                    $errors["$idx.contact"] =
                        $this->trans('Contact person required, max 50 characters');
                }
                if (
                    $phone === ''
                    || !preg_match('/^\+?\d+$/', $phone)
                    || strlen($phone) > 16
                ) {
                    $errors["$idx.phone"] = $this->trans('Phone must be digits, max 16');
                }
                if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors["$idx.email"] = $this->trans('Invalid email');
                }

                $out[] = [
                    'template_name' => $templateName,
                    'address_type' => $addressType,
                    'company' => $company,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'street' => $street,
                    'building' => $building,
                    'apartment' => $apartment,
                    'block' => $block,
                    'entrance' => $entrance,
                    'floor' => $floor,
                    'flat' => $flat,
                    'postal' => $postal,
                    'city' => $city,
                    'country' => $country,
                    'contact' => $contact,
                    'phone' => $phone,
                    'email' => $email,
                    'bank' => $bank,
                    'bank_code' => trim((string) ($tpl['bank_code'] ?? '')),
                    'additional_bank_account_number' => trim((string) ($tpl['additional_bank_account_number'] ?? '')),
                    'external_id' => trim((string) ($tpl['external_id'] ?? '')),
                    'main' => !empty($tpl['main']) ? true : false,
                ];
            }
            $mainCount = 0;
            foreach ($out as $c) {
                if (!empty($c['main'])) {
                    ++$mainCount;
                }
            }
            if ($mainCount > 1) {
                $errors['sender_main'] = 'Only one sender template can be main';
            }
            $clean = $out;
        }

        if ($action === 'shipping_templates') {
            if (!is_array($data)) {
                $data = [];
            }
            $names = [];
            $out = [];
            foreach ($data as $i => $tpl) {
                $idx = "package[$i]";

                $templateName = trim((string) ($tpl['alsendo_template_name'] ?? ''));
                $cfgDefaultPkg = (($region ?? (Configuration::get('ALSENDO_REGION') ?: 'pl')) === 'cz') ? 'PACKAGE' : ((($region ?? (Configuration::get('ALSENDO_REGION') ?: 'pl')) === 'ro') ? 'package' : 'PACZKA');
                $ptype = trim((string) ($tpl['alsendo_package_type'] ?? $cfgDefaultPkg));
                $w = (float) ($tpl['alsendo_width'] ?? 0);
                $l = (float) ($tpl['alsendo_length'] ?? 0);
                $h = (float) ($tpl['alsendo_height'] ?? 0);
                $we = (float) ($tpl['alsendo_weight'] ?? 0);

                $cod = isset($tpl['alsendo_cod']) && $tpl['alsendo_cod'] !== ''
                    ? (float) $tpl['alsendo_cod']
                    : null;

                $decl = isset($tpl['alsendo_declared_value']) && $tpl['alsendo_declared_value'] !== ''
                    ? (float) $tpl['alsendo_declared_value']
                    : null;

                $content = trim(
                    (string) ($tpl['alsendo_shipment_content'] ?? '')
                );
                $pickup = strtolower(
                    trim((string) ($tpl['alsendo_pickup_type'] ?? 'courier'))
                );

                if ($templateName === '') {
                    $errors["$idx.template_name"] = $this->trans('Template name is required');
                }
                if (isset($names[$templateName])) {
                    $errors["$idx.template_name"] =
                        $this->trans('Template name must be unique');
                }
                $names[$templateName] = true;

                if (empty($ptype)) {
                    $errors["$idx.package_type"] = $this->trans('Package type is required');
                }
                if ($w <= 0) {
                    $errors["$idx.width"] = $this->trans('Width must be greater than 0');
                }
                if ($l <= 0) {
                    $errors["$idx.length"] = $this->trans('Length must be greater than 0');
                }
                if ($h <= 0) {
                    $errors["$idx.height"] = $this->trans('Height must be greater than 0');
                }
                if ($we <= 0) {
                    $errors["$idx.weight"] = $this->trans('Weight must be greater than 0');
                }

                if ($cod !== null && $cod < 0) {
                    $errors["$idx.cod"] = $this->trans('COD cannot be negative');
                }

                if ($decl !== null && $decl < 0) {
                    $errors["$idx.declared_value"] = $this->trans('Declared value cannot be negative');
                }

                if (
                    !in_array(
                        $pickup,
                        ['self', 'courier', 'regular', 'occasional', 'on_demand', 'no_pickup']
                    )
                ) {
                    $errors["$idx.pickup_type"] = $this->trans('Invalid pickup type');
                }

                $isNstd = (int) ($tpl['alsendo_is_nstd'] ?? 0);

                $out[] = [
                    'alsendo_template_name' => $templateName,
                    'alsendo_package_type' => $ptype,
                    'alsendo_is_nstd' => $isNstd,
                    'alsendo_width' => $w,
                    'alsendo_length' => $l,
                    'alsendo_height' => $h,
                    'alsendo_weight' => $we,
                    'alsendo_cod' => $cod,
                    'alsendo_declared_value' => $decl,
                    'alsendo_shipment_content' => $content,
                    'alsendo_pickup_type' => $pickup,
                    'main' => !empty($tpl['main']) ? true : false,
                ];
            }
            $mainCount = 0;
            foreach ($out as $c) {
                if (!empty($c['main'])) {
                    ++$mainCount;
                }
            }
            if ($mainCount > 1) {
                $errors['package_main'] = 'Only one package template can be main';
            }
            $clean = $out;
        }

        return [$errors, $clean];
    }

    private function getAvailableTags(): array
    {
        return [
            '{order_id}' => 'Order ID',
            '{product_id}' => 'Product ID',
            '{product_name}' => 'Product Name',
            '{invoice_number}' => 'Invoice Number',
            '{custom_text}' => 'Custom Text',
        ];
    }

    public static function getPackageTypesForRegion(string $region): array
    {
        $defaults = [
            'pl' => [
                ['type' => 'LIST', 'desc' => 'List'],
                ['type' => 'PACZKA', 'desc' => 'Paczka'],
                ['type' => 'PALETA', 'desc' => 'Paleta euro 120x80'],
                ['type' => 'POLPALETA', 'desc' => 'Półpaleta 60x80'],
                ['type' => 'PALETA_60X40', 'desc' => 'Ćwierćpaleta 60x40'],
                ['type' => 'PALETA_PRZEMYSLOWA', 'desc' => 'Paleta przemysłowa 120x100'],
                ['type' => 'PALETA_PRZEMYSLOWA_B', 'desc' => 'Paleta przemysłowa 120x120'],
            ],
            'cz' => [
                ['type' => 'PACKAGE', 'desc' => 'Package'],
                ['type' => 'LETTER', 'desc' => 'Letter'],
                ['type' => 'PALLET', 'desc' => 'Pallet'],
            ],
            'ro' => [
                ['type' => 'package', 'desc' => 'Package'],
                ['type' => 'envelope', 'desc' => 'Envelope'],
                ['type' => 'pallet', 'desc' => 'Pallet'],
            ],
        ];

        $result = $defaults[$region] ?? $defaults['pl'];
        $knownTypes = array_column($result, 'type');

        $cached = Configuration::get('ALSENDO_PACKAGE_TYPES');
        if ($cached) {
            $decoded = json_decode($cached, true);
            if (is_array($decoded)) {
                foreach ($decoded as $entry) {
                    if (!empty($entry['type']) && !in_array($entry['type'], $knownTypes, true)) {
                        $result[] = $entry;
                        $knownTypes[] = $entry['type'];
                    }
                }
            }
        }

        return $result;
    }
}
