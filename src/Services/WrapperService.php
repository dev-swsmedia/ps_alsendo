<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\Services;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'alsendo/src/DTO/FullOrderDTO.php';
require_once _PS_MODULE_DIR_ . 'alsendo/src/Models/WrapperResult.php';
require_once _PS_MODULE_DIR_ . 'alsendo/src/Models/Config.php';
require_once _PS_MODULE_DIR_ . 'alsendo/src/Services/MapBridge.php';
require_once _PS_MODULE_DIR_ . 'alsendo/src/Services/OrderDetailsService.php';
use Alsendo\AlsendoWrapper\Api\Ecolet\EcoletOAuthClient;
use Alsendo\AlsendoWrapper\ApiWrapper;
use Alsendo\AlsendoWrapper\Exception\ResponseException;
use Alsendo\AlsendoWrapper\Exception\ValidationException;
use Alsendo\AlsendoWrapper\Model\Address;
use Alsendo\AlsendoWrapper\Model\Cod;
use Alsendo\AlsendoWrapper\Model\Contact;
use Alsendo\AlsendoWrapper\Model\Notification;
use Alsendo\AlsendoWrapper\Model\NotificationDetail;
use Alsendo\AlsendoWrapper\Model\Order\OrderRequest;
use Alsendo\AlsendoWrapper\Model\Order\SendOrderResponse;
use Alsendo\AlsendoWrapper\Model\Order\Valuation\OrderValuationResponse;
use Alsendo\AlsendoWrapper\Model\Pickup;
use Alsendo\AlsendoWrapper\Model\Shipment;
use Alsendo\DTO\FullOrderDTO;
use Alsendo\Models\Config;
use Alsendo\Models\WrapperResult;

class WrapperService
{
    private static bool $autoloaderLoaded = false;

    private Config $config;

    private static function loadAutoloader(): void
    {
        if (self::$autoloaderLoaded) {
            return;
        }

        // Delegate to the module-level bootstrap so vendor isolation runs identically
        // whether the entrypoint is the main module class, a hook handler, or this
        // service instantiated from a front controller.
        $modulePath = _PS_MODULE_DIR_ . 'alsendo/alsendo.php';
        if (file_exists($modulePath)) {
            require_once $modulePath;
        }
        if (class_exists('Alsendo', false) && method_exists('Alsendo', 'bootstrapAutoloader')) {
            \Alsendo::bootstrapAutoloader();
            self::$autoloaderLoaded = true;
        }
    }

    public function __construct()
    {
        self::loadAutoloader();
        $this->config = $this->loadConfig();
    }

    private function loadConfig(): Config
    {
        $region = (\Configuration::get('ALSENDO_REGION') ?: 'pl');
        $apiConfig = [
            'app_id' => \Configuration::get('ALSENDO_APP_ID', null, null, null, ''),
            'app_secret' => \Configuration::get('ALSENDO_SECRET', null, null, null, ''),
            'token' => \Configuration::get('ALSENDO_TOKEN', null, null, null, ''),
            'api_key' => \Configuration::get('ALSENDO_API_KEY', null, null, null, ''),
            'ro_client_id' => \Configuration::get('ALSENDO_RO_CLIENT_ID', null, null, null, ''),
            'ro_client_secret' => \Configuration::get('ALSENDO_RO_CLIENT_SECRET', null, null, null, ''),
            'ro_oauth_access_token' => \Configuration::get('ALSENDO_RO_OAUTH_ACCESS_TOKEN', null, null, null, ''),
            'ro_oauth_refresh_token' => \Configuration::get('ALSENDO_RO_OAUTH_REFRESH_TOKEN', null, null, null, ''),
            'ro_oauth_expires_at' => \Configuration::get('ALSENDO_RO_OAUTH_EXPIRES_AT', null, null, null, ''),
        ];

        $config = new Config($region, $apiConfig);

        if ($region === 'ro') {
            $this->refreshEcoletTokenIfNeeded($config);
        }

        return $config;
    }

    private function refreshEcoletTokenIfNeeded(Config $config): void
    {
        $expiresAt = (int) $config->getApiConfig()['ro_oauth_expires_at'];
        $refreshToken = $config->getApiConfig()['ro_oauth_refresh_token'] ?? '';

        if (empty($refreshToken) || $expiresAt === 0) {
            return;
        }

        if (time() <= ($expiresAt - 60)) {
            return;
        }

        try {
            self::loadAutoloader();

            $clientId = $config->getRoClientId();
            $clientSecret = $config->getRoClientSecret();
            if (empty($clientId) || empty($clientSecret)) {
                return;
            }

            $isTestMode = (bool) \Configuration::get('ALSENDO_TEST_MODE', null, null, null, false);
            $oauthConfig = [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => 'https://google.com',
            ];

            $oauthClient = new EcoletOAuthClient($oauthConfig, $isTestMode);
            $token = $oauthClient->refreshAccessToken($refreshToken);

            if ($token) {
                \Configuration::updateValue('ALSENDO_RO_OAUTH_ACCESS_TOKEN', $token->getAccessToken());
                \Configuration::updateValue('ALSENDO_RO_OAUTH_REFRESH_TOKEN', $token->refreshToken);
                \Configuration::updateValue('ALSENDO_RO_OAUTH_EXPIRES_AT', (string) $token->expiresAt);

                $updatedApiConfig = $config->getApiConfig();
                $updatedApiConfig['ro_oauth_access_token'] = $token->getAccessToken();
                $updatedApiConfig['ro_oauth_refresh_token'] = $token->refreshToken;
                $updatedApiConfig['ro_oauth_expires_at'] = (string) $token->expiresAt;
                $config->setApiConfig($updatedApiConfig);
            }
        } catch (\Throwable $e) {
            error_log('[Alsendo][refreshEcoletToken] ' . $e->getMessage());
        }
    }

    private function getApiClient()
    {
        if (!$this->config->isComplete()) {
            throw new \Exception('Configuration is incomplete');
        }

        self::loadAutoloader();

        $wrapper = new ApiWrapper(
            $this->config->getWrapperApiName(),
            $this->config->getWrapperConfig(),
            'PrestaShop'
        );

        return $wrapper->getApiClient();
    }

    public function initializeWrapper(): WrapperResult
    {
        try {
            $client = $this->getApiClient();
            $isTestMode = (bool) \Configuration::get('ALSENDO_TEST_MODE', null, null, null, true);
            $environment = $isTestMode ? 'Test Environment' : 'Production';

            return new WrapperResult(true, 'Wrapper initialized successfully', [
                'region' => ucfirst($this->config->getRegion()),
                'client_type' => basename(str_replace('\\', '/', get_class($client))),
                'environment' => $environment,
            ]);
        } catch (\Exception $e) {
            return new WrapperResult(false, 'Failed to initialize wrapper', [], $e->getMessage());
        }
    }

    public function getAvailableServices(): WrapperResult
    {
        try {
            $apiClient = $this->getApiClient();
            $response = $apiClient->getServiceStructure();

            $processed = [];

            if (is_object($response) && !empty($response->services)) {
                foreach ($response->services as $srv) {
                    $processed[] = [
                        'service_id' => $srv->externalId ?? '',
                        'name' => $srv->name ?? '',
                        'supplier' => $srv->supplier ?? '',
                        'to_point' => $srv->toPoint ?? false,
                        'point_to_point' => $srv->pointToPoint ?? null,
                        'door_to_point' => $srv->doorToPoint ?? null,
                        'point_to_door' => $srv->pointToDoor ?? null,
                        'door_to_door' => $srv->doorToDoor ?? null,
                        'logo_url' => $srv->logoUrl ?? null,
                    ];
                }
            }

            if ($this->config->getRegion() === 'ro') {
                foreach ($processed as &$svc) {
                    if ($svc['to_point'] === false) {
                        $slug = strtolower((string) $svc['service_id']);
                        $svc['to_point'] = (
                            str_ends_with($slug, '-locker')
                            || str_ends_with($slug, '-easybox')
                            || str_contains($slug, 'automat')
                        );
                    }
                }
                unset($svc);
            }

            if (!empty($processed)) {
                \Configuration::updateValue('ALSENDO_AVAILABLE_SERVICES', json_encode($processed));
            }

            if (is_object($response) && !empty($response->packageTypes)) {
                $packageTypesData = [];
                foreach ($response->packageTypes as $pt) {
                    $packageTypesData[] = [
                        'type' => $pt->type,
                        'desc' => $pt->desc,
                    ];
                }
                \Configuration::updateValue('ALSENDO_PACKAGE_TYPES', json_encode($packageTypesData));
            }

            return new WrapperResult(true, 'Services retrieved successfully', ['services' => $processed]);
        } catch (\Exception $e) {
            return new WrapperResult(false, 'Service retrieval failed', [], $e->getMessage());
        }
    }

    public function getAddressBookList(): WrapperResult
    {
        try {
            if ($this->config->getRegion() !== 'cz') {
                return new WrapperResult(true, '', ['addresses' => []]);
            }
            $apiClient = $this->getApiClient();
            $response = $apiClient->getAddressBookList();
            $addresses = [];
            foreach ($response->addresses as $id => $addr) {
                $addresses[$id] = [
                    'id' => $addr->id, 'company' => $addr->company ?? '',
                    'firstname' => $addr->firstname ?? '', 'surname' => $addr->surname ?? '',
                    'street' => $addr->street ?? '', 'city' => $addr->city ?? '',
                    'zip' => $addr->zip ?? '', 'country' => $addr->country ?? '',
                    'phone' => $addr->phone ?? '', 'email' => $addr->email ?? '',
                ];
            }

            return new WrapperResult(true, '', ['addresses' => $addresses]);
        } catch (\Exception $e) {
            return new WrapperResult(true, '', ['addresses' => []]);
        }
    }

    public function getOrderValuation(FullOrderDTO $dto): WrapperResult
    {
        try {
            $apiClient = $this->getApiClient();

            $mappedRequest = $this->getOrderRequestFromData($dto);
            try {
                $mappedRequest = $this->prepareOrderRequestForSend($mappedRequest, $dto);
            } catch (\Exception $e) {
            }

            if ($this->config->getRegion() === 'ro') {
                $mappedRequest->serviceId = '';
            }

            if ($this->config->getRegion() === 'cz' && empty($mappedRequest->carrier)) {
                $mapPointId = $mappedRequest->address->receiver->mapPointId ?? '';
                if (preg_match('/^([a-z]+)_[a-z]{2}-/i', $mapPointId, $m)) {
                    $mappedRequest->carrier = strtoupper($m[1]);
                }
                if (empty($mappedRequest->carrier) && !empty($mappedRequest->serviceId)) {
                    $mappedRequest->carrier = strtoupper(preg_replace('/_topoint$/i', '', (string) $mappedRequest->serviceId));
                }
            }
            if ($this->config->getRegion() === 'cz' && !empty($mappedRequest->carrier)) {
                $mappedRequest->carrier = strtoupper($mappedRequest->carrier);
            }

            $response = $apiClient->getOrderValuation($mappedRequest);

            $normalized = [];
            if ($response instanceof OrderValuationResponse) {
                foreach ($response->valuations as $v) {
                    $normalized[$v->serviceId] = $v;
                }
            } else {
                $normalized = (array) $response;
            }

            return new WrapperResult(true, '', $normalized);
        } catch (ValidationException $e) {
            $this->logException($e, 'getOrderValuation');
            $errorDetail = $e->getMessage();
            $fieldErrors = $e->getErrors();
            if (!empty($fieldErrors)) {
                $parts = [];
                foreach ($fieldErrors as $field => $msgs) {
                    $parts[] = $field . ': ' . implode(', ', (array) $msgs);
                }
                $errorDetail .= ' [' . implode('; ', $parts) . ']';
            }

            return new WrapperResult(false, 'Error', [], $errorDetail);
        } catch (ResponseException $e) {
            $this->logException($e, 'getOrderValuation');

            return new WrapperResult(false, 'Error', [], $this->formatResponseException($e));
        } catch (\Exception $e) {
            $this->logException($e, 'getOrderValuation');

            return new WrapperResult(false, 'Error', [], $e->getMessage());
        }
    }

    public function sendOrder(FullOrderDTO $dto, $selectedServiceId = null): WrapperResult
    {
        try {
            $apiClient = $this->getApiClient();

            $mappedRequest = $this->getOrderRequestFromData($dto);
            $mappedRequest = $this->prepareOrderRequestForSend($mappedRequest, $dto);

            if ($selectedServiceId) {
                if ($this->config->getRegion() === 'ro') {
                    $normalized = $this->normalizeEcoletServiceId((string) $selectedServiceId);
                    $mappedRequest->serviceId = $normalized;
                } else {
                    $mappedRequest->serviceId = $selectedServiceId;
                }
            }

            if ($selectedServiceId && $this->config->getRegion() === 'cz') {
                $mappedRequest->carrier = strtoupper(preg_replace('/_topoint$/i', '', (string) $selectedServiceId));
                $mappedRequest->pickup->type = null;
            }

            if ($this->config->getRegion() === 'cz') {
                $mid = $mappedRequest->address->receiver->mapPointId ?? null;
                if (is_string($mid) && preg_match('/^[A-Z]+_[A-Z]{2}-(.+)$/i', $mid, $pm)) {
                    $mappedRequest->address->receiver->mapPointId = $pm[1];
                }
            }

            try {
                $response = $apiClient->sendOrder($mappedRequest);
            } catch (ResponseException $retryEx) {
                $canRetry = (int) \Configuration::get('ALSENDO_SAME_DAY_PICKUP')
                    && isset($mappedRequest->pickup->date)
                    && $mappedRequest->pickup->date === date('Y-m-d')
                    && $this->isPickupDateTimeError($retryEx);

                if ($canRetry) {
                    $tomorrow = date('Y-m-d', strtotime('+1 day'));
                    $mappedRequest->pickup->date = $tomorrow;
                    $response = $apiClient->sendOrder($mappedRequest);
                } else {
                    throw $retryEx;
                }
            }

            if ($this->config->getRegion() === 'ro' && $response->id) {
                try {
                    $orderResponse = $apiClient->getOrder($response->id);
                    if (isset($orderResponse->awb)) {
                        $response->waybillNumber = $orderResponse->awb;

                        $awb = $orderResponse->awb;
                        $response->trackingUrl = 'https://panel.ecolet.ro/ro/track/' . $awb;
                    }
                } catch (\Throwable $e) {
                }
            }

            if ($response instanceof SendOrderResponse) {
                $responseArray = [
                    'id' => $response->id,
                    'service_id' => $response->serviceId,
                    'service_name' => $response->serviceName,
                    'waybill_number' => $response->waybillNumber,
                    'tracking_url' => $response->trackingUrl,
                    'status' => $response->status,
                    'created' => $response->created ? $response->created->format('c') : null,
                ];

                if (in_array($this->config->getRegion(), ['cz', 'ro'], true) && !empty($response->id)) {
                    $responseArray['carrier_tracking_number'] = $response->waybillNumber;
                    $responseArray['waybill_number'] = (string) $response->id;
                }

                if (empty($responseArray['waybill_number']) && !empty($responseArray['id'])) {
                    $responseArray['waybill_number'] = (string) $responseArray['id'];
                }
            } else {
                throw new \Exception('Unexpected API response');
            }

            return new WrapperResult(true, 'Order sent successfully', $responseArray);
        } catch (ValidationException $e) {
            $this->logException($e, 'sendOrder');
            $errorDetail = $e->getMessage();
            $fieldErrors = $e->getErrors();
            if (!empty($fieldErrors)) {
                $parts = [];
                foreach ($fieldErrors as $field => $msgs) {
                    $parts[] = $field . ': ' . implode(', ', (array) $msgs);
                }
                $errorDetail .= ' [' . implode('; ', $parts) . ']';
            }

            return new WrapperResult(false, 'Error', [], $errorDetail);
        } catch (ResponseException $e) {
            $this->logException($e, 'sendOrder');

            return new WrapperResult(false, 'Error', [], $this->formatResponseException($e));
        } catch (\Exception $e) {
            $this->logException($e, 'sendOrder');

            return new WrapperResult(false, 'Error', [], $e->getMessage());
        }
    }

    public function cancelOrder(string $alsendoOrderId): WrapperResult
    {
        try {
            $apiClient = $this->getApiClient();
            $response = $apiClient->cancelOrder($alsendoOrderId);

            return new WrapperResult(true, 'Order cancelled', ['response' => $response]);
        } catch (ValidationException $e) {
            $errorDetail = $e->getMessage();
            $fieldErrors = $e->getErrors();
            if (!empty($fieldErrors)) {
                $parts = [];
                foreach ($fieldErrors as $field => $msgs) {
                    $parts[] = $field . ': ' . implode(', ', (array) $msgs);
                }
                $errorDetail .= ' [' . implode('; ', $parts) . ']';
            }

            return new WrapperResult(false, 'Cancellation failed', [], $errorDetail);
        } catch (\Exception $e) {
            return new WrapperResult(false, 'Cancellation failed', [], $e->getMessage());
        }
    }

    public function getWaybill(string $orderId): WrapperResult
    {
        try {
            $apiClient = $this->getApiClient();
            $response = $apiClient->getWaybill($orderId);

            if (is_object($response) && property_exists($response, 'waybill')) {
                return new WrapperResult(true, '', ['waybill' => $response->waybill, 'type' => $response->type ?? 'pdf']);
            }

            return new WrapperResult(true, '', ['waybill' => $response, 'type' => 'pdf']);
        } catch (\Exception $e) {
            return new WrapperResult(false, 'Error', [], $e->getMessage());
        }
    }

    private function formatBankAccount(string $account): string
    {
        if (empty($account)) {
            return '';
        }

        $account = trim($account);
        $region = $this->config->getRegion();

        if ($region === 'cz') {
            $account = preg_replace('/\s+/', '', $account);
            if (preg_match('/^(\d{0,6}-?\d{1,10})\/\d{4}$/', $account, $matches)) {
                return $matches[1];
            }
            if (preg_match('/^CZ\d{22}$/', strtoupper($account))) {
                return strtoupper($account);
            }

            return $account;
        }

        if ($region === 'ro') {
            $account = preg_replace('/\s+/', '', $account);
            if (preg_match('/^RO\d{2}[A-Z]{4}[A-Z0-9]{16}$/', strtoupper($account))) {
                return strtoupper($account);
            }

            return $account;
        }

        $account = strtoupper($account);

        if (strpos($account, 'PL') === 0) {
            $account = substr($account, 2);
        }

        $account = preg_replace('/\s+/', '', $account);

        if (strlen($account) !== 26) {
            return '';
        }

        $firstTwo = substr($account, 0, 2);
        $rest = substr($account, 2);
        $restFormatted = implode(' ', str_split($rest, 4));
        $formatted = $firstTwo . ' ' . $restFormatted;

        return $formatted;
    }

    private function isPickupDateTimeError(ResponseException $e): bool
    {
        $msg = strtolower($e->getMessage());

        $exactPhrases = ['pickup date', 'pickup time', 'pickup hours', 'range between', 'cut-off', 'godzin', 'too short'];
        foreach ($exactPhrases as $phrase) {
            if (strpos($msg, $phrase) !== false) {
                return true;
            }
        }

        if (strpos($msg, 'pickup') !== false) {
            $timeWords = ['date', 'time', 'hour', 'today', 'tomorrow', 'same day', 'same-day', '15:00', 'cutoff'];
            foreach ($timeWords as $tw) {
                if (strpos($msg, $tw) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isOrlenPaczkaService(?string $serviceId): bool
    {
        if (empty($serviceId) || $this->config->getRegion() !== 'pl') {
            return false;
        }
        $servicesCfg = json_decode(\Configuration::get('ALSENDO_AVAILABLE_SERVICES', null, null, null, ''), true) ?: [];
        foreach ($servicesCfg as $svc) {
            if ((string) ($svc['service_id'] ?? '') === (string) $serviceId) {
                $supplier = strtolower($svc['supplier'] ?? '');
                $name = strtolower($svc['name'] ?? '');

                return strpos($supplier, 'ruch') !== false
                    || strpos($supplier, 'orlen') !== false
                    || strpos($name, 'orlen') !== false
                    || strpos($name, 'ruch') !== false;
            }
        }

        return false;
    }

    private function formatPhoneWithCountryCode(string $phone, string $countryCode): string
    {
        $phone = preg_replace('/[\s\-\(\)\.]+/', '', trim($phone));
        if (empty($phone)) {
            return '';
        }

        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        $prefixMap = [
            'PL' => '48', 'CZ' => '420', 'SK' => '421', 'DE' => '49',
            'RO' => '40', 'HU' => '36', 'AT' => '43', 'NL' => '31',
            'BE' => '32', 'FR' => '33', 'IT' => '39', 'ES' => '34',
            'GB' => '44', 'SE' => '46', 'DK' => '45', 'FI' => '358',
            'NO' => '47', 'CH' => '41', 'IE' => '353', 'PT' => '351',
            'LT' => '370', 'LV' => '371', 'EE' => '372', 'BG' => '359',
            'HR' => '385', 'SI' => '386', 'LU' => '352',
        ];

        $cc = strtoupper($countryCode);
        if (!isset($prefixMap[$cc])) {
            return $phone;
        }

        $prefix = $prefixMap[$cc];

        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }

        if (str_starts_with($phone, $prefix)) {
            return '+' . $phone;
        }

        return '+' . $prefix . $phone;
    }

    private function getOrderRequestFromData(FullOrderDTO $dto): OrderRequest
    {
        if (!$dto->orderDetails) {
            throw new \Exception('Order details are required');
        }

        $od = $dto->orderDetails;
        $oc = $dto->openCartOrder ?? [];

        $region = $this->config->getRegion();
        $currencyMap = ['pl' => 'PLN', 'cz' => 'CZK', 'ro' => 'RON'];
        $currency = $currencyMap[$region] ?? 'PLN';

        $sender = new Contact();
        $regionCountryMap = ['pl' => 'PL', 'cz' => 'CZ', 'ro' => 'RO'];
        $regionCountry = $regionCountryMap[$region] ?? 'PL';
        $senderCountryCode = !empty($od->sender_country) ? strtoupper($od->sender_country) : $regionCountry;

        if ($region === 'cz' && !empty($od->sender_external_id)) {
            $sender->id = (int) $od->sender_external_id;
        }

        $sender->countryCode = $senderCountryCode;
        $sender->country = $senderCountryCode;
        $sender->stateCode = 'YY';
        $sender->name = $od->sender_full_name ?: '';
        $senderStreet = $od->sender_street ?: '';
        $senderBuilding = $od->sender_building_number ?? '';
        $senderApartment = $od->sender_apartment_number ?? '';
        if (!empty($senderBuilding) && strpos($senderStreet, $senderBuilding) === false) {
            $senderStreet = trim($senderStreet . ' ' . $senderBuilding);
        }
        if (!empty($senderApartment)) {
            $senderStreet = $senderStreet . '/' . $senderApartment;
        }
        $sender->line1 = $senderStreet;
        $sender->line2 = '';
        $sender->postalCode = $od->sender_postal_code ?: '';
        $sender->city = $od->sender_city ?: '';
        $sender->isResidential = 0;
        $sender->contactPerson = $od->sender_full_name ?: '';
        $sender->email = $od->sender_email ?: '';
        if ($senderCountryCode === 'RO') {
            $senderPhone = preg_replace('/[\s\-\(\)\.]+/', '', trim($od->sender_phone_number ?: ''));
            if (str_starts_with($senderPhone, '+40')) {
                $senderPhone = substr($senderPhone, 3);
            } elseif (str_starts_with($senderPhone, '0040')) {
                $senderPhone = substr($senderPhone, 4);
            }
        } elseif ($region === 'ro') {
            $senderPhone = preg_replace('/[\s\-\(\)\.]+/', '', trim($od->sender_phone_number ?: ''));
        } else {
            if ($this->isOrlenPaczkaService($od->shipment_selected_service ?? null)) {
                $senderPhone = preg_replace('/[\s\-\(\)\.]+/', '', trim($od->sender_phone_number ?: ''));
                if (str_starts_with($senderPhone, '+48')) {
                    $senderPhone = substr($senderPhone, 3);
                } elseif (str_starts_with($senderPhone, '0048')) {
                    $senderPhone = substr($senderPhone, 4);
                }
            } else {
                $senderPhone = $this->formatPhoneWithCountryCode($od->sender_phone_number ?: '', $sender->countryCode);
            }
        }
        $sender->phone = $senderPhone;

        if ($region === 'ro') {
            $companyName = $od->sender_company_name ?? '';
            if ($od->sender_address_type === 'company' && $companyName !== '') {
                $sender->company = $companyName;
            } else {
                unset($sender->company);
            }
            $sender->hasMapPoint = false;

            $sender->streetName = $od->sender_street ?: '';
            $sender->streetNumber = !empty($od->sender_building_number) ? $od->sender_building_number : '';
            if (!empty($od->sender_block) || !empty($od->sender_entrance) || !empty($od->sender_floor) || !empty($od->sender_flat)) {
                $sender->block = $od->sender_block ?? '';
                $sender->entrance = $od->sender_entrance ?? '';
                $sender->floor = $od->sender_floor ?? '';
                $sender->flat = $od->sender_flat ?? '';
            } elseif (!empty($od->sender_apartment_number)) {
                $sender->flat = $od->sender_apartment_number;
            }
        }

        $receiver = new Contact();
        $receiver->countryCode = $oc['shipping_country'] ?? $regionCountry;
        $receiver->country = $receiver->countryCode;
        $receiver->stateCode = 'YY';
        $receiver->name = trim(($oc['shipping_firstname'] ?? '') . ' ' . ($oc['shipping_lastname'] ?? ''));
        $receiver->line1 = $oc['shipping_address_1'] ?? '';
        $receiver->line2 = !empty($oc['shipping_address_2'] ?? '') ? $oc['shipping_address_2'] : '';
        $receiver->postalCode = $oc['shipping_postcode'] ?? '';
        $receiver->city = $oc['shipping_city'] ?? '';
        $receiver->isResidential = empty($oc['shipping_company']) ? 1 : 0;
        $receiver->contactPerson = $receiver->name;
        $receiver->email = $od->shipping_email ?: ($oc['email'] ?? '');
        $receiverCountryCode = strtoupper($receiver->countryCode);
        if ($receiverCountryCode === 'RO') {
            $receiverPhone = preg_replace('/[\s\-\(\)\.]+/', '', trim($od->shipping_phone_number ?: ($oc['telephone'] ?? '')));
            if (str_starts_with($receiverPhone, '+40')) {
                $receiverPhone = substr($receiverPhone, 3);
            } elseif (str_starts_with($receiverPhone, '0040')) {
                $receiverPhone = substr($receiverPhone, 4);
            }
        } elseif ($region === 'ro') {
            $receiverPhone = preg_replace('/[\s\-\(\)\.]+/', '', trim($od->shipping_phone_number ?: ($oc['telephone'] ?? '')));
        } else {
            if ($this->isOrlenPaczkaService($od->shipment_selected_service ?? null)) {
                $receiverPhone = preg_replace('/[\s\-\(\)\.]+/', '', trim($od->shipping_phone_number ?: ($oc['telephone'] ?? '')));
                if (str_starts_with($receiverPhone, '+48')) {
                    $receiverPhone = substr($receiverPhone, 3);
                } elseif (str_starts_with($receiverPhone, '0048')) {
                    $receiverPhone = substr($receiverPhone, 4);
                }
            } else {
                $receiverPhone = $this->formatPhoneWithCountryCode(
                    $od->shipping_phone_number ?: ($oc['telephone'] ?? ''),
                    $receiver->countryCode
                );
            }
        }
        $receiver->phone = $receiverPhone;

        if ($region === 'ro') {
            $shippingCompany = $oc['shipping_company'] ?? '';
            if (!empty($shippingCompany)) {
                $receiver->company = $shippingCompany;
            } else {
                unset($receiver->company);
            }
            $receiver->hasMapPoint = false;

            if (!empty($oc['shipping_block']) || !empty($oc['shipping_entrance']) || !empty($oc['shipping_floor']) || !empty($oc['shipping_flat'])) {
                $receiver->streetName = $oc['shipping_street'] ?? '';
                $receiver->streetNumber = $oc['shipping_building_number'] ?? '';
                $receiver->block = $oc['shipping_block'] ?? '';
                $receiver->entrance = $oc['shipping_entrance'] ?? '';
                $receiver->floor = $oc['shipping_floor'] ?? '';
                $receiver->flat = $oc['shipping_flat'] ?? '';
            } elseif (!empty($oc['shipping_street'])) {
                $receiver->streetName = $oc['shipping_street'];
                $receiver->streetNumber = !empty($oc['shipping_building_number']) ? $oc['shipping_building_number'] : '';
                $apartmentField = $oc['shipping_apartment_number'] ?? '';
                if (!empty($apartmentField)) {
                    if (preg_match('/\b(?:bl|bloc|block|sc|scara|et|etaj|ap|apartament)\.?\s/iu', $apartmentField)) {
                        $this->extractRomanianAddressComponents($receiver, $apartmentField);
                    } else {
                        $receiver->flat = $apartmentField;
                    }
                }
            } else {
                $this->parseRomanianAddress($receiver, $oc['shipping_address_1'] ?? '', $oc['shipping_address_2'] ?? '');
            }
        }

        $address = new Address($sender, $receiver);

        $empty = new NotificationDetail(0, 0, 0, 0);
        $notification = new Notification($empty, $empty, $empty, $empty);

        $cod = null;

        $codAmount = 0;
        if (isset($od->package_cod)) {
            $codAmount = (float) $od->package_cod;
        }

        if ($codAmount > 0) {
            try {
                if ($region === 'cz') {
                    $codAmount = (int) round($codAmount);
                } elseif ($region === 'pl') {
                    $codAmount = (int) round($codAmount * 100);
                }

                $cod = new Cod();

                $cod->amount = (string) $codAmount;
                $bankAccount = $this->formatBankAccount($od->sender_bank_account_number ?? '');

                if (property_exists($cod, 'bankaccount')) {
                    $cod->bankaccount = $bankAccount;
                } elseif (property_exists($cod, 'bankAccount')) {
                    $cod->bankAccount = $bankAccount;
                }

                $cod->currency = $currency;

                if ($region === 'cz') {
                    $bankCode = !empty($od->sender_bank_code) ? trim($od->sender_bank_code) : '';
                    if (empty($bankCode) && !empty($od->sender_bank_account_number)) {
                        $rawAccount = trim($od->sender_bank_account_number);
                        if (preg_match('/\/(\d{4})$/', $rawAccount, $m)) {
                            $bankCode = $m[1];
                        }
                    }
                    if (!empty($bankCode)) {
                        $cod->bankCode = $bankCode;
                    }
                }

                if ($region === 'cz') {
                    $senderCountry = strtoupper($od->sender_country ?? 'CZ');
                    $receiverCountry = strtoupper($oc['shipping_country'] ?? 'CZ');
                    if ($senderCountry === 'CZ' && $receiverCountry !== 'CZ'
                        && !empty($od->sender_additional_bank_account_number)) {
                        $cod->bankaccount = $od->sender_additional_bank_account_number;
                    }
                }
            } catch (\Exception $e) {
                $cod = null;
            }
        }

        $pickup = new Pickup();
        $defaultPickupType = OrderDetailsService::getConfiguredPickupType($region);
        $pickupType = $od->selected_pickup_type ?: $defaultPickupType;

        if ($region === 'cz') {
            $pickupType = str_replace('_', '', strtoupper($pickupType));
            if (!in_array($pickupType, ['ONDEMAND', 'OCCASIONAL'])) {
                $pickupType = 'OCCASIONAL';
            }
        } elseif ($region === 'pl') {
            $pickupType = strtoupper($pickupType);
            if (!in_array($pickupType, ['SELF', 'COURIER', 'NO_PICKUP'])) {
                $pickupType = 'COURIER';
            }
        }
        $pickup->type = $pickupType;

        if ($region === 'cz' && !empty($od->shipping_via_pickup_point)) {
            $pickup->pickupBranch = '1';
        }

        if ($region === 'ro') {
            $pickupType = strtoupper($pickupType);
            if (!in_array($pickupType, ['COURIER', 'SELF'])) {
                $pickupType = 'COURIER';
            }
            $pickup->type = strtolower($pickupType);
        }

        if ($region !== 'cz') {
            $sameDayEnabled = (int) \Configuration::get('ALSENDO_SAME_DAY_PICKUP');
            $minDate = $sameDayEnabled ? date('Y-m-d') : date('Y-m-d', strtotime('+1 day'));

            if ($sameDayEnabled && $minDate === date('Y-m-d') && (int) date('H') >= 15) {
                $minDate = date('Y-m-d', strtotime('+1 day'));
            }

            $providedDate = $od->shipment_preferred_pickup_date ?? null;

            if (!empty($providedDate) && $providedDate >= $minDate) {
                $pickup->date = $providedDate;
            } else {
                $pickup->date = $minDate;
            }

            if (strtoupper($pickup->type) === 'COURIER') {
                $defaultHoursFrom = \Configuration::get('ALSENDO_DEFAULT_PICKUP_HOURS_FROM', null, null, null, '08:00') ?: '08:00';
                $defaultHoursTo = \Configuration::get('ALSENDO_DEFAULT_PICKUP_HOURS_TO', null, null, null, '17:00') ?: '17:00';

                $pickup->hoursFrom = $od->shipment_preferred_pickup_hours_from ?? $defaultHoursFrom;
                $pickup->hoursTo = $od->shipment_preferred_pickup_hours_to ?? $defaultHoursTo;
            }
        }

        $or = new OrderRequest();
        $or->address = $address;
        $or->option = [];
        $or->notification = $notification;
        $or->pickup = $pickup;

        if ($region === 'cz') {
            if (!empty($od->pickup_request) && strtoupper($pickupType) !== 'ONDEMAND') {
                $or->pickupRequest = true;
            }
        }

        if (in_array($region, ['cz', 'ro'])) {
            $shipment = new Shipment();
            $shipment->dimension1 = (int) ($od->package_length ?: 10);
            $shipment->dimension2 = (int) ($od->package_width ?: 10);
            $shipment->dimension3 = (int) ($od->package_height ?: 10);
            $shipment->weight = (int) ($od->package_weight ?: 1);
            $shipment->isNstd = $od->is_nstd;
            $shipment->shipmentTypeCode = $od->package_type_code;

            if ($region === 'cz' && !empty($od->shipping_via_pickup_point)) {
                $shipment->pickupBranch = 1;
            }

            if ($region === 'ro') {
                $shipment->shape = 'standard';
                $rawContent = $od->package_content ?: '';
                $shipment->content = (strlen($rawContent) >= 3) ? 'Colet: ' . $rawContent : 'Colet PrestaShop';
                $shipment->declaredValue = !empty($od->package_declared_value) ? (string) $od->package_declared_value : 0;
            }

            $or->shipment = [$shipment];
        } else {
            $plPackageType = strtoupper($od->package_type_code);
            $validPlTypes = ['LIST', 'PACZKA', 'PALETA', 'PALETA_60X40', 'POLPALETA', 'PALETA_PRZEMYSLOWA', 'PALETA_PRZEMYSLOWA_B'];
            if (!in_array($plPackageType, $validPlTypes)) {
                $plPackageType = 'PACZKA';
            }
            $shipmentObj = new Shipment();
            $shipmentObj->dimension1 = (int) ($od->package_length ?: 10);
            $shipmentObj->dimension2 = (int) ($od->package_width ?: 10);
            $shipmentObj->dimension3 = (int) ($od->package_height ?: 10);
            $shipmentObj->weight = (int) ($od->package_weight ?: 1);
            $shipmentObj->isNstd = (int) $od->is_nstd;
            $shipmentObj->shipmentTypeCode = $plPackageType;
            $or->shipment = [$shipmentObj];
        }

        $or->shipmentValue = !empty($od->package_declared_value)
            ? (float) $od->package_declared_value * 100
            : 0;

        if ($cod) {
            $or->cod = $cod;
        }

        $orContent = $od->package_content ?: '';
        if ($region === 'ro') {
            $or->content = (strlen($orContent) >= 3) ? 'Colet: ' . $orContent : 'Colet PrestaShop';
        } else {
            $or->content = $orContent;
        }
        $or->currency = $currency;
        $or->source = 'prestashop';

        if (!empty($od->shipment_selected_service)) {
            $or->serviceId = (string) $od->shipment_selected_service;

            if ($region === 'cz') {
                $servicesCfg = json_decode(\Configuration::get('ALSENDO_AVAILABLE_SERVICES', null, null, null, ''), true) ?: [];
                foreach ($servicesCfg as $svcCfg) {
                    $svcId = (string) ($svcCfg['service_id'] ?? '');
                    $baseId = preg_replace('/_topoint$/i', '', $svcId);
                    if ($svcId === (string) $od->shipment_selected_service || $baseId === (string) $od->shipment_selected_service) {
                        $or->carrier = $svcCfg['supplier'] ?? null;
                        break;
                    }
                }
            }
        }

        return $or;
    }

    // NOT USED COMMENTED FOR PRESTASHOP VALIDATION
    /*
    private function dtoHasReceiverPickupPoint(FullOrderDTO $dto): bool
    {
        $od = $dto->orderDetails;
        if (!$od) {
            return false;
        }
        $shipmentPickupPoint = $od->shipment_pickup_point ?? null;
        if ($shipmentPickupPoint && is_array($shipmentPickupPoint) && !empty($shipmentPickupPoint['code'])) {
            return true;
        }
        if (!empty($dto->orderPickupPoint) && is_array($dto->orderPickupPoint) && !empty($dto->orderPickupPoint['code'])) {
            return true;
        }

        return false;
    }

    private function applySenderMerchantPickupPoint(OrderRequest $orderRequest, FullOrderDTO $dto): bool
    {
        $od = $dto->orderDetails;
        if (!$od) {
            return false;
        }
        $orderId = (int) ($od->order_id ?? 0);
        if (!$orderId) {
            return false;
        }

        $merchantPickupPoint = $od->merchant_pickup_point ?? null;
        if (!$merchantPickupPoint || empty($merchantPickupPoint['code'])) {
            $merchantPickupPoint = $this->getMerchantPickupPointFromConfig($orderId);
        }

        if (!$merchantPickupPoint || empty($merchantPickupPoint['code'])) {
            return false;
        }

        $code = $merchantPickupPoint['code'];
        $orderRequest->address->sender->foreignAddressId = $code;
        $orderRequest->address->sender->hasMapPoint = true;
        $orderRequest->address->sender->mapPointId = is_numeric($code) ? (int) $code : $code;

        return true;
    }

    private function applyReceiverPickupPoint(OrderRequest $orderRequest, FullOrderDTO $dto): void
    {
        $od = $dto->orderDetails;
        if (!$od) {
            return;
        }

        $pickupPointToUse = null;
        $shipmentPickupPoint = $od->shipment_pickup_point ?? null;
        if ($shipmentPickupPoint && is_array($shipmentPickupPoint) && !empty($shipmentPickupPoint['code'])) {
            $pickupPointToUse = $shipmentPickupPoint;
        } elseif (!empty($dto->orderPickupPoint) && is_array($dto->orderPickupPoint) && !empty($dto->orderPickupPoint['code'])) {
            $pickupPointToUse = $dto->orderPickupPoint;
        }

        if (!$pickupPointToUse) {
            return;
        }

        $code = $pickupPointToUse['code'];
        $orderRequest->address->receiver->foreignAddressId = $code;
        $orderRequest->address->receiver->hasMapPoint = true;
        $orderRequest->address->receiver->mapPointId = is_numeric($code) ? (int) $code : $code;

        if ($this->config->getRegion() === 'ro') {
            if (!empty($pickupPointToUse['city'])) {
                $orderRequest->address->receiver->city = $pickupPointToUse['city'];
            }
            if (!empty($pickupPointToUse['postalCode'])) {
                $orderRequest->address->receiver->postalCode = $pickupPointToUse['postalCode'];
            }
            $orderRequest->address->receiver->localityId = null;
            $pointAddress = $pickupPointToUse['address'] ?? '';
            $pointName = $pickupPointToUse['name'] ?? $pickupPointToUse['description'] ?? '';
            $orderRequest->address->receiver->line1 = $pointAddress ?: $pointName;
            $orderRequest->address->receiver->line2 = null;
            $pointStreet = $pickupPointToUse['street'] ?? '';
            $orderRequest->address->receiver->streetName = $pointStreet ?: $pointName;
            $orderRequest->address->receiver->streetNumber = '';
        }
    }
    */

    private function prepareOrderRequestForSend(OrderRequest $orderRequest, FullOrderDTO $dto): OrderRequest
    {
        $preparedOrderRequest = clone $orderRequest;

        $od = $dto->orderDetails;
        if (!$od) {
            return $preparedOrderRequest;
        }

        $orderId = (int) ($od->order_id ?? 0);
        if (!$orderId) {
            return $preparedOrderRequest;
        }

        $selectedPickupType = $od->selected_pickup_type ?? 'COURIER';
        $shipmentPickupPoint = $od->shipment_pickup_point ?? null;
        $merchantPickupPoint = $od->merchant_pickup_point ?? null;

        if ($selectedPickupType === 'SELF') {
            $requiresMerchantPickup = $this->carrierRequiresMerchantPickup($orderId);

            if ($requiresMerchantPickup) {
                if (!$merchantPickupPoint || empty($merchantPickupPoint['code'])) {
                    $merchantPickupPoint = $this->getMerchantPickupPointFromConfig($orderId);
                }

                if (!$merchantPickupPoint || empty($merchantPickupPoint['code'])) {
                    $customerOperator = null;
                    if ($shipmentPickupPoint && is_array($shipmentPickupPoint) && !empty($shipmentPickupPoint['operator'])) {
                        $customerOperator = strtolower($shipmentPickupPoint['operator']);
                    } elseif (!empty($dto->orderPickupPoint) && is_array($dto->orderPickupPoint) && !empty($dto->orderPickupPoint['operator'])) {
                        $customerOperator = strtolower($dto->orderPickupPoint['operator']);
                    }
                    if ($customerOperator) {
                        $mapBridge = new MapBridge();
                        $configKey = $mapBridge->getPickupConfigKey($customerOperator);
                        $pickupJson = \Configuration::get($configKey, null, null, null, '');
                        if (!empty($pickupJson)) {
                            $val = $pickupJson;
                            for ($i = 0; $i < 10; ++$i) {
                                $point = json_decode($val, true);
                                if (is_array($point) && !empty($point['code'])) {
                                    $merchantPickupPoint = $point;
                                    break;
                                }
                                $stripped = stripslashes($val);
                                if ($stripped === $val) {
                                    break;
                                }
                                $val = $stripped;
                            }
                        }
                    }
                }

                if ($merchantPickupPoint && !empty($merchantPickupPoint['code'])) {
                    $code = $merchantPickupPoint['code'];
                    $preparedOrderRequest->address->sender->foreignAddressId = $code;
                    $preparedOrderRequest->address->sender->hasMapPoint = true;
                    $preparedOrderRequest->address->sender->mapPointId = is_numeric($code) ? (int) $code : $code;
                } else {
                    throw new \Exception('No pickup point configured for SELF pickup type');
                }
            }
        }

        $pickupPointToUse = null;
        $pickupPointSource = null;
        if ($shipmentPickupPoint && is_array($shipmentPickupPoint) && !empty($shipmentPickupPoint['code'])) {
            $pickupPointToUse = $shipmentPickupPoint;
            $pickupPointSource = 'admin_form';
        } elseif (!empty($dto->orderPickupPoint) && is_array($dto->orderPickupPoint)) {
            if (!empty($dto->orderPickupPoint['code'])) {
                $pickupPointToUse = $dto->orderPickupPoint;
                $pickupPointSource = 'checkout';
            }
        }

        $servicesCfg = json_decode(\Configuration::get('ALSENDO_AVAILABLE_SERVICES', null, null, null, ''), true) ?: [];

        if ($selectedPickupType === 'COURIER' && $pickupPointToUse && $this->config->getRegion() !== 'ro'
            && !empty($preparedOrderRequest->serviceId)) {
            $serviceDeliversToPoint = false;
            foreach ($servicesCfg as $svc) {
                if ((string) ($svc['service_id'] ?? '') === (string) $preparedOrderRequest->serviceId) {
                    $serviceDeliversToPoint = !empty($svc['to_point'])
                        || !empty($svc['door_to_point'])
                        || !empty($svc['point_to_point']);
                    break;
                }
            }
            if (!$serviceDeliversToPoint) {
                $pickupPointToUse = null;
            }
        }

        if ($pickupPointToUse && $pickupPointSource === 'checkout' && !empty($preparedOrderRequest->serviceId)) {
            $pointOperator = strtolower($pickupPointToUse['operator'] ?? '');
            if (!empty($pointOperator)) {
                $region = $this->config->getRegion();
                $mapBridge = new MapBridge();
                $selectedSupplier = '';
                $selectedServiceName = '';
                foreach ($servicesCfg as $svc) {
                    if ((string) ($svc['service_id'] ?? '') === (string) $preparedOrderRequest->serviceId) {
                        $selectedSupplier = strtolower($svc['supplier'] ?? '');
                        $selectedServiceName = $svc['name'] ?? '';
                        break;
                    }
                }
                if (!empty($selectedSupplier)) {
                    $selectedOperator = $mapBridge->resolveMapOperator($region, $selectedSupplier, $selectedServiceName);
                    $pointBPOperator = $mapBridge->resolveMapOperator($region, $pointOperator);
                    if ($selectedOperator && $pointBPOperator && $selectedOperator !== $pointBPOperator) {
                        $pickupPointToUse = null;
                    }
                }
            }
        }

        if ($pickupPointToUse) {
            $code = $pickupPointToUse['code'];
            $preparedOrderRequest->address->receiver->foreignAddressId = $code;
            $preparedOrderRequest->address->receiver->hasMapPoint = true;
            $preparedOrderRequest->address->receiver->mapPointId = is_numeric($code) ? (int) $code : $code;

            if ($this->config->getRegion() === 'ro') {
                if (!empty($pickupPointToUse['city'])) {
                    $preparedOrderRequest->address->receiver->city = $pickupPointToUse['city'];
                }
                if (!empty($pickupPointToUse['postalCode'])) {
                    $preparedOrderRequest->address->receiver->postalCode = $pickupPointToUse['postalCode'];
                }
                $preparedOrderRequest->address->receiver->localityId = null;
                $pointAddress = $pickupPointToUse['address'] ?? '';
                $pointName = $pickupPointToUse['name'] ?? $pickupPointToUse['description'] ?? '';

                $preparedOrderRequest->address->receiver->line1 = $pointAddress ?: $pointName;
                $preparedOrderRequest->address->receiver->line2 = null;
                $pointStreet = $pickupPointToUse['street'] ?? '';
                $preparedOrderRequest->address->receiver->streetName = $pointStreet ?: $pointName;
                $preparedOrderRequest->address->receiver->streetNumber = '';
            }
        }

        return $preparedOrderRequest;
    }

    private function getMerchantPickupPointFromConfig(int $orderId): ?array
    {
        try {
            $order = new \Order($orderId);
            $carrier = new \Carrier($order->id_carrier);
            $carrierName = strtolower(trim($carrier->name));

            $region = \Configuration::get('ALSENDO_REGION') ?: 'pl';
            $mapBridge = new MapBridge();
            $pickupOperators = $mapBridge->getDefaultPickupOperators($region);

            $carrierMap = [];
            foreach ($pickupOperators as $opKey) {
                $configKey = $mapBridge->getPickupConfigKey($opKey);
                $carrierMap[$opKey] = $configKey;
            }

            if ($region === 'pl') {
                $carrierMap['poczta'] = 'ALSENDO_PICKUP_POCZTA';
            }

            foreach ($carrierMap as $alias => $configKey) {
                if (strpos($carrierName, $alias) !== false) {
                    $pickupJson = \Configuration::get($configKey, null, null, null, '');

                    if (!empty($pickupJson)) {
                        $val = $pickupJson;
                        for ($i = 0; $i < 10; ++$i) {
                            $point = json_decode($val, true);
                            if (is_array($point) && !empty($point['code'])) {
                                return $point;
                            }
                            $stripped = stripslashes($val);
                            if ($stripped === $val) {
                                break;
                            }
                            $val = $stripped;
                        }
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function isCarrierWithoutMerchantPickup(string $carrierName): bool
    {
        $aliases = ['orlen', 'ruch', 'paczka', 'gls'];
        $lower = strtolower(trim($carrierName));
        foreach ($aliases as $alias) {
            if (strpos($lower, $alias) !== false) {
                return true;
            }
        }

        return false;
    }

    private function carrierRequiresMerchantPickup(int $orderId): bool
    {
        try {
            $order = new \Order($orderId);
            $carrier = new \Carrier($order->id_carrier);

            return !self::isCarrierWithoutMerchantPickup($carrier->name);
        } catch (\Exception $e) {
            return true;
        }
    }

    private function logException(\Throwable $e, string $context): void
    {
        $line = date('Y-m-d H:i:s') . " [{$context}] " . get_class($e) . ': ' . $e->getMessage();

        if ($e instanceof ValidationException) {
            $errors = $e->getErrors();
            if (!empty($errors)) {
                $parts = [];
                foreach ($errors as $field => $msgs) {
                    $parts[] = $field . ': ' . implode(', ', (array) $msgs);
                }
                $line .= "\n  Validation errors: " . implode(' | ', $parts);
            }
        }

        if ($e instanceof ResponseException) {
            $apiErr = $e->getApiErrorResponse();
            $line .= "\n  API statusCode=" . ($apiErr->statusCode ?? 'NULL')
                . ', message=' . ($apiErr->message ?? 'NULL');
            if (!empty($apiErr->errors)) {
                foreach ($apiErr->errors as $i => $err) {
                    $line .= "\n  error[{$i}]: field=" . ($err->field ?? 'NULL') . ', msg=' . $err->message;
                }
            }
        }

        error_log('[Alsendo] ' . $line);
    }

    private function formatResponseException(ResponseException $e): string
    {
        $msg = strip_tags($e->getMessage());

        if (strpos($msg, 'Comanda de ridicare') !== false) {
            return 'The carrier rejected the pickup order — the configured pickup hours '
                . 'are outside the carrier\'s schedule for this service. '
                . 'Please adjust the default pickup hours in module settings '
                . '(Settings → Default pickup hours) or use the full form to set a later pickup time.';
        }

        $apiErr = $e->getApiErrorResponse();
        if (empty($apiErr->errors)) {
            return $msg;
        }
        $parts = [];
        foreach ($apiErr->errors as $err) {
            $prefix = $err->field ? "[{$err->field}] " : '';
            $parts[] = $prefix . strip_tags($err->message);
        }

        return implode('; ', $parts);
    }

    private function normalizeEcoletServiceId(string $serviceId): string
    {
        $services = json_decode(\Configuration::get('ALSENDO_AVAILABLE_SERVICES') ?: '[]', true) ?: [];
        $knownIds = array_column($services, 'service_id');

        if (in_array($serviceId, $knownIds, true)) {
            return $serviceId;
        }

        $bestMatch = $serviceId;
        $bestLen = 0;
        foreach ($knownIds as $knownId) {
            if (str_starts_with($serviceId, $knownId) && strlen($knownId) > $bestLen) {
                $bestMatch = $knownId;
                $bestLen = strlen($knownId);
            }
        }

        return $bestMatch;
    }

    private function parseRomanianAddress(Contact $contact, string $address1, string $address2): void
    {
        $address1 = trim($address1);
        $address2 = trim($address2);

        if (preg_match('/^(.*?)\s+(\d+\w{0,5})\s*$/u', $address1, $m)) {
            $contact->streetName = trim($m[1]);
            $contact->streetNumber = $m[2];
        } else {
            $contact->streetName = $address1;
            $contact->streetNumber = $this->extractRomanianStreetNumber($address2);
        }

        if (strlen($contact->streetNumber) > 10) {
            $contact->streetNumber = substr($contact->streetNumber, 0, 10);
        }

        $this->extractRomanianAddressComponents($contact, $address2);
    }

    private function extractRomanianStreetNumber(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        if (preg_match('/(?:^|[,;]\s*)nr\.?\s*(\d+\w{0,3})/iu', $text, $m)) {
            return $m[1];
        }

        if (preg_match('/^(\d+\w{0,3})(?:[,\s]|$)/u', $text, $m)) {
            return $m[1];
        }

        return '';
    }

    private function extractRomanianAddressComponents(Contact $contact, string $text): void
    {
        if (empty($text)) {
            return;
        }

        if (preg_match('/\b(?:bl(?:oc)?|block)\.?\s*([A-Za-z0-9]+)/iu', $text, $m)) {
            $contact->block = $m[1];
        }

        if (preg_match('/\b(?:sc(?:ara)?|entrance)\.?\s*([A-Za-z0-9]+)/iu', $text, $m)) {
            $contact->entrance = $m[1];
        }

        if (preg_match('/\b(?:et(?:aj)?|floor)\.?\s*([A-Za-z0-9]+)/iu', $text, $m)) {
            $contact->floor = $m[1];
        }

        if (preg_match('/\b(?:ap(?:artament)?|apt)\.?\s*([A-Za-z0-9]+)/iu', $text, $m)) {
            $contact->flat = $m[1];
        }
    }

    public static function selectCheapestService(array $valuationArray, string $region, bool $hasMap = false, $mappedServiceId = null): ?array
    {
        $availableServices = json_decode(\Configuration::get('ALSENDO_AVAILABLE_SERVICES') ?: '[]', true) ?: [];
        $toPointMap = [];
        $carrierNameToCode = [];
        foreach ($availableServices as $svc) {
            $toPointMap[(string) ($svc['service_id'] ?? '')] = !empty($svc['to_point']);
            if ($region === 'cz' && !empty($svc['name']) && !empty($svc['service_id'])) {
                $baseName = preg_replace('/\s*\(Pickup point\)\s*$/i', '', $svc['name']);
                $carrierNameToCode[strtolower($baseName)] = preg_replace('/_topoint$/i', '', $svc['service_id']);
            }
        }

        $mappedBase = null;
        if ($mappedServiceId && $region === 'cz') {
            $mappedBase = strtoupper(preg_replace('/_topoint$/i', '', (string) $mappedServiceId));
        } elseif ($mappedServiceId) {
            $mappedBase = $mappedServiceId;
        }

        $resolveEffectiveId = function ($sid, $service) use ($region, $carrierNameToCode) {
            $effectiveId = $sid;
            if ($region === 'cz' && !empty($service['carrier'])) {
                $carrierLower = strtolower($service['carrier']);
                if (!empty($carrierNameToCode[$carrierLower])) {
                    $effectiveId = $carrierNameToCode[$carrierLower];
                } else {
                    $effectiveId = $service['carrier'];
                }
            }

            return $effectiveId;
        };

        $cheapest = null;
        $cheapestPrice = PHP_FLOAT_MAX;

        foreach ($valuationArray as $sid => $service) {
            $priceGross = (float) ($service['priceTable']['priceGross'] ?? 0);
            if ($priceGross <= 0) {
                continue;
            }

            if ($region === 'pl') {
                $priceGross /= 100;
            }

            $effectiveId = $resolveEffectiveId($sid, $service);

            if ($mappedBase) {
                $effectiveBase = ($region === 'cz')
                    ? strtoupper(preg_replace('/_topoint$/i', '', (string) $effectiveId))
                    : $effectiveId;
                if ((string) $effectiveBase !== (string) $mappedBase) {
                    continue;
                }
            }

            if (!empty($toPointMap)) {
                $isToPoint = $toPointMap[(string) $sid] ?? $toPointMap[(string) $effectiveId] ?? null;
                if ($hasMap && $isToPoint === false) {
                    continue;
                }
                if (!$hasMap && $isToPoint === true) {
                    continue;
                }
            }

            if ($priceGross < $cheapestPrice) {
                $cheapestPrice = $priceGross;
                $cheapest = [
                    'service_id' => $effectiveId,
                    'price_gross' => $cheapestPrice,
                    'service_name' => $service['serviceName'] ?? $service['name'] ?? ('Service ' . $effectiveId),
                ];
            }
        }

        if (!$cheapest && $mappedBase) {
            $cheapestPrice = PHP_FLOAT_MAX;
            foreach ($valuationArray as $sid => $service) {
                $priceGross = (float) ($service['priceTable']['priceGross'] ?? 0);
                if ($priceGross <= 0) {
                    continue;
                }
                if ($region === 'pl') {
                    $priceGross /= 100;
                }

                $effectiveId = $resolveEffectiveId($sid, $service);

                $effectiveBase = ($region === 'cz')
                    ? strtoupper(preg_replace('/_topoint$/i', '', (string) $effectiveId))
                    : $effectiveId;
                if ((string) $effectiveBase !== (string) $mappedBase) {
                    continue;
                }

                if ($priceGross < $cheapestPrice) {
                    $cheapestPrice = $priceGross;
                    $cheapest = [
                        'service_id' => $effectiveId,
                        'price_gross' => $cheapestPrice,
                        'service_name' => $service['serviceName'] ?? $service['name'] ?? ('Service ' . $effectiveId),
                    ];
                }
            }
        }

        if (!$cheapest && !$mappedBase) {
            $cheapestPrice = PHP_FLOAT_MAX;
            foreach ($valuationArray as $sid => $service) {
                $priceGross = (float) ($service['priceTable']['priceGross'] ?? 0);
                if ($priceGross <= 0) {
                    continue;
                }
                if ($region === 'pl') {
                    $priceGross /= 100;
                }

                $effectiveId = $resolveEffectiveId($sid, $service);

                if (!empty($toPointMap)) {
                    $isToPoint = $toPointMap[(string) $sid] ?? $toPointMap[(string) $effectiveId] ?? null;
                    if ($hasMap && $isToPoint === false) {
                        continue;
                    }
                    if (!$hasMap && $isToPoint === true) {
                        continue;
                    }
                }

                if ($priceGross < $cheapestPrice) {
                    $cheapestPrice = $priceGross;
                    $cheapest = [
                        'service_id' => $effectiveId,
                        'price_gross' => $cheapestPrice,
                        'service_name' => $service['serviceName'] ?? $service['name'] ?? ('Service ' . $effectiveId),
                    ];
                }
            }
        }

        if (!$cheapest && !$mappedBase) {
            $cheapestPrice = PHP_FLOAT_MAX;
            foreach ($valuationArray as $sid => $service) {
                $priceGross = (float) ($service['priceTable']['priceGross'] ?? 0);
                if ($priceGross <= 0) {
                    continue;
                }
                if ($region === 'pl') {
                    $priceGross /= 100;
                }

                $effectiveId = $resolveEffectiveId($sid, $service);

                if ($priceGross < $cheapestPrice) {
                    $cheapestPrice = $priceGross;
                    $cheapest = [
                        'service_id' => $effectiveId,
                        'price_gross' => $cheapestPrice,
                        'service_name' => $service['serviceName'] ?? $service['name'] ?? ('Service ' . $effectiveId),
                    ];
                }
            }
        }

        return $cheapest;
    }
}
