<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\Hook;

use Alsendo\Services\AddressParser;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/../Services/AddressParser.php';

class AdminOrderHook
{
    private $module;
    private $context;

    public function __construct($module)
    {
        $this->module = $module;
        $this->context = \Context::getContext();
    }

    public function hookDisplayAdminOrderSide($params)
    {
        $idOrder = (int) ($params['id_order'] ?? \Tools::getValue('id_order'));

        $shipmentRow = \Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'alsendo_order_shipment`
             WHERE id_order=' . (int) $idOrder
        );

        $alsendo_sender = [];
        $alsendo_shipping_address = [];
        $alsendo_package = [];
        $alsendo_shipping = [];

        if ($shipmentRow) {
            $hasSubmitted = (
                in_array($shipmentRow['status'] ?? '', ['submitted', 'ordered', 'created', 'new', 'success'], true)
                || !empty($shipmentRow['waybill_number'])
                || !empty($shipmentRow['external_id'] ?? null)
            );

            if ($hasSubmitted) {
                $alsendo_shipping = [
                    'status' => $shipmentRow['status'] ?? '',
                    'waybill_number' => $shipmentRow['waybill_number'] ?? '',
                    'tracking_url' => $shipmentRow['tracking_url'] ?? '',
                    'shipping_method' => $shipmentRow['shipping_method'] ?? '',
                    'courier_service' => $shipmentRow['courier_service'] ?? '',
                    'price' => $shipmentRow['price'] ?? '',
                    'pickup_point' => $shipmentRow['pickup_point'] ?? '',
                ];
                $jsonData = json_decode($shipmentRow['data'] ?? '{}', true);
                if (is_array($jsonData)) {
                    if (!empty($jsonData['carrier_tracking_number'])) {
                        $alsendo_shipping['carrier_tracking_number'] = $jsonData['carrier_tracking_number'];
                    }
                }
            }

            if (!empty($shipmentRow['data'])) {
                $data = json_decode($shipmentRow['data'], true);
                if (is_array($data)) {
                    $alsendo_sender = $data['sender'] ?? [];
                    $alsendo_shipping_address = $data['recipient'] ?? [];
                    $alsendo_package = $data['package'] ?? [];
                }
            }
        }

        $estimatedPrice = null;
        $estimatedServiceId = null;

        if (empty($alsendo_sender) || empty($alsendo_package) || empty($alsendo_shipping_address)) {
            $details = \Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'alsendo_order_details`
                 WHERE id_order=' . (int) $idOrder
            );
            if ($details) {
                $detailsData = json_decode($details['data'], true);
                if (is_array($detailsData)) {
                    if (empty($alsendo_sender) && isset($detailsData['sender'])) {
                        $alsendo_sender = $detailsData['sender'];
                    }
                    if (empty($alsendo_package) && isset($detailsData['package'])) {
                        $alsendo_package = $detailsData['package'];
                    }
                    if (empty($alsendo_shipping_address) && isset($detailsData['recipient'])) {
                        $alsendo_shipping_address = $detailsData['recipient'];
                    }
                    if (isset($detailsData['shipment']['estimated_price'])) {
                        $estimatedPrice = $detailsData['shipment']['estimated_price'];
                    }
                    if (isset($detailsData['shipment']['estimated_service_id'])) {
                        $estimatedServiceId = $detailsData['shipment']['estimated_service_id'];
                    }
                }
            }
        } else {
            $details = \Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'alsendo_order_details`
                 WHERE id_order=' . (int) $idOrder
            );
            if ($details) {
                $detailsData = json_decode($details['data'], true);
                if (is_array($detailsData)) {
                    if (isset($detailsData['shipment']['estimated_price'])) {
                        $estimatedPrice = $detailsData['shipment']['estimated_price'];
                    }
                    if (isset($detailsData['shipment']['estimated_service_id'])) {
                        $estimatedServiceId = $detailsData['shipment']['estimated_service_id'];
                    }
                }
            }
        }

        if (empty($alsendo_shipping_address) || empty($alsendo_shipping['shipping_method'])) {
            try {
                $order = new \Order($idOrder);
                if (\Validate::isLoadedObject($order)) {
                    $address = new \Address($order->id_address_delivery);
                    $customer = new \Customer($order->id_customer);
                    $carrier = new \Carrier($order->id_carrier);

                    if (empty($alsendo_shipping_address) && \Validate::isLoadedObject($address)) {
                        $parsed = AddressParser::parseAddressToComponents($address->address1, $address->address2);
                        $composedAddr = trim($parsed['street']
                            . (!empty($parsed['building_number']) ? ' ' . $parsed['building_number'] : '')
                            . (!empty($parsed['apartment_number']) ? '/' . $parsed['apartment_number'] : ''));
                        $alsendo_shipping_address = [
                            'first_name' => $address->firstname,
                            'last_name' => $address->lastname,
                            'company' => $address->company,
                            'address' => $composedAddr,
                            'address2' => '',
                            'town' => $address->city,
                            'postal_code' => $address->postcode,
                            'country' => \Country::getNameById($this->context->language->id, $address->id_country),
                        ];
                    }

                    if (empty($alsendo_shipping['shipping_method']) && \Validate::isLoadedObject($carrier)) {
                        if (empty($alsendo_shipping)) {
                            $alsendo_shipping = [];
                        }
                        $alsendo_shipping['shipping_method'] = $carrier->name;
                        $alsendo_shipping['courier_service'] = $carrier->name;
                    }
                }
            } catch (\Exception $e) {
                error_log('[Alsendo] Failed to load fallback data: ' . $e->getMessage());
            }
        }

        $pickupPointData = null;
        $pickupRow = \Db::getInstance()->getRow(
            'SELECT pickup_point, pickup_point_display FROM `' . _DB_PREFIX_ . 'alsendo_order_pickup`
             WHERE id_order=' . (int) $idOrder
        );

        if (!$pickupRow || empty($pickupRow['pickup_point'])) {
            try {
                $order = new \Order($idOrder);
                if (\Validate::isLoadedObject($order) && (int) $order->id_cart > 0) {
                    $pickupRow = \Db::getInstance()->getRow(
                        'SELECT pickup_point, pickup_point_display FROM `' . _DB_PREFIX_ . 'alsendo_order_pickup`
                         WHERE id_cart=' . (int) $order->id_cart
                    );
                }
            } catch (\Exception $e) {
            }
        }

        if ($pickupRow && !empty($pickupRow['pickup_point'])) {
            $pickupPointData = json_decode($pickupRow['pickup_point'], true);
            if (!is_array($pickupPointData)) {
                $pickupPointData = [
                    'code' => $pickupRow['pickup_point_display'] ?? '',
                    'display' => $pickupRow['pickup_point_display'] ?? '',
                ];
            }
        }

        $alsendo_sender = array_merge([
            'template_name' => '',
            'first_name' => '',
            'last_name' => '',
            'company_name' => '',
            'street' => '',
            'building_number' => '',
            'apartment_number' => '',
            'postal_code' => '',
            'city' => '',
            'country' => '',
            'phone_number' => '',
            'email' => '',
            'bank_account_number' => '',
        ], $alsendo_sender ?: []);

        $alsendo_package = array_merge([
            'template_name' => '',
            'width' => '',
            'length' => '',
            'height' => '',
            'weight' => '',
            'package_type' => '',
            'shipment_content' => '',
            'cash_on_delivery' => '',
            'declared_value' => '',
            'pickup_type' => '',
        ], $alsendo_package ?: []);

        $alsendo_shipping_address = array_merge([
            'first_name' => '',
            'last_name' => '',
            'company' => '',
            'address' => '',
            'address2' => '',
            'town' => '',
            'postal_code' => '',
            'country' => '',
        ], $alsendo_shipping_address ?: []);

        if (!empty($alsendo_shipping_address['street']) && empty($alsendo_shipping_address['address'])) {
            $alsendo_shipping_address['address'] = trim($alsendo_shipping_address['street']
                . (!empty($alsendo_shipping_address['building_number']) ? ' ' . $alsendo_shipping_address['building_number'] : '')
                . (!empty($alsendo_shipping_address['apartment_number']) ? '/' . $alsendo_shipping_address['apartment_number'] : ''));
            $alsendo_shipping_address['address2'] = '';
        }

        if (isset($alsendo_shipping_address['city']) && !isset($alsendo_shipping_address['town'])) {
            $alsendo_shipping_address['town'] = $alsendo_shipping_address['city'];
        }

        $region = \Configuration::get('ALSENDO_REGION') ?: 'pl';
        $currencyMap = ['pl' => 'PLN', 'cz' => 'CZK', 'ro' => 'RON'];
        $alsendo_currency = $currencyMap[$region] ?? 'PLN';

        $pickupTypeLabels = [
            'SELF' => $this->module->l('Deliver to point', 'AdminAlsendoOrderController'),
            'COURIER' => $this->module->l('Courier pickup', 'AdminAlsendoOrderController'),
            'NO_PICKUP' => $this->module->l('No pickup', 'AdminAlsendoOrderController'),
            'OCCASIONAL' => $this->module->l('Label provided by courier', 'AdminAlsendoOrderController'),
            'ONDEMAND' => $this->module->l('Self-printed label (drop-off)', 'AdminAlsendoOrderController'),
        ];

        $displayPrice = '';
        if (!empty($alsendo_shipping['price'])) {
            $p = (float) $alsendo_shipping['price'];
            $displayPrice = number_format($p, 2, '.', '');
        }

        $displayEstimatedPrice = '';
        if ($estimatedPrice !== null && $estimatedPrice !== '') {
            $ep = (float) $estimatedPrice;
            $displayEstimatedPrice = number_format($ep, 2, '.', '');
        }

        if (!isset($order) || !\Validate::isLoadedObject($order)) {
            $order = new \Order($idOrder);
        }
        $orderTotal = (float) $order->total_paid_tax_incl;

        $this->context->smarty->assign([
            'order_id' => $idOrder,
            'order_total' => $orderTotal,
            'alsendo_sender' => $alsendo_sender,
            'alsendo_shipping_address' => $alsendo_shipping_address,
            'alsendo_package' => $alsendo_package,
            'alsendo_shipping' => $alsendo_shipping,
            'alsendo_pickup_point' => $pickupPointData,
            'alsendo_estimated_price' => $estimatedPrice,
            'alsendo_estimated_service_id' => $estimatedServiceId,
            'alsendo_price_display' => $displayPrice,
            'alsendo_estimated_price_display' => $displayEstimatedPrice,
            'alsendo_currency' => $alsendo_currency,
            'pickup_type_labels' => $pickupTypeLabels,
            'user_token' => \Tools::getAdminTokenLite('AdminAlsendoOrder'),
        ]);

        return $this->module->display(
            $this->module->getLocalPath(),
            'views/templates/admin/order_alsendo_shipping.tpl'
        );
    }

    public function hookDisplayAdminOrderMain($params)
    {
        return '';
    }
}
