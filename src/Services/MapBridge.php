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

use Alsendo\AlsendoWrapper\Map\MapConfig;
use Alsendo\AlsendoWrapper\Map\MapRegion;
use Alsendo\AlsendoWrapper\Map\MapService;

class MapBridge
{
    private MapService $mapService;

    private static function ensureAutoloader(): void
    {
        $modulePath = _PS_MODULE_DIR_ . 'alsendo/alsendo.php';
        if (file_exists($modulePath)) {
            require_once $modulePath;
        }
        if (class_exists('Alsendo', false) && method_exists('Alsendo', 'bootstrapAutoloader')) {
            \Alsendo::bootstrapAutoloader();
        }
    }

    public const OPERATOR_MAP = [
        'pl' => [
            'inpost_international' => 'INPOST_INTERNATIONAL',
            'inpost' => 'INPOST',
            'dhl_parcel' => 'DHL_PARCEL', 'dhl_int' => 'DHL_PARCEL', 'dhl' => 'DHL',
            'poczta' => 'POCZTA', 'pocztapolska' => 'POCZTA', 'pocztex' => 'POCZTA',
            'poczta polska' => 'POCZTA',
            'dpd' => 'DPD', 'ups' => 'UPS', 'gls' => 'GLS',
            'orlen' => 'RUCH', 'ruch' => 'RUCH', 'orlenpaczka' => 'RUCH',
            'pwr' => 'RUCH',
        ],
        'cz' => [
            'ppl' => 'PPL', 'balikovna' => 'BALIKOVNA', 'balíkovna' => 'BALIKOVNA',
            'ceska_posta' => 'BALIKOVNA', 'česká pošta' => 'BALIKOVNA',
            'zasilkovna' => 'PACKETA', 'zásilkovna' => 'PACKETA', 'packeta' => 'PACKETA',
            'dpd' => 'DPD', 'gls' => 'GLS', 'wedo' => 'ONE_DELIVERY',
        ],
        'ro' => [
            'dpd' => 'DPD', 'sameday' => 'SAMEDAY', 'fan' => 'FAN_COURIER',
            'fan_courier' => 'FAN_COURIER', 'fancourier' => 'FAN_COURIER',
            'cargus' => 'CARGUS', 'gls' => 'GLS',
        ],
    ];

    public const PICKUP_OPERATORS = [
        'pl' => ['inpost', 'dhl', 'poczta', 'dpd', 'ups'],
        'cz' => ['ppl', 'balikovna', 'packeta'],
        'ro' => ['dpd', 'sameday', 'fan_courier', 'gls', 'cargus'],
    ];

    public const INITIAL_ADDRESS = [
        'pl' => 'Warszawa',
        'cz' => 'Praha',
        'ro' => 'București',
    ];

    public const OPERATOR_POS_TYPE = [
        'cargus' => 'all',
    ];

    public const OPERATOR_LABELS = [
        'inpost' => 'InPost', 'dhl' => 'DHL', 'poczta' => 'Pocztex',
        'dpd' => 'DPD', 'ups' => 'UPS', 'gls' => 'GLS',
        'ruch' => 'Orlen Paczka',
        'ppl' => 'PPL', 'balikovna' => 'Balíkovna', 'packeta' => 'Packeta/Zásilkovna',
        'sameday' => 'Sameday', 'fan_courier' => 'Fan Courier', 'cargus' => 'Cargus',
    ];

    public function __construct()
    {
        self::ensureAutoloader();
        $this->mapService = new MapService();
    }

    public function getAssetUrls(string $region = 'pl'): array
    {
        $config = new MapConfig();
        $config->region = $region;
        $config->callbackFunctionName = 'dummy';

        return $this->mapService->getAssetUrls($config);
    }

    public function getRegionConfig(string $region): array
    {
        $config = new MapConfig();
        $config->region = $region;
        $config->callbackFunctionName = 'dummy';
        $config = $this->mapService->applyRegionDefaults($config);

        $countryCodes = $config->countryCodes;
        if ($region === 'cz') {
            $countryCodes = MapRegion::EU_COUNTRY_CODES;
        }

        return [
            'region' => $region,
            'language' => $config->language,
            'countryCodes' => $countryCodes,
            'defaultOperators' => $config->operators ?? [],
            'operatorMarkers' => $config->operatorMarkers,
            'operatorMap' => self::OPERATOR_MAP[$region] ?? [],
            'initialAddress' => self::INITIAL_ADDRESS[$region] ?? null,
        ];
    }

    public function renderModalHtml(string $containerId = 'alsendo-map-widget'): string
    {
        $overlayId = htmlspecialchars($containerId . '-overlay', ENT_QUOTES, 'UTF-8');
        $modalId = htmlspecialchars($containerId . '-modal', ENT_QUOTES, 'UTF-8');
        $closeId = htmlspecialchars($containerId . '-close', ENT_QUOTES, 'UTF-8');
        $cId = htmlspecialchars($containerId, ENT_QUOTES, 'UTF-8');

        return
            '<div id="' . $overlayId . '" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999;">' . "\n"
            . '  <div id="' . $modalId . '" style="position:absolute; top:5%; left:5%; width:90%; height:90%; background:#fff; border-radius:8px; overflow:hidden;">' . "\n"
            . '    <button id="' . $closeId . '" style="position:absolute; top:10px; right:10px; z-index:10000; background:#fff; border:1px solid #ccc; border-radius:50%; width:40px; height:40px; cursor:pointer; font-size:24px; line-height:1; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 5px rgba(0,0,0,0.3);" type="button">&times;</button>' . "\n"
            . '    <div id="' . $cId . '" style="width:100%; height:100%;"></div>' . "\n"
            . '  </div>' . "\n"
            . '</div>' . "\n";
    }

    public function getMapTemplateData(string $region, string $customerCity = ''): array
    {
        $assets = $this->getAssetUrls($region);
        $regionConfig = $this->getRegionConfig($region);
        if (!empty($customerCity)) {
            $regionConfig['initialAddress'] = $customerCity;
        }
        $modalHtml = $this->renderModalHtml();

        $assetsHtml = '<link rel="stylesheet" href="' . htmlspecialchars($assets['css'], ENT_QUOTES, 'UTF-8') . '" media="screen">' . "\n"
            . '<script src="' . htmlspecialchars($assets['js'], ENT_QUOTES, 'UTF-8') . '"></script>' . "\n";

        return [
            'assets_html' => $assetsHtml,
            'modal_html' => $modalHtml,
            'config_json' => json_encode($regionConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '{}',
        ];
    }

    public function getMapTemplateDataSafe(string $region, string $customerCity = '', string $containerId = 'alsendo-map-widget'): array
    {
        $assets = $this->getAssetUrls($region);
        $regionConfig = $this->getRegionConfig($region);
        if (!empty($customerCity)) {
            $regionConfig['initialAddress'] = $customerCity;
        }

        return [
            'css_url' => $assets['css'],
            'js_url' => $assets['js'],
            'container_id' => $containerId,
            'config_json' => json_encode($regionConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '{}',
        ];
    }

    public function getOperatorMapForRegion(string $region): array
    {
        return self::OPERATOR_MAP[$region] ?? [];
    }

    public function resolveMapOperator(string $region, ?string $supplier, string $serviceName = null, string $countryIso = null): ?string
    {
        if (!$supplier && !$serviceName) {
            return null;
        }

        $operatorMap = self::OPERATOR_MAP[$region] ?? [];
        $resolved = null;

        if ($supplier) {
            $supplierLower = strtolower($supplier);
            if (strpos($supplierLower, 'inpost') !== false
                && $serviceName && stripos($serviceName, 'international') !== false) {
                $resolved = 'INPOST_INTERNATIONAL';
            }
        }

        if ($resolved === null && $supplier) {
            $supplierLower = strtolower($supplier);
            foreach ($operatorMap as $alias => $operator) {
                if (strpos($supplierLower, $alias) !== false) {
                    $resolved = $operator;
                    break;
                }
            }
        }

        if ($resolved === null && $serviceName) {
            $nameStr = strtolower($serviceName);
            foreach ($operatorMap as $alias => $operator) {
                if (strpos($nameStr, $alias) !== false) {
                    $resolved = $operator;
                    break;
                }
            }
        }

        return $this->adjustDhlOperatorForCountry($resolved, $region, $countryIso);
    }

    private function adjustDhlOperatorForCountry(?string $operator, string $region, ?string $countryIso): ?string
    {
        if ($operator === null || $countryIso === null || $region !== 'pl') {
            return $operator;
        }

        $isDomestic = strtoupper($countryIso) === 'PL';

        if ($isDomestic && $operator === 'DHL_PARCEL') {
            return 'DHL';
        }

        if (!$isDomestic && $operator === 'DHL') {
            return 'DHL_PARCEL';
        }

        return $operator;
    }

    public function getDefaultPickupOperators(string $region): array
    {
        return self::PICKUP_OPERATORS[$region] ?? [];
    }

    public function getPickupConfigKey(string $operatorKey): string
    {
        return 'ALSENDO_PICKUP_' . strtoupper($operatorKey);
    }

    public function getOperatorLabel(string $operatorKey): string
    {
        return self::OPERATOR_LABELS[$operatorKey] ?? ucfirst($operatorKey);
    }

    public function getPickupOperatorsForTemplate(string $region): array
    {
        $operators = [];
        foreach ($this->getDefaultPickupOperators($region) as $opKey) {
            $configKey = $this->getPickupConfigKey($opKey);
            $posType = self::OPERATOR_POS_TYPE[$opKey] ?? 'POSTING';
            $operators[] = [
                'key' => $opKey,
                'label' => $this->getOperatorLabel($opKey),
                'value' => \Configuration::get($configKey, null, null, null, ''),
                'display' => \Configuration::get($configKey . '_DISPLAY', null, null, null, ''),
                'pos_type' => $posType,
            ];
        }

        return $operators;
    }
}
