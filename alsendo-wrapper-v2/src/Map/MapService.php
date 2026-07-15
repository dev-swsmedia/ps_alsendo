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

class MapService
{
    /**
     * Zwraca gotowy HTML+JS string z widgetem mapy do wstawienia w dowolną stronę.
     */
    public function render(MapConfig $config): string
    {
        $config = $this->applyRegionDefaults($config);
        $this->validate($config);

        $html = '';
        $html .= $this->renderAssets($config);
        $html .= $this->renderContainer($config);
        $html .= $this->renderInitScript($config);

        return $html;
    }

    /**
     * Zwraca tylko URL do JS i CSS — dla aplikacji które same zarządzają assetami.
     */
    public function getAssetUrls(MapConfig $config): array
    {
        $base = rtrim($config->widgetBaseUrl, '/');
        $version = $config->widgetVersion;

        return [
            'js' => $base . '/' . $version . '/main.js',
            'css' => $base . '/' . $version . '/main.css',
        ];
    }

    /**
     * Wypełnia domyślne wartości na podstawie regionu.
     */
    public function applyRegionDefaults(MapConfig $config): MapConfig
    {
        if ($config->language === null && isset(MapRegion::LANGUAGES[$config->region])) {
            $config->language = MapRegion::LANGUAGES[$config->region];
        }

        if ($config->countryCodes === null && isset(MapRegion::COUNTRY_CODES[$config->region])) {
            $config->countryCodes = MapRegion::COUNTRY_CODES[$config->region];
        }

        if ($config->operators === null && isset(MapRegion::DEFAULT_OPERATORS[$config->region])) {
            $config->operators = MapRegion::DEFAULT_OPERATORS[$config->region];
        }

        // RO domyślnie z operatorMarkers
        if ($config->region === MapRegion::RO) {
            $config->operatorMarkers = true;
        }

        return $config;
    }

    /**
     * Waliduje konfigurację.
     *
     * @throws \InvalidArgumentException
     */
    public function validate(MapConfig $config): void
    {
        if (!in_array($config->region, MapRegion::ALL, true)) {
            throw new \InvalidArgumentException(sprintf('Nieprawidłowy region: "%s". Dozwolone: %s', $config->region, implode(', ', MapRegion::ALL)));
        }

        if (empty($config->callbackFunctionName)) {
            throw new \InvalidArgumentException('callbackFunctionName jest wymagany');
        }

        if (!preg_match('/^[a-zA-Z_$][a-zA-Z0-9_$.]*$/', $config->callbackFunctionName)) {
            throw new \InvalidArgumentException(sprintf('Nieprawidłowa nazwa funkcji JS: "%s"', $config->callbackFunctionName));
        }
    }

    private function renderAssets(MapConfig $config): string
    {
        $urls = $this->getAssetUrls($config);

        return sprintf(
            '<link rel="stylesheet" href="%s" />' . "\n" . '<script src="%s"></script>' . "\n",
            $this->escapeHtmlAttr($urls['css']),
            $this->escapeHtmlAttr($urls['js'])
        );
    }

    private function renderContainer(MapConfig $config): string
    {
        $containerId = $this->escapeHtmlAttr($config->containerId);
        $width = $this->escapeHtmlAttr($config->containerWidth);
        $height = $this->escapeHtmlAttr($config->containerHeight);

        $widgetDiv = sprintf(
            '<div id="%s" style="width: %s; height: %s;"></div>',
            $containerId,
            $width,
            $height
        );

        if (!$config->withModal) {
            return $widgetDiv . "\n";
        }

        $overlayId = $this->escapeHtmlAttr($config->containerId . '-overlay');
        $modalId = $this->escapeHtmlAttr($config->containerId . '-modal');
        $closeId = $this->escapeHtmlAttr($config->containerId . '-close');

        // Div wewnątrz modala — 100% szerokości i wysokości
        $innerDiv = sprintf(
            '<div id="%s" style="width: 100%%; height: 100%%;"></div>',
            $containerId
        );

        return sprintf(
            '<div id="%s" style="display:none; position:fixed; top:0; left:0; width:100%%; height:100%%; background:rgba(0,0,0,0.5); z-index:9999;">' . "\n"
            . '  <div id="%s" style="position:absolute; top:5%%; left:5%%; width:90%%; height:90%%; background:#fff; border-radius:8px; overflow:hidden;">' . "\n"
            . '    <button id="%s" style="position:absolute; top:10px; right:10px; z-index:10000; background:#fff; border:1px solid #ccc; border-radius:50%%; width:32px; height:32px; cursor:pointer; font-size:18px; line-height:1;" type="button">&times;</button>' . "\n"
            . '    %s' . "\n"
            . '  </div>' . "\n"
            . '</div>' . "\n",
            $overlayId,
            $modalId,
            $closeId,
            $innerDiv
        );
    }

    private function renderInitScript(MapConfig $config): string
    {
        $containerId = $this->escapeJs($config->containerId);
        $callbackName = $this->escapeJs($config->callbackFunctionName);

        $widgetOptions = $this->buildWidgetOptions($config);
        $optionsJson = json_encode($widgetOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $modalScript = '';
        if ($config->withModal) {
            $overlayId = $this->escapeJs($config->containerId . '-overlay');
            $closeId = $this->escapeJs($config->containerId . '-close');
            $modalScript = <<<JS

    // Modal controls
    var overlay = document.getElementById('{$overlayId}');
    var closeBtn = document.getElementById('{$closeId}');
    if (closeBtn && overlay) {
        closeBtn.addEventListener('click', function() { overlay.style.display = 'none'; });
        overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.style.display = 'none'; });
    }
JS;
        }

        return <<<HTML
<script>
(function() {
    function initAlsendoMap() {
        if (typeof BPWidget === 'undefined') {
            setTimeout(initAlsendoMap, 100);
            return;
        }
        var container = document.getElementById('{$containerId}');
        if (!container) {
            setTimeout(initAlsendoMap, 100);
            return;
        }

        var options = {$optionsJson};
        options.callback = function(point) {
            var normalized = {
                code: point.code || '',
                operator: point.operator || '',
                name: point.description || point.street || '',
                street: point.street || '',
                postalCode: point.postalCode || '',
                city: point.city || '',
                province: point.province || '',
                latitude: point.latitude || null,
                longitude: point.longitude || null,
                hasCod: !!point.cod,
                raw: point
            };
            if (typeof window['{$callbackName}'] === 'function') {
                window['{$callbackName}'](normalized);
            }
        };

        BPWidget.init(container, options);{$modalScript}
    }
    initAlsendoMap();
})();
</script>
HTML;
    }

    private function buildWidgetOptions(MapConfig $config): array
    {
        $options = [];

        // Operatorzy
        if (!empty($config->operators)) {
            $operators = [];
            foreach ($config->operators as $op) {
                $operators[] = ['operator' => $op];
            }
            $options['operators'] = $operators;
        }

        $options['posType'] = $config->posType;

        if ($config->language !== null) {
            $options['language'] = $config->language;
        }

        if ($config->countryCodes !== null) {
            $options['countryCodes'] = $config->countryCodes;
        }

        $options['codeSearch'] = $config->codeSearch;
        $options['testMode'] = $config->testMode;

        if ($config->operatorMarkers) {
            $options['operatorMarkers'] = true;
        }

        if ($config->codOnly !== null) {
            $options['codOnly'] = $config->codOnly;
        }

        if ($config->showCod !== null) {
            $options['showCod'] = $config->showCod;
        }

        if ($config->initialAddress !== null) {
            $options['initialAddress'] = $config->initialAddress;
        }

        if ($config->selectedPoint !== null) {
            $options['selectedPoint'] = $config->selectedPoint;
        }

        if ($config->alias !== null) {
            $options['alias'] = $config->alias;
        }

        if ($config->prices !== null) {
            $options['prices'] = $config->prices;
        }

        if ($config->hideFilters !== null) {
            $options['hideFilters'] = $config->hideFilters;
        }

        if ($config->mapOnly !== null) {
            $options['mapOnly'] = $config->mapOnly;
        }

        return $options;
    }

    private function escapeHtmlAttr(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function escapeJs(string $value): string
    {
        return addcslashes($value, "\\'\"\n\r\t");
    }
}
