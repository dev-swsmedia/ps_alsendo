<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Unit\Map;

use Alsendo\AlsendoWrapper\Map\MapConfig;
use Alsendo\AlsendoWrapper\Map\MapRegion;
use Alsendo\AlsendoWrapper\Map\MapService;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MapServiceTest extends TestCase
{
    private MapService $service;

    protected function setUp(): void
    {
        $this->service = new MapService();
    }

    // --- render() generuje poprawny HTML ---

    public function testRenderContainsScriptTag(): void
    {
        $config = $this->makeConfig('pl', 'onPointSelected');

        $html = $this->service->render($config);

        $this->assertStringContainsString('<script src="https://map.alsendo.com/v8.7/main.js"></script>', $html);
    }

    public function testRenderContainsCssLink(): void
    {
        $config = $this->makeConfig('pl', 'onPointSelected');

        $html = $this->service->render($config);

        $this->assertStringContainsString('<link rel="stylesheet" href="https://map.alsendo.com/v8.7/main.css"', $html);
    }

    public function testRenderContainsContainerDiv(): void
    {
        $config = $this->makeConfig('pl', 'onPointSelected');

        $html = $this->service->render($config);

        $this->assertStringContainsString('<div id="alsendo-map-widget"', $html);
        $this->assertStringContainsString('width: 100%', $html);
        $this->assertStringContainsString('height: 500px', $html);
    }

    public function testRenderContainsBPWidgetInit(): void
    {
        $config = $this->makeConfig('pl', 'onPointSelected');

        $html = $this->service->render($config);

        $this->assertStringContainsString('BPWidget.init(container, options)', $html);
    }

    public function testRenderContainsCallbackFunction(): void
    {
        $config = $this->makeConfig('pl', 'myCallback');

        $html = $this->service->render($config);

        $this->assertStringContainsString("window['myCallback']", $html);
    }

    // --- applyRegionDefaults() ---

    public function testApplyRegionDefaultsPL(): void
    {
        $config = $this->makeConfig('pl', 'cb');

        $config = $this->service->applyRegionDefaults($config);

        $this->assertSame('pl', $config->language);
        $this->assertSame('PL', $config->countryCodes);
        $this->assertSame(MapRegion::DEFAULT_OPERATORS['pl'], $config->operators);
        $this->assertFalse($config->operatorMarkers);
    }

    public function testApplyRegionDefaultsCZ(): void
    {
        $config = $this->makeConfig('cz', 'cb');

        $config = $this->service->applyRegionDefaults($config);

        $this->assertSame('cz', $config->language);
        $this->assertSame('CZ', $config->countryCodes);
        $this->assertSame(['PPL', 'PACKETA', 'BALIKOVNA', 'ONE_DELIVERY'], $config->operators);
    }

    public function testApplyRegionDefaultsRO(): void
    {
        $config = $this->makeConfig('ro', 'cb');

        $config = $this->service->applyRegionDefaults($config);

        $this->assertSame('ro', $config->language);
        $this->assertSame('RO', $config->countryCodes);
        $this->assertSame(['DPD', 'SAMEDAY', 'FAN_COURIER', 'CARGUS'], $config->operators);
        $this->assertTrue($config->operatorMarkers);
    }

    public function testApplyRegionDefaultsDoesNotOverrideExplicitValues(): void
    {
        $config = $this->makeConfig('pl', 'cb');
        $config->language = 'en';
        $config->countryCodes = 'DE';
        $config->operators = ['DPD'];

        $config = $this->service->applyRegionDefaults($config);

        $this->assertSame('en', $config->language);
        $this->assertSame('DE', $config->countryCodes);
        $this->assertSame(['DPD'], $config->operators);
    }

    // --- validate() ---

    public function testValidateThrowsOnInvalidRegion(): void
    {
        $config = new MapConfig();
        $config->region = 'xx';
        $config->callbackFunctionName = 'cb';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Nieprawidłowy region');

        $this->service->validate($config);
    }

    public function testValidateThrowsOnEmptyCallback(): void
    {
        $config = new MapConfig();
        $config->region = 'pl';
        $config->callbackFunctionName = '';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('callbackFunctionName jest wymagany');

        $this->service->validate($config);
    }

    public function testValidateThrowsOnInvalidJsFunctionName(): void
    {
        $config = new MapConfig();
        $config->region = 'pl';
        $config->callbackFunctionName = '123invalid';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Nieprawidłowa nazwa funkcji');

        $this->service->validate($config);
    }

    public function testValidateAcceptsDottedFunctionName(): void
    {
        $config = new MapConfig();
        $config->region = 'pl';
        $config->callbackFunctionName = 'window.alsendo.onSelect';

        // Nie powinien rzucić wyjątku
        $this->service->validate($config);
        $this->assertTrue(true);
    }

    // --- operators z config nadpisują domyślne ---

    public function testCustomOperatorsOverrideDefaults(): void
    {
        $config = $this->makeConfig('pl', 'cb');
        $config->operators = ['INPOST', 'DPD'];

        $html = $this->service->render($config);

        $this->assertStringContainsString('"operator":"INPOST"', $html);
        $this->assertStringContainsString('"operator":"DPD"', $html);
        $this->assertStringNotContainsString('"operator":"UPS"', $html);
        $this->assertStringNotContainsString('"operator":"GLS"', $html);
    }

    // --- getAssetUrls() ---

    public function testGetAssetUrls(): void
    {
        $config = $this->makeConfig('pl', 'cb');

        $urls = $this->service->getAssetUrls($config);

        $this->assertSame('https://map.alsendo.com/v8.7/main.js', $urls['js']);
        $this->assertSame('https://map.alsendo.com/v8.7/main.css', $urls['css']);
    }

    public function testGetAssetUrlsCustomVersion(): void
    {
        $config = $this->makeConfig('pl', 'cb');
        $config->widgetVersion = 'v9.0';
        $config->widgetBaseUrl = 'https://custom.cdn.com/widget';

        $urls = $this->service->getAssetUrls($config);

        $this->assertSame('https://custom.cdn.com/widget/v9.0/main.js', $urls['js']);
        $this->assertSame('https://custom.cdn.com/widget/v9.0/main.css', $urls['css']);
    }

    // --- Modal ---

    public function testRenderWithModalContainsOverlay(): void
    {
        $config = $this->makeConfig('pl', 'cb');
        $config->withModal = true;

        $html = $this->service->render($config);

        $this->assertStringContainsString('alsendo-map-widget-overlay', $html);
        $this->assertStringContainsString('alsendo-map-widget-modal', $html);
        $this->assertStringContainsString('alsendo-map-widget-close', $html);
        $this->assertStringContainsString('&times;', $html);
    }

    public function testRenderWithoutModalHasNoOverlay(): void
    {
        $config = $this->makeConfig('pl', 'cb');
        $config->withModal = false;

        $html = $this->service->render($config);

        $this->assertStringNotContainsString('overlay', $html);
        $this->assertStringNotContainsString('modal', $html);
    }

    // --- Custom container ---

    public function testCustomContainerDimensions(): void
    {
        $config = $this->makeConfig('cz', 'cb');
        $config->containerHeight = '600px';
        $config->containerWidth = '80%';
        $config->containerId = 'my-map';

        $html = $this->service->render($config);

        $this->assertStringContainsString('id="my-map"', $html);
        $this->assertStringContainsString('height: 600px', $html);
        $this->assertStringContainsString('width: 80%', $html);
    }

    // --- Normalizacja w callbacku ---

    public function testCallbackNormalizesPointData(): void
    {
        $config = $this->makeConfig('pl', 'onSelect');

        $html = $this->service->render($config);

        // Sprawdź że callback normalizuje dane
        $this->assertStringContainsString('point.code', $html);
        $this->assertStringContainsString('point.operator', $html);
        $this->assertStringContainsString('point.street', $html);
        $this->assertStringContainsString('point.postalCode', $html);
        $this->assertStringContainsString('point.city', $html);
        $this->assertStringContainsString('point.latitude', $html);
        $this->assertStringContainsString('point.longitude', $html);
        $this->assertStringContainsString('point.cod', $html);
        $this->assertStringContainsString('raw: point', $html);
    }

    // --- Helper ---

    private function makeConfig(string $region, string $callback): MapConfig
    {
        $config = new MapConfig();
        $config->region = $region;
        $config->callbackFunctionName = $callback;

        return $config;
    }
}
