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

require_once __DIR__ . '/../../src/Services/WrapperService.php';

use Alsendo\Services\WrapperService;

class AdminAlsendoShippingConfigurationController extends ModuleAdminController
{
    private WrapperService $wrapperService;

    public function displayAjax()
    {
        $this->postProcess();
    }

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $this->wrapperService = new WrapperService();
    }

    public function initContent()
    {
        parent::initContent();
        $shipping_settings_list = Configuration::get('ALSENDO_SHIPPING_SETTINGS_LIST', null, null, null, '');
        $this->context->smarty->assign([
            'alsendo_shipping_settings_list' => $shipping_settings_list,
        ]);
        $this->setTemplate('shipping_configuration.tpl');
    }

    public function postProcess()
    {
        if (Tools::getIsset('ajax') && Tools::getIsset('action')) {
            $action = Tools::getValue('action');
            $result = ['error' => false, 'message' => 'Saved!'];
            switch ($action) {
                case 'get_shipping_methods':
                    $methods = Configuration::get('ALSENDO_SHIPPING_METHODS_LIST', null, null, null, '');
                    $methods = $methods ? json_decode($methods, true) : [];
                    header('Content-Type: application/json');
                    exit(json_encode($methods));
                case 'save_shipping_methods':
                    $methods_list = Tools::getValue('alsendo_shipping_methods_list');
                    $decoded = json_decode($methods_list, true);
                    $filtered = [];
                    if (is_array($decoded)) {
                        $existingCarriers = Carrier::getCarriers((int) Context::getContext()->language->id, false, false, false, null, 0);
                        foreach ($decoded as $method) {
                            $carrierId = null;
                            foreach ($existingCarriers as $carrierData) {
                                if ($carrierData['external_module_name'] === 'alsendo' && $carrierData['name'] === $method['name']) {
                                    $carrierId = $carrierData['id_carrier'];
                                    break;
                                }
                            }
                            $filtered_method = [
                                'name' => isset($method['name']) ? $method['name'] : '',
                                'courier' => isset($method['courier']) ? $method['courier'] : '',
                                'supplier' => isset($method['supplier']) ? $method['supplier'] : '',
                                'price' => isset($method['price']) ? $method['price'] : '',
                                'map' => !empty($method['map']) ? true : false,
                                'id_carrier' => $carrierId,
                                'method' => $method,
                            ];
                            $filtered[] = $filtered_method;
                        }
                    }
                    Configuration::updateValue('ALSENDO_SHIPPING_METHODS_LIST', json_encode($filtered));

                    require_once _PS_MODULE_DIR_ . 'alsendo/alsendoCarrier.php';
                    $existingCarriers = Carrier::getCarriers((int) Context::getContext()->language->id, false, false, false, null, 0);
                    $newNames = array_map(function ($m) {
                        return $m['name'];
                    }, $filtered);
                    foreach ($existingCarriers as $carrierData) {
                        if ($carrierData['external_module_name'] === 'alsendo' && !in_array($carrierData['name'], $newNames)) {
                            $carrierObj = new Carrier($carrierData['id_carrier']);
                            $carrierObj->deleted = true;
                            $carrierObj->active = false;
                            $carrierObj->save();
                        }
                    }

                    foreach ($filtered as $method) {
                        $carrierId = null;
                        foreach ($existingCarriers as $carrierData) {
                            if ($carrierData['external_module_name'] === 'alsendo' && $carrierData['name'] === $method['name']) {
                                $carrierId = $carrierData['id_carrier'];
                                break;
                            }
                        }
                        $method['id_carrier'] = $carrierId;
                        $delayMap = [];
                        foreach (Language::getLanguages(true) as $lang) {
                            $delayMap[$lang['id_lang']] = '1-2 days';
                        }
                        $carrierProps = [
                            'name' => $method['name'],
                            'delay' => $delayMap,
                            'active' => true,
                            'deleted' => false,
                            'is_module' => true,
                            'shipping_external' => true,
                            'external_module_name' => 'alsendo',
                            'is_free' => false,
                            'shipping_handling' => true,
                            'need_range' => true,
                            'range_behavior' => false,
                            'shipping_method' => 2,
                            'grade' => 5,
                            'max_weight' => 0,
                            'max_width' => 0,
                            'max_depth' => 0,
                            'max_height' => 0,
                            'img' => '',
                            'ranges' => [
                                ['delimiter1' => 0, 'delimiter2' => 9999, 'price' => (float) $method['price']],
                            ],
                        ];
                        if ($carrierId) {
                            $carrierObj = new Carrier($carrierId);
                            $carrierObj->name = $carrierProps['name'];
                            // @phpstan-ignore-next-line — Carrier::$delay is multilang array<id_lang, string> in PS internals
                            $carrierObj->delay = $carrierProps['delay'];
                            $carrierObj->active = $carrierProps['active'];
                            $carrierObj->deleted = $carrierProps['deleted'];
                            $carrierObj->is_module = $carrierProps['is_module'];
                            $carrierObj->shipping_external = $carrierProps['shipping_external'];
                            $carrierObj->external_module_name = $carrierProps['external_module_name'];
                            $carrierObj->is_free = $carrierProps['is_free'];
                            $carrierObj->shipping_handling = $carrierProps['shipping_handling'];
                            $carrierObj->need_range = $carrierProps['need_range'];
                            $carrierObj->range_behavior = $carrierProps['range_behavior'];
                            $carrierObj->shipping_method = $carrierProps['shipping_method'];
                            $carrierObj->grade = $carrierProps['grade'];
                            $carrierObj->max_weight = $carrierProps['max_weight'];
                            $carrierObj->max_width = $carrierProps['max_width'];
                            $carrierObj->max_depth = $carrierProps['max_depth'];
                            $carrierObj->max_height = $carrierProps['max_height'];
                            $carrierObj->save();
                            $taxRules = TaxRulesGroup::getTaxRulesGroups(true);
                            if (!empty($taxRules)) {
                                $carrierObj->setTaxRulesGroup($taxRules[count($taxRules) - 1]['id_tax_rules_group'], true);
                            }
                        } else {
                            $carrier = new AlsendoCarrier();
                            $carrierId = $carrier->installModuleCarrier($carrierProps);
                            if ($carrierId) {
                                $carrierObj = new Carrier($carrierId);
                                $taxRules = TaxRulesGroup::getTaxRulesGroups(true);
                                if (!empty($taxRules)) {
                                    $carrierObj->setTaxRulesGroup($taxRules[count($taxRules) - 1]['id_tax_rules_group'], true);
                                }
                            }
                        }
                        if ($carrierId) {
                            $carrierObj = new Carrier($carrierId);
                            $zones = Zone::getZones(true);
                            foreach ($zones as $zone) {
                                $exists = (int) Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'carrier_zone WHERE id_carrier=' . (int) $carrierId . ' AND id_zone=' . (int) $zone['id_zone']);
                                if (!$exists) {
                                    $carrierObj->addZone($zone['id_zone']);
                                }
                            }
                            $groups = Group::getGroups($this->context->language->id);
                            $carrierObj->setGroups(array_map(function ($g) {
                                return $g['id_group'];
                            }, $groups));
                            $existingRangeId = (int) Db::getInstance()->getValue('SELECT id_range_price FROM ' . _DB_PREFIX_ . 'range_price WHERE id_carrier=' . (int) $carrierId . ' AND delimiter1=0 AND delimiter2=9999');
                            if (!$existingRangeId) {
                                $rangePrice = new RangePrice();
                                $rangePrice->id_carrier = $carrierId;
                                $rangePrice->delimiter1 = 0;
                                $rangePrice->delimiter2 = 9999;
                                $rangePrice->add();
                                $rangeId = $rangePrice->id;
                            } else {
                                $rangeId = $existingRangeId;
                            }
                            foreach ($zones as $zone) {
                                Db::getInstance()->insert('delivery', [
                                    'id_carrier' => $carrierId,
                                    'id_zone' => $zone['id_zone'],
                                    'id_range_price' => $rangeId,
                                    'price' => (float) $method['price'],
                                ]);
                            }
                        }
                    }
                    break;
                case 'get_available_services':
                    $servicesJson = Configuration::get('ALSENDO_AVAILABLE_SERVICES', null, null, null, '');
                    $services = $servicesJson ? json_decode($servicesJson, true) : [];

                    $activeOnly = array_filter($services, function ($service) {
                        return isset($service['active']) && $service['active'] === true;
                    });

                    header('Content-Type: application/json');
                    exit(json_encode(array_values($activeOnly)));
                case 'save_available_services':
                    $servicesParam = Tools::getValue('services');
                    $decoded = json_decode($servicesParam, true);

                    if (!is_array($decoded) || empty($decoded)) {
                        header('Content-Type: application/json');
                        exit(json_encode(['error' => true, 'message' => $this->l('Invalid data')]));
                    }

                    Configuration::updateValue('ALSENDO_AVAILABLE_SERVICES', json_encode($decoded));

                    header('Content-Type: application/json');
                    exit(json_encode(['error' => false, 'message' => $this->l('Saved!')]));
                case 'sync_available_services':
                    $res = $this->wrapperService->getAvailableServices();
                    if (method_exists($res, 'isSuccess') && $res->isSuccess()) {
                        $data = $res->getData();
                        $services = isset($data['services']) && is_array($data['services']) ? $data['services'] : [];
                        Configuration::updateValue('ALSENDO_AVAILABLE_SERVICES', json_encode($services));
                        header('Content-Type: application/json');
                        exit(json_encode(['message' => 'Synchronized!', 'services' => $services]));
                    }
                    header('Content-Type: application/json');
                    exit(json_encode(['error' => $res->getError() ?: 'Sync failed']));
                default:
                    $result = ['error' => true, 'message' => 'Unknown action'];
            }
            header('Content-Type: application/json');
            exit(json_encode($result));
        }
    }
}
