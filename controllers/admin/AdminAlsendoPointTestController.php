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

$_alsendoModulePath = _PS_MODULE_DIR_ . 'alsendo/alsendo.php';
if (file_exists($_alsendoModulePath)) {
    require_once $_alsendoModulePath;
}
if (class_exists('Alsendo', false) && method_exists('Alsendo', 'bootstrapAutoloader')) {
    Alsendo::bootstrapAutoloader();
}

use Alsendo\AlsendoWrapper\Map\MapConfig;
use Alsendo\AlsendoWrapper\Map\MapService;

class AdminAlsendoPointTestController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function checkAccess()
    {
        if (Tools::getIsset('ajax')) {
            return true;
        }

        return parent::checkAccess();
    }

    public function initContent()
    {
        if (Tools::getIsset('ajax')) {
            $this->handleAjax();

            return;
        }

        $this->context->smarty->assign([
            'ajax_url' => $this->context->link->getAdminLink('AdminAlsendoPointTest'),
            'operators' => [
                'INPOST' => 'InPost',
                'DPD' => 'DPD',
                'POCZTA' => 'Poczta Polska',
                'UPS' => 'UPS',
                'FEDEX' => 'FedEx',
                'RUCH' => 'Ruch',
            ],
        ]);

        $this->setTemplate('point_test.tpl');
    }

    private function handleAjax()
    {
        $action = Tools::getValue('action');

        if ($action === 'search_points') {
            $this->searchPoints();

            return;
        }

        if ($action === 'search_by_address') {
            $this->searchByAddress();

            return;
        }

        if ($action === 'search_unified') {
            $this->searchUnified();

            return;
        }

        if ($action === 'render_map') {
            $this->renderMap();

            return;
        }

        $this->jsonResponse(['success' => false, 'error' => 'Unknown action']);
    }

    private function searchPoints()
    {
        $this->jsonResponse(['success' => false, 'error' => 'BliskaPaczkaPointService nie jest jeszcze zaimplementowany w wrapper-v2. Uzyj panelu "Test widgetu mapy" ponizej.']);
    }

    private function searchByAddress()
    {
        $this->jsonResponse(['success' => false, 'error' => 'BliskaPaczkaPointService nie jest jeszcze zaimplementowany w wrapper-v2. Uzyj panelu "Test widgetu mapy" ponizej.']);
    }

    private function searchUnified()
    {
        $this->jsonResponse(['success' => false, 'error' => 'PickupPointService nie jest jeszcze zaimplementowany w wrapper-v2. Uzyj panelu "Test widgetu mapy" ponizej.']);
    }

    private function renderMap()
    {
        $region = Tools::getValue('region', 'pl');
        $posType = Tools::getValue('pos_type', 'DELIVERY');
        $height = Tools::getValue('height', '500px');
        $testMode = (bool) Tools::getValue('test_mode', false);
        $codeSearch = (bool) Tools::getValue('code_search', true);
        $operatorMarkers = (bool) Tools::getValue('operator_markers', false);
        $operators = Tools::getValue('operators', '');
        $callbackName = Tools::getValue('callback', 'alsendoMapTestCallback');
        $alias = Tools::getValue('alias', '');

        try {
            $config = new MapConfig();
            $config->region = $region;
            $config->callbackFunctionName = $callbackName;
            $config->posType = $posType;
            $config->containerHeight = $height;
            $config->testMode = $testMode;
            $config->codeSearch = $codeSearch;
            $config->operatorMarkers = $operatorMarkers;

            if (!empty($operators)) {
                $config->operators = array_map('trim', explode(',', $operators));
            }

            if (!empty($alias)) {
                $config->alias = $alias;
            }

            $mapService = new MapService();
            $html = $mapService->render($config);

            $this->jsonResponse([
                'success' => true,
                'html' => $html,
                'config_applied' => [
                    'region' => $config->region,
                    'language' => $config->language,
                    'countryCodes' => $config->countryCodes,
                    'operators' => $config->operators,
                    'posType' => $config->posType,
                    'testMode' => $config->testMode,
                ],
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        exit(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
