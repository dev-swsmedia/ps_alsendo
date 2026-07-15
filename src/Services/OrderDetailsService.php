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

class OrderDetailsService
{
    public function __construct()
    {
    }

    public function getByOrderId(int $idOrder): array
    {
        $row = \Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'alsendo_order_details WHERE id_order=' . (int) $idOrder);
        if (!$row) {
            return ['data' => []];
        }

        return ['data' => json_decode($row['data'], true)];
    }

    public function save(int $idOrder, array $data)
    {
        \Db::getInstance()->execute('REPLACE INTO ' . _DB_PREFIX_ . 'alsendo_order_details (id_order,data,created_at,updated_at) VALUES (' . (int) $idOrder . ',"' . pSQL(json_encode($data)) . '",NOW(),NOW())');
    }

    public function createIfNotExists($orderId, $usePickupPoint = true)
    {
        $exists = \Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'alsendo_order_details` WHERE id_order = ' . (int) $orderId
        );

        if ($exists) {
            return;
        }

        $order = new \Order($orderId);
        $customer = new \Customer($order->id_customer);
        $address = new \Address($order->id_address_delivery);

        $senderTemplate = $this->getDefaultSenderTemplate();
        $packageTemplate = $this->getDefaultPackageTemplate();

        $pickupPoint = null;
        if ($usePickupPoint) {
            $pickupPointData = \Db::getInstance()->getRow(
                'SELECT pickup_point FROM `' . _DB_PREFIX_ . 'alsendo_order_pickup` WHERE id_cart = ' . (int) $order->id_cart
            );
            if ($pickupPointData && !empty($pickupPointData['pickup_point'])) {
                $pickupPoint = $pickupPointData['pickup_point'];
            }
        }

        $data = [
            'sender' => $senderTemplate ? $this->mapSenderTemplateToData($senderTemplate) : [],
            'package' => $packageTemplate ? $this->mapPackageTemplateToData($packageTemplate) : [],
            'recipient' => [
                'first_name' => $address->firstname,
                'last_name' => $address->lastname,
                'company' => $address->company,
                'address' => $address->address1,
                'address2' => $address->address2,
                'city' => $address->city,
                'postal_code' => $address->postcode,
                'country' => \Country::getIsoById($address->id_country),
                'phone' => $address->phone,
                'email' => $customer->email,
                'pickup_point' => $pickupPoint,
            ],
            'shipment' => [
                'pickup_point' => $pickupPoint,
                'preferred_pickup_date' => PickupHoursService::getDefaultPickupDate(),
            ],
        ];

        $this->save($orderId, $data);

        return $data;
    }

    private function getDefaultSenderTemplate()
    {
        $senderTemplates = json_decode(\Configuration::get('ALSENDO_SENDER_LIST', null, null, null, ''), true);
        if (empty($senderTemplates)) {
            return null;
        }

        foreach ($senderTemplates as $template) {
            if (isset($template['main']) && $template['main']) {
                return $template;
            }
        }

        return $senderTemplates[0] ?? null;
    }

    private function getDefaultPackageTemplate()
    {
        $shippingTemplates = json_decode(\Configuration::get('ALSENDO_SHIPPING_SETTINGS_LIST', null, null, null, ''), true);
        if (empty($shippingTemplates)) {
            return null;
        }

        foreach ($shippingTemplates as $template) {
            if (isset($template['main']) && $template['main']) {
                return $template;
            }
        }

        return $shippingTemplates[0] ?? null;
    }

    private function mapSenderTemplateToData($template)
    {
        return [
            'template_name' => $template['template_name'] ?? '',
            'company_name' => $template['company'] ?? '',
            'first_name' => $template['firstname'] ?? '',
            'last_name' => $template['lastname'] ?? '',
            'full_name' => trim(($template['firstname'] ?? '') . ' ' . ($template['lastname'] ?? '')),
            'street' => $template['street'] ?? '',
            'building_number' => $template['building'] ?? '',
            'apartment_number' => $template['apartment'] ?? '',
            'postal_code' => $template['postal'] ?? '',
            'city' => $template['city'] ?? '',
            'country' => ['pl' => 'PL', 'cz' => 'CZ', 'ro' => 'RO'][\Configuration::get('ALSENDO_REGION') ?: 'pl'] ?? 'PL',
            'phone_number' => $template['phone'] ?? '',
            'email' => $template['email'] ?? '',
            'bank_account_number' => $template['bank'] ?? '',
        ];
    }

    private function mapPackageTemplateToData($template)
    {
        $odDefaultPkgType = ((\Configuration::get('ALSENDO_REGION') ?: 'pl') === 'cz') ? 'PACKAGE' : (((\Configuration::get('ALSENDO_REGION') ?: 'pl') === 'ro') ? 'package' : 'PACZKA');

        return [
            'template_name' => $template['alsendo_template_name'] ?? '',
            'width' => (float) ($template['alsendo_width'] ?? 0),
            'length' => (float) ($template['alsendo_length'] ?? 0),
            'height' => (float) ($template['alsendo_height'] ?? 0),
            'weight' => (float) ($template['alsendo_weight'] ?? 0),
            'package_type' => $template['alsendo_package_type'] ?? $odDefaultPkgType,
            'shipment_content' => $template['alsendo_shipment_content'] ?? '',
            'package_content' => $template['alsendo_shipment_content'] ?? '',
            'shipment_packaging' => $template['alsendo_package_type'] ?? $odDefaultPkgType,
            'shipment_type' => $template['alsendo_package_type'] ?? $odDefaultPkgType,
            'is_nstd' => (int) ($template['alsendo_is_nstd'] ?? 0),
            'cash_on_delivery' => (float) ($template['alsendo_cod'] ?? 0),
            'cod_value' => (float) ($template['alsendo_cod'] ?? 0),
            'declared_value' => (float) ($template['alsendo_declared_value'] ?? 0),
            'pickup_type' => $template['alsendo_pickup_type'] ?? self::getConfiguredPickupType(),
        ];
    }

    public static function getConfiguredPickupType(string $region = null): string
    {
        $region = $region ?: (\Configuration::get('ALSENDO_REGION') ?: 'pl');

        $shippingTemplates = json_decode(\Configuration::get('ALSENDO_SHIPPING_SETTINGS_LIST', null, null, null, ''), true);
        if (!empty($shippingTemplates)) {
            $template = null;
            foreach ($shippingTemplates as $tpl) {
                if (!empty($tpl['main'])) {
                    $template = $tpl;
                    break;
                }
            }
            if (!$template) {
                $template = $shippingTemplates[0];
            }

            if (!empty($template['alsendo_pickup_type'])) {
                $val = str_replace('_', '', strtoupper($template['alsendo_pickup_type']));
                if ($region === 'cz' && in_array($val, ['ONDEMAND', 'OCCASIONAL'])) {
                    return $val;
                }
                if ($region === 'pl' && in_array($val, ['SELF', 'COURIER', 'NOPICKUP'])) {
                    return $val;
                }
                if ($region === 'ro' && in_array($val, ['COURIER', 'SELF'])) {
                    return $val;
                }
            }
        }

        $fallbacks = ['cz' => 'ONDEMAND', 'ro' => 'COURIER', 'pl' => 'COURIER'];

        return $fallbacks[$region] ?? 'COURIER';
    }

    public static function syncTrackingNumber(int $orderId, string $trackingNumber): void
    {
        $order = new \Order($orderId);
        if (!\Validate::isLoadedObject($order)) {
            return;
        }

        $shipping = $order->getShipping();
        if (empty($shipping)) {
            return;
        }

        $lastShipping = end($shipping);
        if (!isset($lastShipping['id_order_carrier'])) {
            return;
        }

        $oc = new \OrderCarrier((int) $lastShipping['id_order_carrier']);
        if (\Validate::isLoadedObject($oc)) {
            $oc->tracking_number = $trackingNumber;
            $oc->update();
        }
    }
}
