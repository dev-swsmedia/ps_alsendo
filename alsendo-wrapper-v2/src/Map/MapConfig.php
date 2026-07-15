<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

declare(strict_types=1);

namespace Alsendo\AlsendoWrapper\Map;

if (!defined('_PS_VERSION_')) {
    exit;
}
class MapConfig
{
    /** @var string Region: 'pl', 'cz', 'ro' */
    public string $region;

    /** @var string Nazwa globalnej funkcji JS wywoływanej po wyborze punktu */
    public string $callbackFunctionName;

    /** @var string|null Język widgetu — auto-fill z regionu jeśli nie podane */
    public ?string $language = null;

    /** @var string|null Kod kraju — auto-fill z regionu jeśli nie podane */
    public ?string $countryCodes = null;

    /** @var array|null Operatorzy — null = domyślni dla regionu */
    public ?array $operators = null;

    /** @var string Typ punktu: 'DELIVERY' lub 'POSTING' */
    public string $posType = 'DELIVERY';

    /** @var string ID kontenera div */
    public string $containerId = 'alsendo-map-widget';

    /** @var string Szerokość kontenera CSS */
    public string $containerWidth = '100%';

    /** @var string Wysokość kontenera CSS */
    public string $containerHeight = '500px';

    /** @var bool Tryb testowy widgetu */
    public bool $testMode = false;

    /** @var bool Wyszukiwanie po kodzie pocztowym */
    public bool $codeSearch = true;

    /** @var bool Markery operatorów na mapie */
    public bool $operatorMarkers = false;

    /** @var bool|null Tylko punkty z COD */
    public ?bool $codOnly = null;

    /** @var bool|null Pokaż info o COD */
    public ?bool $showCod = null;

    /** @var string|null Adres początkowy */
    public ?string $initialAddress = null;

    /** @var array|null Wybrany punkt: ['code' => 'XX', 'operator' => 'YY'] */
    public ?array $selectedPoint = null;

    /** @var string|null Alias np. 'ecolet-192872' dla RO */
    public ?string $alias = null;

    /** @var array|null Ceny: ['INPOST' => ['price' => 9.99, 'currency' => 'zł']] */
    public ?array $prices = null;

    /** @var bool|null Ukryj filtry */
    public ?bool $hideFilters = null;

    /** @var bool|null Tylko mapa bez listy */
    public ?bool $mapOnly = null;

    /** @var string Wersja widgetu BPWidget */
    public string $widgetVersion = 'v8.7';

    /** @var string Bazowy URL widgetu */
    public string $widgetBaseUrl = 'https://map.alsendo.com';

    /** @var bool Generuj modal/overlay zamiast samego div */
    public bool $withModal = false;
}
