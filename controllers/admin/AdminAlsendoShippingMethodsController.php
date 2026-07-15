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

require_once _PS_MODULE_DIR_ . 'alsendo/alsendoCarrier.php';
require_once _PS_MODULE_DIR_ . 'alsendo/src/Services/WrapperService.php';

use Alsendo\Services\WrapperService;

class AdminAlsendoShippingMethodsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function initContent()
    {
        parent::initContent();

        $configJson = Configuration::get('ALSENDO_AVAILABLE_SERVICES', null, null, null, '');
        $allServices = [];
        $activeServices = [];

        if ($configJson) {
            $allServices = json_decode($configJson, true);

            $activeServices = array_filter((array) $allServices, function ($service) {
                return isset($service['active']) && $service['active'] === true;
            });

            $activeServices = array_values($activeServices);
        } else {
            $wrapper = new WrapperService();
            $availableServicesResult = $wrapper->getAvailableServices();

            if (is_object($availableServicesResult) && method_exists($availableServicesResult, 'getData')) {
                $data = $availableServicesResult->getData();
                if (isset($data['services'])) {
                    $allServices = $data['services'];

                    foreach ($allServices as &$service) {
                        $service['active'] = true;
                    }

                    $activeServices = $allServices;

                    Configuration::updateValue('ALSENDO_AVAILABLE_SERVICES', json_encode($allServices));
                }
            }
        }

        $methods = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'alsendo_shipping_methods`
             WHERE id_shop=' . (int) $this->context->shop->id
        );

        $region = Configuration::get('ALSENDO_REGION') ?: 'pl';

        $servicesCapabilitiesJson = '[]';
        if ($region === 'cz') {
            $servicesData = Configuration::get('ALSENDO_AVAILABLE_SERVICES');
            if ($servicesData) {
                $servicesCapabilitiesJson = $servicesData;
            }
        }

        $this->context->smarty->assign([
            'available_services' => $activeServices,
            'methods' => $methods,
            'currency_iso' => $this->context->currency->iso_code,
            'token_shipping_config' => Tools::getAdminTokenLite('AdminAlsendoShippingConfiguration'),
            'token_shipping_methods' => Tools::getAdminTokenLite('AdminAlsendoShippingMethods'),
            'alsendo_msg_saved' => $this->l('Saved!'),
            'alsendo_region' => $region,
            'alsendo_services_capabilities_json' => $servicesCapabilitiesJson,
        ]);

        $this->setTemplate('shipping_methods.tpl');
    }

    public function postProcess()
    {
        if (!Tools::getIsset('action')) {
            return;
        }

        $action = Tools::getValue('action');

        try {
            switch ($action) {
                case 'add':
                    $this->addMethod();
                    break;
                case 'update':
                    $this->updateMethod();
                    break;
                case 'update_active':
                    $this->updateActive();
                    break;
                case 'update_map':
                    $this->updateMap();
                    break;
                case 'delete':
                    $this->deleteMethod();
                    break;
                case 'get_available_services':
                    $this->getAvailableServices();
                    break;
                case 'save_available_services':
                    $this->saveAvailableServices();
                    break;
                case 'sync_available_services':
                    $this->syncAvailableServices();
                    break;
                case 'update_ship_via':
                    $this->updateShipVia();
                    break;
                case 'update_pickup_request':
                    $this->updatePickupRequest();
                    break;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            exit(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    private function getAvailableServices()
    {
        $servicesJson = Configuration::get('ALSENDO_AVAILABLE_SERVICES', null, null, null, '');
        $services = $servicesJson ? json_decode($servicesJson, true) : [];

        header('Content-Type: application/json');
        exit(json_encode($services));
    }

    private function saveAvailableServices()
    {
        $servicesParam = Tools::getValue('services');

        $decoded = json_decode($servicesParam, true);

        if (!is_array($decoded)) {
            header('Content-Type: application/json');
            exit(json_encode(['success' => false, 'message' => $this->l('Invalid data format')]));
        }

        $activeCount = 0;
        $inactiveCount = 0;
        foreach ($decoded as $svc) {
            if (isset($svc['active']) && $svc['active'] === true) {
                ++$activeCount;
            } else {
                ++$inactiveCount;
            }
        }

        Configuration::updateValue('ALSENDO_AVAILABLE_SERVICES', json_encode($decoded));

        header('Content-Type: application/json');
        exit(json_encode([
            'success' => true,
            'message' => 'Services saved! (Active: ' . $activeCount . ', Inactive: ' . $inactiveCount . ')',
        ]));
    }

    private function syncAvailableServices()
    {
        $wrapper = new WrapperService();
        $res = $wrapper->getAvailableServices();

        if (method_exists($res, 'isSuccess') && $res->isSuccess()) {
            $data = $res->getData();
            $services = isset($data['services']) && is_array($data['services']) ? $data['services'] : [];

            $existing = Configuration::get('ALSENDO_AVAILABLE_SERVICES', null, null, null, '');
            $existingServices = $existing ? json_decode($existing, true) : [];
            $existingMap = [];

            foreach ($existingServices as $svc) {
                if (isset($svc['service_id'])) {
                    $existingMap[$svc['service_id']] = $svc['active'] ?? true;
                }
            }

            foreach ($services as &$service) {
                if (isset($existingMap[$service['service_id']])) {
                    $service['active'] = $existingMap[$service['service_id']];
                } else {
                    $service['active'] = true;
                }
            }

            Configuration::updateValue('ALSENDO_AVAILABLE_SERVICES', json_encode($services));

            header('Content-Type: application/json');
            exit(json_encode([
                'success' => true,
                'message' => 'Synchronized! ' . count($services) . ' services loaded.',
            ]));
        }

        header('Content-Type: application/json');
        exit(json_encode([
            'success' => false,
            'error' => $res->getError() ?: 'Sync failed',
        ]));
    }

    private function addMethod()
    {
        try {
            $methodName = trim((string) Tools::getValue('method_name'));
            if ($methodName === '') {
                $methodName = 'Alsendo ' . date('Y-m-d H:i:s');
            }

            $serviceId = Tools::getValue('service_id');
            $serviceId = !empty($serviceId) ? pSQL($serviceId) : null;
            $price = (float) Tools::getValue('price');
            $hasMap = (int) Tools::getValue('has_map');

            $delay = [];
            foreach (Language::getLanguages(true) as $lang) {
                $delay[$lang['id_lang']] = 'Delivered in 2–5 days.';
            }

            $carrier = new AlsendoCarrier();
            $idCarrier = $carrier->installModuleCarrier([
                'name' => $methodName,
                'delay' => $delay,
                'is_module' => true,
                'external_module_name' => 'alsendo',
                'active' => true,
                'need_range' => 1,
                'shipping_external' => true,
                'range_behavior' => 0,
                'shipping_method' => Carrier::SHIPPING_METHOD_WEIGHT,
                'grade' => 5,
                'max_weight' => 30,
                'ranges' => [[
                    'delimiter1' => 0,
                    'delimiter2' => 1000,
                    'price' => $price,
                ]],
            ]);

            if (!$idCarrier) {
                header('Content-Type: application/json');
                exit(json_encode(['success' => false, 'error' => 'Failed to create carrier']));
            }

            $insertData = [
                'id_service' => $serviceId,
                'method_name' => pSQL($methodName),
                'price' => (float) $price,
                'has_map' => (int) $hasMap,
                'active' => 1,
                'id_carrier' => (int) $idCarrier,
                'id_shop' => (int) $this->context->shop->id,
                'ship_via_pickup_point' => (int) Tools::getValue('ship_via_pickup_point'),
                'pickup_request' => (int) Tools::getValue('pickup_request'),
            ];

            $result = Db::getInstance()->insert('alsendo_shipping_methods', $insertData);

            if (!$result) {
                header('Content-Type: application/json');
                exit(json_encode(['success' => false, 'error' => Db::getInstance()->getMsgError()]));
            }

            $newId = Db::getInstance()->Insert_ID();

            header('Content-Type: application/json');
            exit(json_encode(['success' => true, 'id' => (int) $newId, 'id_carrier' => (int) $idCarrier]));
        } catch (Exception $e) {
            header('Content-Type: application/json');
            exit(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    private function updateMethod()
    {
        try {
            $id = (int) Tools::getValue('id');
            $name = trim((string) Tools::getValue('method_name'));
            $price = (float) Tools::getValue('price');
            $hasMap = (int) Tools::getValue('has_map');
            $serviceId = Tools::getValue('service_id');
            $serviceId = !empty($serviceId) ? pSQL($serviceId) : null;

            $method = Db::getInstance()->getRow(
                'SELECT `id_carrier` FROM `' . _DB_PREFIX_ . 'alsendo_shipping_methods` WHERE `id_alsendo_shipping_method` = ' . (int) $id
            );

            if (!$method || !$method['id_carrier']) {
                header('Content-Type: application/json');
                exit(json_encode(['success' => false, 'error' => 'Method not found']));
            }

            $id_carrier = (int) $method['id_carrier'];

            $carrier = new Carrier($id_carrier);
            if (Validate::isLoadedObject($carrier)) {
                $carrier->name = pSQL($name);
                $carrier->active = true;
                $carrier->deleted = false;
                $carrier->update();

                if ((int) $carrier->id !== $id_carrier) {
                    $oldCarrierId = $id_carrier;
                    $newCarrierId = (int) $carrier->id;

                    Db::getInstance()->update(
                        'alsendo_shipping_methods',
                        ['id_carrier' => $newCarrierId],
                        '`id_alsendo_shipping_method` = ' . (int) $id
                    );

                    $existingDelivery = Db::getInstance()->executeS(
                        'SELECT * FROM `' . _DB_PREFIX_ . 'delivery` WHERE `id_carrier` = ' . (int) $oldCarrierId
                    );
                    if (!empty($existingDelivery)) {
                        $newDeliveryCount = (int) Db::getInstance()->getValue(
                            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'delivery` WHERE `id_carrier` = ' . $newCarrierId
                        );
                        if ($newDeliveryCount === 0) {
                            foreach ($existingDelivery as $row) {
                                $row['id_carrier'] = $newCarrierId;
                                unset($row['id_delivery']);
                                Db::getInstance()->insert('delivery', $row);
                            }
                        }
                    }

                    $id_carrier = $newCarrierId;
                }
            }

            Db::getInstance()->update(
                'delivery',
                ['price' => (float) $price],
                '`id_carrier` = ' . (int) $id_carrier
            );

            $updateData = [
                'method_name' => pSQL($name),
                'price' => (float) $price,
                'has_map' => (int) $hasMap,
                'id_service' => $serviceId,
                'ship_via_pickup_point' => (int) Tools::getValue('ship_via_pickup_point'),
                'pickup_request' => (int) Tools::getValue('pickup_request'),
            ];

            $ok = Db::getInstance()->update(
                'alsendo_shipping_methods',
                $updateData,
                '`id_alsendo_shipping_method` = ' . (int) $id
            );

            if (!$ok) {
                header('Content-Type: application/json');
                exit(json_encode(['success' => false, 'error' => Db::getInstance()->getMsgError()]));
            }

            header('Content-Type: application/json');
            exit(json_encode(['success' => true, 'id_carrier' => (int) $id_carrier]));
        } catch (Exception $e) {
            header('Content-Type: application/json');
            exit(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    private function updateActive()
    {
        try {
            $id = (int) Tools::getValue('id');
            $active = (int) Tools::getValue('active');

            if (!$id) {
                header('Content-Type: application/json');
                exit(json_encode(['success' => false, 'error' => 'Invalid ID']));
            }

            $ok = Db::getInstance()->update(
                'alsendo_shipping_methods',
                ['active' => $active],
                '`id_alsendo_shipping_method` = ' . (int) $id
            );

            if (!$ok) {
                header('Content-Type: application/json');
                exit(json_encode(['success' => false, 'error' => Db::getInstance()->getMsgError()]));
            }

            header('Content-Type: application/json');
            exit(json_encode(['success' => true]));
        } catch (Exception $e) {
            header('Content-Type: application/json');
            exit(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    private function updateMap()
    {
        try {
            $id = (int) Tools::getValue('id');
            $hasMap = (int) Tools::getValue('has_map');

            if (!$id) {
                header('Content-Type: application/json');
                exit(json_encode(['success' => false, 'error' => 'Invalid ID']));
            }

            $ok = Db::getInstance()->update(
                'alsendo_shipping_methods',
                ['has_map' => $hasMap],
                '`id_alsendo_shipping_method` = ' . (int) $id
            );

            if (!$ok) {
                header('Content-Type: application/json');
                exit(json_encode(['success' => false, 'error' => Db::getInstance()->getMsgError()]));
            }

            header('Content-Type: application/json');
            exit(json_encode(['success' => true]));
        } catch (Exception $e) {
            header('Content-Type: application/json');
            exit(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    private function updateShipVia()
    {
        try {
            $id = (int) Tools::getValue('id');
            $val = (int) Tools::getValue('ship_via_pickup_point');

            if (!$id) {
                header('Content-Type: application/json');
                exit(json_encode(['success' => false, 'error' => 'Invalid ID']));
            }

            $updateData = ['ship_via_pickup_point' => $val];
            if ($val) {
                $updateData['pickup_request'] = 0;
            }

            $ok = Db::getInstance()->update(
                'alsendo_shipping_methods',
                $updateData,
                '`id_alsendo_shipping_method` = ' . (int) $id
            );

            header('Content-Type: application/json');
            exit(json_encode(['success' => (bool) $ok]));
        } catch (Exception $e) {
            header('Content-Type: application/json');
            exit(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    private function updatePickupRequest()
    {
        try {
            $id = (int) Tools::getValue('id');
            $val = (int) Tools::getValue('pickup_request');

            if (!$id) {
                header('Content-Type: application/json');
                exit(json_encode(['success' => false, 'error' => 'Invalid ID']));
            }

            $updateData = ['pickup_request' => $val];
            if ($val) {
                $updateData['ship_via_pickup_point'] = 0;
            }

            $ok = Db::getInstance()->update(
                'alsendo_shipping_methods',
                $updateData,
                '`id_alsendo_shipping_method` = ' . (int) $id
            );

            header('Content-Type: application/json');
            exit(json_encode(['success' => (bool) $ok]));
        } catch (Exception $e) {
            header('Content-Type: application/json');
            exit(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    private function deleteMethod()
    {
        try {
            $id = (int) Tools::getValue('id');

            $method = Db::getInstance()->getRow(
                'SELECT `id_carrier` FROM `' . _DB_PREFIX_ . 'alsendo_shipping_methods`
                 WHERE `id_alsendo_shipping_method` = ' . (int) $id
            );

            if ($method && !empty($method['id_carrier'])) {
                $id_carrier = (int) $method['id_carrier'];
                AlsendoCarrier::deleteCarrier($id_carrier);
            }

            $ok = Db::getInstance()->delete(
                'alsendo_shipping_methods',
                '`id_alsendo_shipping_method` = ' . (int) $id
            );

            header('Content-Type: application/json');
            exit(json_encode(['success' => $ok]));
        } catch (Exception $e) {
            header('Content-Type: application/json');
            exit(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }
}
