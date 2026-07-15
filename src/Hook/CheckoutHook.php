<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\Services\MapBridge;
use Alsendo\Services\OrderDetailsService;

class CheckoutHook
{
    private $module;
    private $orderDetailsService;
    private $mapBridge;

    public function __construct($module)
    {
        $this->module = $module;
        $this->orderDetailsService = new OrderDetailsService();
        $this->mapBridge = new MapBridge();
    }

    public function hookDisplayAfterCarrier($params)
    {
        $cart = $params['cart'] ?? null;

        if (!$cart) {
            return '';
        }

        $alsendoCarriers = $this->getAlsendoCarriers();

        if (empty($alsendoCarriers)) {
            return '';
        }

        $pickupPoint = '';
        $pickupPointDisplay = '';

        $row = \Db::getInstance()->getRow(
            'SELECT pickup_point, pickup_point_display
         FROM `' . _DB_PREFIX_ . 'alsendo_order_pickup`
         WHERE id_cart=' . (int) $cart->id
        );

        if ($row) {
            $pickupPoint = $row['pickup_point'] ?? '';
            $pickupPointDisplay = $row['pickup_point_display'] ?? '';
        }

        $countryIso = 'PL';
        if (!empty($cart->id_address_delivery)) {
            $address = new \Address($cart->id_address_delivery);
            if ($address->id_country) {
                $countryIso = \Country::getIsoById($address->id_country);
            }
        }

        $context = \Context::getContext();

        $region = (\Configuration::get('ALSENDO_REGION') ?: 'pl');

        $deliveryCity = '';
        if (!empty($cart->id_address_delivery)) {
            $deliveryAddress = new \Address($cart->id_address_delivery);
            $deliveryCity = trim($deliveryAddress->postcode . ' ' . $deliveryAddress->city);
        }

        $mapData = $this->mapBridge->getMapTemplateDataSafe($region, $deliveryCity);

        $shippingMethodsJson = json_encode(
            $alsendoCarriers,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        ) ?: '[]';

        $context->smarty->assign([
            'alsendo_cart' => $cart,
            'alsendo_shipping_methods_json' => $shippingMethodsJson,
            'alsendo_pickup_point' => $pickupPoint,
            'alsendo_pickup_point_display' => $pickupPointDisplay,
            'alsendo_shipping_country' => $countryIso,
            'alsendo_map_css_url' => $mapData['css_url'],
            'alsendo_map_js_url' => $mapData['js_url'],
            'alsendo_map_modal_container_id' => $mapData['container_id'],
            'alsendo_map_config' => $mapData['config_json'],
        ]);

        $output = $this->module->display(
            $this->module->getLocalPath(),
            'views/templates/front/carrier_map.tpl'
        );

        return $output;
    }

    private function getAlsendoCarriers(): array
    {
        $carriers = [];

        $rows = \Db::getInstance()->executeS(
            'SELECT
            m.id_carrier,
            m.id_service,
            m.method_name as name,
            m.has_map as map,
            c.name as carrier_name
         FROM `' . _DB_PREFIX_ . 'alsendo_shipping_methods` m
         LEFT JOIN `' . _DB_PREFIX_ . 'carrier` c ON m.id_carrier = c.id_carrier
         WHERE m.id_shop = ' . (int) \Context::getContext()->shop->id . '
         AND m.active = 1'
        );

        if (!$rows) {
            return $carriers;
        }

        $servicesCfg = json_decode(\Configuration::get('ALSENDO_AVAILABLE_SERVICES', null, null, null, ''), true) ?: [];
        $servicesIndex = [];
        foreach ($servicesCfg as $svc) {
            if (!empty($svc['service_id'])) {
                $servicesIndex[(string) $svc['service_id']] = $svc;
            }
        }

        $countryIso = 'PL';
        $context = \Context::getContext();
        if ($context->cart && !empty($context->cart->id_address_delivery)) {
            $address = new \Address($context->cart->id_address_delivery);
            if ($address->id_country) {
                $countryIso = \Country::getIsoById($address->id_country);
            }
        }

        foreach ($rows as $row) {
            $serviceId = $row['id_service'] ?? null;
            $serviceInfo = $serviceId ? ($servicesIndex[(string) $serviceId] ?? null) : null;
            $operator = $this->getOperatorFromService($serviceInfo, $countryIso);

            $carriers[] = [
                'id_carrier' => (int) $row['id_carrier'],
                'id_service' => $serviceId,
                'name' => $row['name'] ?? $row['carrier_name'] ?? '',
                'supplier' => $serviceInfo['supplier'] ?? '',
                'courier' => $operator,
                'operator' => $operator,
                'map' => (bool) $row['map'],
                'country' => $countryIso,
            ];
        }

        return $carriers;
    }

    private function getOperatorFromService(?array $serviceInfo, string $countryIso = null): ?string
    {
        if (!$serviceInfo) {
            return null;
        }

        $region = (\Configuration::get('ALSENDO_REGION') ?: 'pl');
        $supplier = $serviceInfo['supplier'] ?? '';
        $serviceName = $serviceInfo['name'] ?? '';

        return $this->mapBridge->resolveMapOperator($region, $supplier, $serviceName, $countryIso);
    }

    public function hookActionValidateOrder($params)
    {
        $order = $params['order'] ?? null;
        $cart = $params['cart'] ?? null;

        if (!$order || !$cart) {
            return;
        }

        $carrier = new \Carrier($order->id_carrier);
        if ($carrier->external_module_name !== 'alsendo') {
            return;
        }

        $this->orderDetailsService->createIfNotExists($order->id);

        \Db::getInstance()->getRow(
            'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_details`
             WHERE id_order=' . (int) $order->id
        );
    }

    public function hookActionCarrierProcess($params)
    {
        $cart = $params['cart'] ?? null;
        if (!$cart) {
            return;
        }

        $carrier = new \Carrier($cart->id_carrier ?? 0);
        if ($carrier->external_module_name !== 'alsendo') {
            return;
        }

        $requiresMap = false;
        $row = \Db::getInstance()->getRow(
            'SELECT has_map FROM `' . _DB_PREFIX_ . 'alsendo_shipping_methods`
             WHERE id_carrier=' . (int) $carrier->id . '
             AND id_shop=' . (int) \Context::getContext()->shop->id . '
             AND active=1'
        );
        if ($row) {
            $requiresMap = (bool) $row['has_map'];
        }

        if ($requiresMap) {
            $pickupPoint = \Db::getInstance()->getValue(
                'SELECT pickup_point FROM `' . _DB_PREFIX_ . 'alsendo_order_pickup`
                 WHERE id_cart=' . (int) $cart->id
            );

            if (empty($pickupPoint)) {
                $context = \Context::getContext();
                if (isset($context->controller)) {
                    $context->controller->errors[] = $this->l('Please select a pickup point');
                }

                return false;
            }
        }

        return true;
    }

    private function l(string $string): string
    {
        return $this->module->l($string, 'CheckoutHook');
    }
}
