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

require_once _PS_MODULE_DIR_ . 'alsendo/src/Models/BulkSendBatch.php';
require_once _PS_MODULE_DIR_ . 'alsendo/src/Models/BulkSendItem.php';
require_once _PS_MODULE_DIR_ . 'alsendo/src/DTO/FullOrderDTO.php';
require_once _PS_MODULE_DIR_ . 'alsendo/src/DTO/OrderShipmentSubmitDTO.php';
require_once _PS_MODULE_DIR_ . 'alsendo/src/Services/WrapperService.php';
require_once _PS_MODULE_DIR_ . 'alsendo/src/Services/OrderDetailsService.php';
require_once _PS_MODULE_DIR_ . 'alsendo/src/Services/PickupHoursService.php';
require_once _PS_MODULE_DIR_ . 'alsendo/src/Services/AddressParser.php';
require_once _PS_MODULE_DIR_ . 'alsendo/src/Services/MapBridge.php';

use Alsendo\DTO\FullOrderDTO;
use Alsendo\DTO\OrderShipmentSubmitDTO;
use Alsendo\Models\BulkSendBatch;
use Db;
use Exception;
use Order;

class BulkSendService
{
    private $db;
    private WrapperService $wrapperService;
    private $context;

    public const SEND_INTERVAL = 1;

    public function __construct($db, $context)
    {
        $this->db = $db;
        $this->context = $context;
        $this->wrapperService = new WrapperService();
    }

    public function createBatch(array $orderIds, int $idEmployee): BulkSendBatch
    {
        $batchReference = 'BATCH_' . date('YmdHis') . '_' . uniqid();

        $this->db->insert('alsendo_bulk_send_batch', [
            'batch_reference' => pSQL($batchReference),
            'id_employee' => (int) $idEmployee,
            'id_shop' => (int) $this->context->shop->id,
            'total_orders' => count($orderIds),
            'status' => 'pending',
            'created_at' => ['type' => 'sql', 'value' => 'NOW()'],
        ]);

        $batchId = $this->db->Insert_ID();

        foreach ($orderIds as $orderId) {
            $this->db->insert('alsendo_bulk_send_item', [
                'id_batch' => (int) $batchId,
                'id_order' => (int) $orderId,
                'status' => 'pending',
            ]);
        }

        return new BulkSendBatch($batchId, $batchReference);
    }

    public function sendBatch($batchId, array $templateOverrides = [])
    {
        $batch = \Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'alsendo_bulk_send_batch`
             WHERE id_batch = ' . (int) $batchId
        );

        if (!$batch) {
            return ['error' => 'Batch not found', 'successful' => 0, 'failed' => 0];
        }

        if ($batch['status'] === 'cancelled' || $batch['status'] === 'completed') {
            \Db::getInstance()->update(
                'alsendo_bulk_send_batch',
                [
                    'status' => 'processing',
                    'processing_started_at' => ['type' => 'sql', 'value' => 'NOW()'],
                    'completed_at' => null,
                ],
                'id_batch = ' . (int) $batchId
            );

            \Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'alsendo_bulk_send_item`
                 SET status = "pending",
                     error_message = NULL,
                     error_type = NULL,
                     waybill_number = NULL,
                     alsendo_order_id = NULL,
                     tracking_url = NULL,
                     shipment_data = NULL,
                     completed_at = NULL,
                     attempted_at = NULL
                 WHERE id_batch = ' . (int) $batchId . ' AND status NOT IN ("success", "processing")'
            );
        }

        $senderTemplates = json_decode(\Configuration::get('ALSENDO_SENDER_LIST', null, null, null, '[]'), true) ?: [];
        $defaultSender = null;
        foreach ($senderTemplates as $tpl) {
            if (!empty($tpl['main'])) {
                $defaultSender = $tpl;
                break;
            }
        }
        if (!$defaultSender && !empty($senderTemplates)) {
            $defaultSender = $senderTemplates[0];
        }

        if (!$defaultSender) {
            return ['error' => 'No default sender configured. Please configure sender in module settings.', 'successful' => 0, 'failed' => 0];
        }

        $packageTemplates = json_decode(\Configuration::get('ALSENDO_SHIPPING_SETTINGS_LIST', null, null, null, '[]'), true) ?: [];
        $defaultPackage = !empty($packageTemplates) ? $packageTemplates[0] : null;
        if (!$defaultPackage) {
            return ['error' => 'No default package configured. Please configure package template in module settings.', 'successful' => 0, 'failed' => 0];
        }

        $items = \Db::getInstance()->executeS(
            'SELECT id_order FROM `' . _DB_PREFIX_ . 'alsendo_bulk_send_item`
             WHERE id_batch = ' . (int) $batchId . ' AND status NOT IN ("success", "processing")'
        );

        $successful = 0;
        $failed = 0;

        foreach ($items as $item) {
            $orderId = (int) $item['id_order'];

            try {
                $existingShipment = \Db::getInstance()->getRow(
                    'SELECT status, waybill_number FROM `' . _DB_PREFIX_ . 'alsendo_order_shipment`
                     WHERE id_order = ' . (int) $orderId
                );

                if ($existingShipment && ($existingShipment['status'] === 'submitted' || $existingShipment['status'] === 'success' || $existingShipment['status'] === 'NEW')) {
                    \Db::getInstance()->update(
                        'alsendo_bulk_send_item',
                        [
                            'status' => 'failed',
                            'error_type' => 'other',
                            'error_message' => 'Already sent (Waybill: ' . pSQL($existingShipment['waybill_number']) . ')',
                            'completed_at' => ['type' => 'sql', 'value' => 'NOW()'],
                        ],
                        'id_order = ' . (int) $orderId . ' AND id_batch = ' . (int) $batchId
                    );
                    ++$failed;
                    continue;
                }

                $order = new \Order($orderId);

                if (!\Validate::isLoadedObject($order)) {
                    throw new \Exception('Order not found');
                }

                $carrier = new \Carrier($order->id_carrier);
                $carrierName = \Validate::isLoadedObject($carrier) ? $carrier->name : 'Unknown';

                $carrierRow = \Db::getInstance()->getRow(
                    'SELECT id_service, has_map, ship_via_pickup_point, pickup_request FROM `' . _DB_PREFIX_ . 'alsendo_shipping_methods`
                     WHERE id_carrier = ' . (int) $order->id_carrier
                );

                if (!$carrierRow) {
                    throw new \Exception("Carrier \"{$carrierName}\" (ID: {$order->id_carrier}) is not mapped to any Alsendo service. Please configure the shipping method mapping in Alsendo settings.");
                }

                $mappedServiceId = !empty($carrierRow['id_service'])
                    ? (is_numeric($carrierRow['id_service']) ? (int) $carrierRow['id_service'] : $carrierRow['id_service'])
                    : null;
                $hasMap = (bool) $carrierRow['has_map'];
                $smShipVia = (int) ($carrierRow['ship_via_pickup_point'] ?? 0);
                $smPickupRequest = (int) ($carrierRow['pickup_request'] ?? 0);

                $address = new \Address($order->id_address_delivery);
                $customer = new \Customer($order->id_customer);

                $savedDetailsRow = \Db::getInstance()->getRow(
                    'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_details`
                     WHERE id_order = ' . (int) $orderId
                );
                $savedData = $savedDetailsRow ? json_decode($savedDetailsRow['data'], true) : [];
                $hasSavedSender = !empty($savedData['sender']['full_name']) || !empty($savedData['sender']['company_name']);
                $hasSavedPackage = !empty($savedData['package']['width']) || !empty($savedData['package']['weight']);

                $region = \Configuration::get('ALSENDO_REGION') ?: 'pl';
                $regionCountryMap = ['pl' => 'PL', 'cz' => 'CZ', 'ro' => 'RO'];
                $regionCountry = $regionCountryMap[$region] ?? 'PL';

                if ($hasSavedSender) {
                    $s = $savedData['sender'];
                    $senderPostData = [
                        'sender_address_type' => $s['address_type'] ?? 'company',
                        'sender_company_name' => $s['company_name'] ?? '',
                        'sender_full_name' => $s['full_name'] ?? '',
                        'sender_street' => $s['street'] ?? '',
                        'sender_building_number' => $s['building_number'] ?? '',
                        'sender_apartment_number' => $s['apartment_number'] ?? '',
                        'sender_block' => $s['block'] ?? '',
                        'sender_entrance' => $s['entrance'] ?? '',
                        'sender_floor' => $s['floor'] ?? '',
                        'sender_flat' => $s['flat'] ?? '',
                        'sender_postal_code' => $s['postal_code'] ?? '',
                        'sender_city' => $s['city'] ?? '',
                        'sender_country' => $s['country'] ?? $regionCountry,
                        'sender_phone_number' => $s['phone_number'] ?? '',
                        'sender_email' => $s['email'] ?? '',
                        'sender_contact_person' => $s['contact_person'] ?? '',
                        'sender_bank_account_number' => $s['bank_account_number'] ?? '',
                        'sender_bank_code' => $s['bank_code'] ?? '',
                        'sender_additional_bank_account_number' => $s['additional_bank_account_number'] ?? '',
                        'sender_external_id' => $s['external_id'] ?? '',
                    ];
                } else {
                    $senderPostData = [
                        'sender_address_type' => $defaultSender['address_type'] ?? 'company',
                        'sender_company_name' => $defaultSender['company'] ?? '',
                        'sender_full_name' => trim(($defaultSender['firstname'] ?? '') . ' ' . ($defaultSender['lastname'] ?? '')),
                        'sender_street' => $defaultSender['street'] ?? '',
                        'sender_building_number' => $defaultSender['building'] ?? '',
                        'sender_apartment_number' => $defaultSender['apartment'] ?? '',
                        'sender_block' => $defaultSender['block'] ?? '',
                        'sender_entrance' => $defaultSender['entrance'] ?? '',
                        'sender_floor' => $defaultSender['floor'] ?? '',
                        'sender_flat' => $defaultSender['flat'] ?? '',
                        'sender_postal_code' => $defaultSender['postal'] ?? '',
                        'sender_city' => $defaultSender['city'] ?? '',
                        'sender_country' => $regionCountry,
                        'sender_phone_number' => $defaultSender['phone'] ?? '',
                        'sender_email' => $defaultSender['email'] ?? '',
                        'sender_contact_person' => $defaultSender['contact'] ?? '',
                        'sender_bank_account_number' => $defaultSender['bank'] ?? '',
                        'sender_bank_code' => $defaultSender['bank_code'] ?? '',
                        'sender_additional_bank_account_number' => $defaultSender['additional_bank_account_number'] ?? '',
                        'sender_external_id' => $defaultSender['external_id'] ?? '',
                    ];
                }

                $isCodOrder = in_array($order->module, ['ps_cashondelivery', 'cashondelivery'], true);
                $orderTotal = (float) $order->total_paid_tax_incl;

                $autoDeclaredValue = (bool) \Configuration::get('ALSENDO_AUTO_DECLARED_VALUE');
                $bsDefaultPkgType = ($region === 'cz') ? 'PACKAGE' : (($region === 'ro') ? 'package' : 'PACZKA');

                $effectiveTemplate = $defaultPackage;
                if (isset($templateOverrides[$orderId]) && isset($packageTemplates[$templateOverrides[$orderId]])) {
                    $effectiveTemplate = $packageTemplates[$templateOverrides[$orderId]];
                }

                $savedCod = (float) ($savedData['package']['cod_value'] ?? 0);
                $savedDeclVal = (float) ($savedData['package']['declared_value'] ?? 0);
                $templateDeclVal = (float) ($effectiveTemplate['alsendo_declared_value'] ?? 0);

                $packagePostData = [
                    'package_shipment_type' => $effectiveTemplate['alsendo_package_type'] ?? $bsDefaultPkgType,
                    'package_is_nstd' => (int) ($effectiveTemplate['alsendo_is_nstd'] ?? 0),
                    'package_width' => (float) ($effectiveTemplate['alsendo_width'] ?? 0),
                    'package_length' => (float) ($effectiveTemplate['alsendo_length'] ?? 0),
                    'package_height' => (float) ($effectiveTemplate['alsendo_height'] ?? 0),
                    'package_weight' => (float) ($effectiveTemplate['alsendo_weight'] ?? 0),
                    'package_content' => $effectiveTemplate['alsendo_shipment_content'] ?? 'Order #' . $orderId,
                    'package_cod' => ($savedCod > 0)
                        ? $savedCod
                        : (((float) ($effectiveTemplate['alsendo_cod'] ?? 0) > 0)
                            ? (float) $effectiveTemplate['alsendo_cod']
                            : ($isCodOrder ? $orderTotal : 0)),
                    'package_declared_value' => ($savedDeclVal > 0)
                        ? $savedDeclVal
                        : (($templateDeclVal > 0)
                            ? (($autoDeclaredValue && $orderTotal > $templateDeclVal) ? $orderTotal : $templateDeclVal)
                            : ($autoDeclaredValue ? $orderTotal : 0)),
                ];

                $defaultPickupType = OrderDetailsService::getConfiguredPickupType($region);
                $pickupType = !empty($effectiveTemplate['alsendo_pickup_type'])
                    ? strtoupper($effectiveTemplate['alsendo_pickup_type'])
                    : $defaultPickupType;

                $sh = $savedData['shipment'] ?? [];
                $pickupDate = '';
                if (!empty($sh['preferred_pickup_date'])) {
                    $pickupDate = $sh['preferred_pickup_date'];
                } elseif ($pickupType === 'COURIER') {
                    $pickupDate = PickupHoursService::getDefaultPickupDate();
                }

                $defaultHours = PickupHoursService::getDefaultPickupHours();
                $hoursFrom = !empty($sh['preferred_pickup_hours_from']) ? $sh['preferred_pickup_hours_from'] : $defaultHours['from'];
                $hoursTo = !empty($sh['preferred_pickup_hours_to']) ? $sh['preferred_pickup_hours_to'] : $defaultHours['to'];

                $recipientPhone = $address->phone_mobile ?: $address->phone;
                $recipientEmail = $customer->email;
                if (!empty($savedData['recipient']['phone'])) {
                    $recipientPhone = $savedData['recipient']['phone'];
                }
                if (!empty($savedData['recipient']['email'])) {
                    $recipientEmail = $savedData['recipient']['email'];
                }

                $postData = array_merge($senderPostData, $packagePostData, [
                    'order_id' => $orderId,
                    'selected_pickup_type' => $pickupType,
                    'shipment_preferred_pickup_date' => $pickupDate,
                    'shipment_preferred_pickup_hours_from' => $hoursFrom,
                    'shipment_preferred_pickup_hours_to' => $hoursTo,
                    'shipment_selected_service' => null,
                    'shipping_phone_number' => $recipientPhone,
                    'shipping_email' => $recipientEmail,
                    'shipping_via_pickup_point' => $smShipVia,
                    'pickup_request' => $smPickupRequest,
                ]);

                $finalServiceId = null;

                $pickupPoint = null;

                if (!empty($savedData['shipment']['shipment_pickup_point'])) {
                    $pickupPoint = $savedData['shipment']['shipment_pickup_point'];
                    $postData['shipment_pickup_point'] = json_encode($pickupPoint);
                }

                if (!$pickupPoint && $hasMap && (int) $order->id_cart > 0) {
                    $pickupPointData = \Db::getInstance()->getValue(
                        'SELECT pickup_point FROM ' . _DB_PREFIX_ . 'alsendo_order_pickup WHERE id_cart=' . (int) $order->id_cart
                    );
                    if ($pickupPointData) {
                        $pickupPoint = json_decode($pickupPointData, true);
                        $postData['shipment_pickup_point'] = json_encode($pickupPoint);
                    }
                }

                $mapBridge = new MapBridge();
                $bulkCarrier = new \Carrier($order->id_carrier);
                $bulkCarrierName = strtolower($bulkCarrier->name);

                if ($hasMap && !$pickupPoint) {
                    $pickupKey = null;
                    foreach ($mapBridge->getDefaultPickupOperators($region) as $opKey) {
                        if (strpos($bulkCarrierName, $opKey) !== false) {
                            $pickupKey = $mapBridge->getPickupConfigKey($opKey);
                            break;
                        }
                    }

                    if ($pickupKey) {
                        $defaultPickup = \Configuration::get($pickupKey, null, null, null, '');
                        if ($defaultPickup) {
                            $postData['shipment_pickup_point'] = $defaultPickup;
                        }
                    }
                }

                $merchantPickupKey = null;
                foreach ($mapBridge->getDefaultPickupOperators($region) as $opKey) {
                    if (strpos($bulkCarrierName, $opKey) !== false) {
                        $merchantPickupKey = $mapBridge->getPickupConfigKey($opKey);
                        break;
                    }
                }

                if ($merchantPickupKey) {
                    $merchantPickupConfig = \Configuration::get($merchantPickupKey, null, null, null, '');
                    if ($merchantPickupConfig) {
                        $postData['merchant_pickup_point'] = $merchantPickupConfig;
                    }
                }

                $dto = OrderShipmentSubmitDTO::fromRequest($postData);
                $recipientOverride = $savedData['recipient'] ?? null;
                $fullDto = FullOrderDTO::fromPrestaOrder($order, $dto, $pickupPoint, $recipientOverride);

                $wrapperService = new WrapperService();

                $quoteResult = $wrapperService->getOrderValuation($fullDto);
                if ($quoteResult->isSuccess()) {
                    $valData = json_decode(json_encode($quoteResult->getData()), true);
                    $region = \Configuration::get('ALSENDO_REGION') ?: 'pl';
                    $cheapest = WrapperService::selectCheapestService($valData, $region, $hasMap, $mappedServiceId);
                    if ($cheapest) {
                        $finalServiceId = $cheapest['service_id'];
                        $estimatedPrice = $cheapest['price_gross'] ?? 0;
                    } else {
                        throw new \Exception("Order #{$orderId}: No services with price. Cannot auto-select cheapest.");
                    }
                } else {
                    throw new \Exception("Order #{$orderId}: Failed to get quote: " . ($quoteResult->getError() ?: 'API error'));
                }

                $postData['shipment_selected_service'] = $finalServiceId;
                $dto = OrderShipmentSubmitDTO::fromRequest($postData);
                $fullDto = FullOrderDTO::fromPrestaOrder($order, $dto, $pickupPoint, $recipientOverride);

                $result = $wrapperService->sendOrder($fullDto, $finalServiceId);

                if ($result->isSuccess()) {
                    $apiData = $result->getData();
                    $waybill = pSQL($apiData['waybill_number'] ?? '');
                    if (empty($waybill) && !empty($apiData['id'])) {
                        $waybill = pSQL((string) $apiData['id']);
                    }
                    $trackingUrl = pSQL($apiData['tracking_url'] ?? '');
                    $serviceName = pSQL($apiData['service_name'] ?? '');
                    $serviceId = pSQL($apiData['service_id'] ?? '');
                    $status = pSQL($apiData['status'] ?? 'submitted');
                    $price = isset($apiData['price']) ? (float) $apiData['price'] : 0;
                    if ($price == 0 && $estimatedPrice > 0) {
                        $price = (float) $estimatedPrice;
                    }

                    $previousState = (int) $order->current_state;

                    \Db::getInstance()->execute(
                        'REPLACE INTO `' . _DB_PREFIX_ . 'alsendo_order_shipment`
                        (id_order, status, waybill_number, shipping_method, courier_service, price, tracking_url, previous_order_state, data)
                        VALUES (
                            ' . (int) $orderId . ',
                            "' . $status . '",
                            "' . $waybill . '",
                            "' . $serviceName . '",
                            "' . $serviceId . '",
                            "' . $price . '",
                            "' . $trackingUrl . '",
                            ' . (int) $previousState . ',
                            "' . pSQL(json_encode($apiData)) . '"
                        )'
                    );

                    $this->updatePrestaShopOrderStatus($orderId, $status);

                    OrderDetailsService::syncTrackingNumber($orderId, $waybill);

                    \Db::getInstance()->update(
                        'alsendo_bulk_send_item',
                        [
                            'status' => 'success',
                            'waybill_number' => $waybill,
                            'tracking_url' => $trackingUrl,
                            'shipment_data' => pSQL(json_encode($apiData)),
                            'completed_at' => ['type' => 'sql', 'value' => 'NOW()'],
                        ],
                        'id_order = ' . (int) $orderId . ' AND id_batch = ' . (int) $batchId
                    );

                    $detailRow = \Db::getInstance()->getRow(
                        'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_details` WHERE id_order=' . (int) $orderId
                    );
                    $detailData = $detailRow ? json_decode($detailRow['data'], true) : [];
                    if (!is_array($detailData)) {
                        $detailData = [];
                    }
                    if (!isset($detailData['package'])) {
                        $detailData['package'] = [];
                    }

                    $detailData['package']['template_name'] = $effectiveTemplate['alsendo_template_name'] ?? '';
                    $detailData['package']['width'] = (float) ($effectiveTemplate['alsendo_width'] ?? 0);
                    $detailData['package']['length'] = (float) ($effectiveTemplate['alsendo_length'] ?? 0);
                    $detailData['package']['height'] = (float) ($effectiveTemplate['alsendo_height'] ?? 0);
                    $detailData['package']['weight'] = (float) ($effectiveTemplate['alsendo_weight'] ?? 0);
                    $detailData['package']['shipment_packaging'] = $effectiveTemplate['alsendo_package_type'] ?? $bsDefaultPkgType;
                    $detailData['package']['package_type'] = $effectiveTemplate['alsendo_package_type'] ?? $bsDefaultPkgType;
                    $detailData['package']['shipment_type'] = $effectiveTemplate['alsendo_package_type'] ?? $bsDefaultPkgType;
                    $detailData['package']['is_nstd'] = (int) ($effectiveTemplate['alsendo_is_nstd'] ?? 0);
                    $detailData['package']['pickup_type'] = $pickupType;
                    $detailData['package']['package_content'] = $effectiveTemplate['alsendo_shipment_content'] ?? 'Order #' . $orderId;

                    if ($detailRow) {
                        \Db::getInstance()->update(
                            'alsendo_order_details',
                            ['data' => pSQL(json_encode($detailData)), 'updated_at' => ['type' => 'sql', 'value' => 'NOW()']],
                            'id_order=' . (int) $orderId
                        );
                    } else {
                        \Db::getInstance()->insert(
                            'alsendo_order_details',
                            [
                                'id_order' => (int) $orderId,
                                'data' => pSQL(json_encode($detailData)),
                                'created_at' => ['type' => 'sql', 'value' => 'NOW()'],
                                'updated_at' => ['type' => 'sql', 'value' => 'NOW()'],
                            ]
                        );
                    }

                    ++$successful;
                } else {
                    throw new \Exception($result->getError());
                }
            } catch (\Exception $e) {
                \Db::getInstance()->update(
                    'alsendo_bulk_send_item',
                    [
                        'status' => 'failed',
                        'error_type' => 'validation',
                        'error_message' => pSQL($e->getMessage()),
                        'completed_at' => ['type' => 'sql', 'value' => 'NOW()'],
                    ],
                    'id_order = ' . (int) $orderId . ' AND id_batch = ' . (int) $batchId
                );
                ++$failed;
            }
        }

        $newStatus = ($failed === 0) ? 'completed' : (($successful > 0) ? 'partial_error' : 'error');

        \Db::getInstance()->update(
            'alsendo_bulk_send_batch',
            [
                'status' => $newStatus,
                'successful_orders' => (int) $successful,
                'failed_orders' => (int) $failed,
                'completed_at' => ['type' => 'sql', 'value' => 'NOW()'],
            ],
            'id_batch = ' . (int) $batchId
        );

        return [
            'successful' => $successful,
            'failed' => $failed,
            'batch_id' => $batchId,
            'status' => $newStatus,
        ];
    }

    private function sendSingleOrder(int $orderId, int $idBatch): array
    {
        $order = new \Order($orderId);

        $this->updateBatchItem($orderId, $idBatch, [
            'status' => 'processing',
            'attempted_at' => ['type' => 'sql', 'value' => 'NOW()'],
        ]);

        try {
            $validation = $this->validateOrderForShipping($order);
            if (!$validation['success']) {
                $this->updateBatchItem($orderId, $idBatch, [
                    'status' => 'failed',
                    'error_type' => 'validation',
                    'error_message' => $validation['message'],
                    'completed_at' => ['type' => 'sql', 'value' => 'NOW()'],
                ]);

                return [
                    'success' => false,
                    'error_type' => 'validation',
                    'error_message' => $validation['message'],
                ];
            }

            $existing = $this->db->getRow(
                'SELECT id_order FROM `' . _DB_PREFIX_ . 'alsendo_order_shipment`
                 WHERE id_order = ' . (int) $orderId . '
                 AND status != "cancelled"'
            );

            if ($existing) {
                $this->updateBatchItem($orderId, $idBatch, [
                    'status' => 'failed',
                    'error_type' => 'duplicate',
                    'error_message' => 'Order already sent to Alsendo',
                    'completed_at' => ['type' => 'sql', 'value' => 'NOW()'],
                ]);

                return [
                    'success' => false,
                    'error_type' => 'duplicate',
                    'error_message' => 'Order already sent',
                ];
            }

            $orderDetailsRow = $this->db->getRow(
                'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_details`
                 WHERE id_order = ' . (int) $orderId
            );

            $orderData = $orderDetailsRow
                ? json_decode($orderDetailsRow['data'], true)
                : [];

            if (empty($orderData)) {
                throw new \Exception('Order details not found');
            }

            $flatData = $this->flattenOrderData($orderData);
            $flatData['order_id'] = $orderId;
            $dto = OrderShipmentSubmitDTO::fromRequest($flatData);

            $pickupPoint = null;
            if ((int) $order->id_cart > 0) {
                $pickupData = $this->db->getValue(
                    'SELECT pickup_point FROM `' . _DB_PREFIX_ . 'alsendo_order_pickup`
                     WHERE id_cart = ' . (int) $order->id_cart
                );
                $pickupPoint = $pickupData ? json_decode($pickupData, true) : null;
            }

            $recipientOverride = $orderData['recipient'] ?? null;
            $fullDto = FullOrderDTO::fromPrestaOrder($order, $dto, $pickupPoint, $recipientOverride);

            $selectedServiceId = !empty($flatData['shipment_selected_service'])
                ? (is_numeric($flatData['shipment_selected_service']) ? (int) $flatData['shipment_selected_service'] : $flatData['shipment_selected_service'])
                : null;
            $result = $this->wrapperService->sendOrder($fullDto, $selectedServiceId);

            if (!$result->isSuccess()) {
                $this->updateBatchItem($orderId, $idBatch, [
                    'status' => 'failed',
                    'error_type' => 'api',
                    'error_message' => $result->getError(),
                    'completed_at' => ['type' => 'sql', 'value' => 'NOW()'],
                ]);

                return [
                    'success' => false,
                    'error_type' => 'api',
                    'error_message' => $result->getError(),
                ];
            }

            $apiData = $result->getData();
            $waybill = $apiData['waybill_number'] ?? '';
            if (empty($waybill) && !empty($apiData['id'])) {
                $waybill = (string) $apiData['id'];
            }
            $trackingUrl = $apiData['tracking_url'] ?? '';
            $serviceName = $apiData['service_name'] ?? '';

            $this->db->insert('alsendo_order_shipment', [
                'id_order' => (int) $orderId,
                'status' => 'submitted',
                'waybill_number' => pSQL($waybill),
                'tracking_url' => pSQL($trackingUrl),
                'courier_service' => pSQL($serviceName),
                'data' => pSQL(json_encode($apiData)),
            ], true);

            $this->updateBatchItem($orderId, $idBatch, [
                'status' => 'success',
                'alsendo_order_id' => pSQL($apiData['id'] ?? ''),
                'waybill_number' => pSQL($waybill),
                'tracking_url' => pSQL($trackingUrl),
                'shipment_data' => pSQL(json_encode($apiData)),
                'completed_at' => ['type' => 'sql', 'value' => 'NOW()'],
            ]);

            return [
                'success' => true,
                'waybill_number' => $waybill,
                'tracking_url' => $trackingUrl,
            ];
        } catch (\Exception $e) {
            $errorType = $this->categorizeException($e);
            $this->updateBatchItem($orderId, $idBatch, [
                'status' => 'failed',
                'error_type' => $errorType,
                'error_message' => $e->getMessage(),
                'completed_at' => ['type' => 'sql', 'value' => 'NOW()'],
            ]);

            return [
                'success' => false,
                'error_type' => $errorType,
                'error_message' => $e->getMessage(),
            ];
        }
    }

    public function retryFailedOrders(int $idBatch, array $templateOverrides = []): array
    {
        $failedItems = $this->db->executeS(
            'SELECT id_order FROM `' . _DB_PREFIX_ . 'alsendo_bulk_send_item`
             WHERE id_batch = ' . (int) $idBatch . '
             AND status = "failed"'
        );

        if (empty($failedItems)) {
            return ['message' => 'No failed orders to retry'];
        }

        $this->db->execute(
            'UPDATE `' . _DB_PREFIX_ . 'alsendo_bulk_send_item`
             SET status = "pending", retry_count = retry_count + 1
             WHERE id_batch = ' . (int) $idBatch . '
             AND status = "failed"'
        );

        return $this->sendBatch($idBatch, $templateOverrides);
    }

    public function retrySingleOrder(int $idBatch, int $orderId): array
    {
        $item = $this->db->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'alsendo_bulk_send_item`
             WHERE id_batch = ' . (int) $idBatch . '
             AND id_order = ' . (int) $orderId
        );

        if (!$item) {
            return ['success' => false, 'message' => 'Order not found in batch'];
        }

        $this->db->execute(
            'UPDATE `' . _DB_PREFIX_ . 'alsendo_bulk_send_item`
             SET status = "pending", retry_count = retry_count + 1
             WHERE id_batch = ' . (int) $idBatch . '
             AND id_order = ' . (int) $orderId
        );

        $result = $this->sendSingleOrder($orderId, $idBatch);

        if ($result['success'] ?? false) {
            return [
                'success' => true,
                'item' => [
                    'id_order' => $orderId,
                    'status' => 'success',
                    'error_message' => null,
                ],
            ];
        } else {
            return [
                'success' => false,
                'message' => $result['error_message'] ?? 'Unknown error',
                'item' => [
                    'id_order' => $orderId,
                    'status' => 'failed',
                    'error_message' => $result['error_message'] ?? 'Unknown error',
                ],
            ];
        }
    }

    public function cancelOrderShipment(int $orderId, int $idBatch): array
    {
        try {
            $shipmentRow = $this->db->getRow(
                'SELECT data, status FROM `' . _DB_PREFIX_ . 'alsendo_order_shipment`
             WHERE id_order = ' . (int) $orderId
            );

            if (!$shipmentRow) {
                return ['success' => false, 'message' => 'No shipment found'];
            }

            $data = json_decode($shipmentRow['data'], true);
            $externalId = $this->resolveCancelExternalId($data);

            if (!$externalId) {
                return ['success' => false, 'message' => 'No external ID'];
            }

            if ($shipmentRow['status'] === 'cancelled') {
                return ['success' => false, 'message' => 'Shipment already cancelled'];
            }

            $result = $this->wrapperService->cancelOrder($externalId);

            if ($result->isSuccess()) {
                $this->db->update(
                    'alsendo_order_shipment',
                    ['status' => 'cancelled'],
                    'id_order = ' . (int) $orderId
                );

                $this->updatePrestaShopOrderStatus($orderId, 'cancelled');

                OrderDetailsService::syncTrackingNumber($orderId, '');

                return ['success' => true, 'message' => 'Shipment cancelled'];
            } else {
                return ['success' => false, 'message' => $result->getError()];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function cancelBatch(int $idBatch): array
    {
        try {
            $batch = $this->db->getRow(
                'SELECT id_batch, status FROM `' . _DB_PREFIX_ . 'alsendo_bulk_send_batch`
                 WHERE id_batch = ' . (int) $idBatch
            );

            if (!$batch) {
                return ['success' => false, 'message' => 'Batch not found'];
            }

            $items = $this->db->executeS(
                'SELECT id_item, id_order, status FROM `' . _DB_PREFIX_ . 'alsendo_bulk_send_item`
                 WHERE id_batch = ' . (int) $idBatch
            );

            $cancelledCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($items as $item) {
                if ($item['status'] === 'success') {
                    $shipmentRow = $this->db->getRow(
                        'SELECT data, status, previous_order_state FROM `' . _DB_PREFIX_ . 'alsendo_order_shipment`
                         WHERE id_order = ' . (int) $item['id_order']
                    );

                    if ($shipmentRow) {
                        $data = json_decode($shipmentRow['data'], true);
                        $externalId = $this->resolveCancelExternalId($data);

                        if ($externalId) {
                            $result = $this->wrapperService->cancelOrder($externalId);

                            if ($result->isSuccess()) {
                                ++$cancelledCount;

                                $previousState = (int) ($shipmentRow['previous_order_state'] ?? 0);
                                if ($previousState > 0) {
                                    $order = new \Order((int) $item['id_order']);
                                    if (\Validate::isLoadedObject($order) && (int) $order->current_state !== $previousState) {
                                        $history = new \OrderHistory();
                                        $history->id_order = (int) $item['id_order'];
                                        $history->changeIdOrderState($previousState, $order, true);
                                        $history->addWithemail(true);
                                    }
                                } else {
                                    $this->updatePrestaShopOrderStatus((int) $item['id_order'], 'cancelled');
                                }

                                OrderDetailsService::syncTrackingNumber((int) $item['id_order'], '');

                                $this->db->delete(
                                    'alsendo_order_shipment',
                                    'id_order = ' . (int) $item['id_order']
                                );
                            } else {
                                $errors[] = 'Order ' . $item['id_order'] . ': ' . $result->getError();
                                ++$errorCount;
                            }
                        }
                    }
                }

                $this->db->update(
                    'alsendo_bulk_send_item',
                    [
                        'status' => 'cancelled',
                        'error_type' => 'other',
                        'error_message' => 'Cancelled by user',
                        'completed_at' => ['type' => 'sql', 'value' => 'NOW()'],
                    ],
                    'id_item = ' . (int) $item['id_item']
                );
            }

            $this->db->update(
                'alsendo_bulk_send_batch',
                [
                    'status' => 'cancelled',
                    'completed_at' => ['type' => 'sql', 'value' => 'NOW()'],
                ],
                'id_batch = ' . (int) $idBatch
            );

            $message = 'Batch cancelled successfully';
            if ($cancelledCount > 0) {
                $message .= '. Cancelled ' . $cancelledCount . ' shipment(s)';
            }
            if ($errorCount > 0) {
                $message .= '. Failed to cancel ' . $errorCount . ' shipment(s)';
            }

            return [
                'success' => true,
                'message' => $message,
                'cancelled' => $cancelledCount,
                'errors' => $errorCount,
                'error_details' => $errors,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error cancelling batch: ' . $e->getMessage(),
            ];
        }
    }

    public function getWaybillsForBatch(int $idBatch, bool $merged = true): array
    {
        $items = $this->db->executeS(
            'SELECT id_order FROM `' . _DB_PREFIX_ . 'alsendo_bulk_send_item`
         WHERE id_batch = ' . (int) $idBatch . '
         AND status = "success"'
        );

        if (empty($items)) {
            return ['error' => 'No waybills available'];
        }

        $waybills = [];
        foreach ($items as $item) {
            try {
                $shipment = $this->db->getRow(
                    'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_shipment`
                 WHERE id_order = ' . (int) $item['id_order']
                );

                if (!$shipment) {
                    continue;
                }

                $data = json_decode($shipment['data'], true);
                $externalId = $this->resolveWaybillExternalId($data);
                $waybillNumber = $data['waybill_number'] ?? null;

                if (!$externalId) {
                    continue;
                }

                $result = $this->wrapperService->getWaybill($externalId);
                if ($result->isSuccess()) {
                    $waybills[$item['id_order']] = [
                        'order_id' => $item['id_order'],
                        'waybill_number' => $waybillNumber,
                        'pdf_base64' => $result->getData()['waybill'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return ['waybills' => $waybills];
    }

    public function getWaybill(int $orderId): array
    {
        try {
            $shipment = $this->db->getRow(
                'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_shipment`
             WHERE id_order = ' . (int) $orderId
            );

            if (!$shipment) {
                return ['error' => 'No shipment found'];
            }

            $data = json_decode($shipment['data'], true);
            $externalId = $this->resolveWaybillExternalId($data);

            if (!$externalId) {
                return ['error' => 'No external ID'];
            }

            $result = $this->wrapperService->getWaybill($externalId);

            if ($result->isSuccess()) {
                return $result->getData();
            } else {
                return ['error' => $result->getError()];
            }
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function validateOrderForShipping(\Order $order): array
    {
        if (count($order->getProducts()) === 0) {
            return ['success' => false, 'message' => 'Order has no items'];
        }

        if ($order->id_currency <= 0) {
            return ['success' => false, 'message' => 'Invalid order currency'];
        }

        return ['success' => true];
    }

    private function categorizeException(\Exception $e): string
    {
        $message = strtolower($e->getMessage());

        if (strpos($message, 'timeout') !== false) {
            return 'timeout';
        }
        if (strpos($message, 'duplicate') !== false || strpos($message, 'already') !== false) {
            return 'duplicate';
        }
        if (strpos($message, 'validation') !== false) {
            return 'validation';
        }
        if (strpos($message, 'api') !== false || strpos($message, 'connection') !== false) {
            return 'api';
        }

        return 'other';
    }

    // NOT USED COMMENTED FOR PRESTASHOP VALIDATION
    /*
    private function getBatchDetails(int $idBatch): array
    {
        return $this->db->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'alsendo_bulk_send_batch`
             WHERE id_batch = ' . (int)$idBatch
        );
    }

    private function getBatchItems(int $idBatch): array
    {
        return $this->db->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'alsendo_bulk_send_item`
             WHERE id_batch = ' . (int)$idBatch . '
             ORDER BY id_item ASC'
        );
    }

    private function updateBatchStatus(int $idBatch, string $status, array $data = []): void
    {
        $data['status'] = pSQL($status);
        $this->db->update(
            'alsendo_bulk_send_batch',
            $data,
            'id_batch = ' . (int)$idBatch
        );
    }
    */

    private function updateBatchItem(int $orderId, int $idBatch, array $data): void
    {
        $this->db->update(
            'alsendo_bulk_send_item',
            $data,
            'id_order = ' . (int) $orderId . ' AND id_batch = ' . (int) $idBatch
        );
    }

    public function getWaybillsAsZip(int $idBatch, string $outputPath = null): array
    {
        $items = $this->db->executeS(
            'SELECT id_order, waybill_number FROM `' . _DB_PREFIX_ . 'alsendo_bulk_send_item`
         WHERE id_batch = ' . (int) $idBatch . '
         AND status = "success"
         AND waybill_number IS NOT NULL'
        );

        if (empty($items)) {
            return ['error' => 'No waybills available'];
        }

        $zip = new \ZipArchive();
        $tempZipPath = sys_get_temp_dir() . '/alsendo_waybills_' . $idBatch . '_' . time() . '.zip';

        if ($zip->open($tempZipPath, \ZipArchive::CREATE) !== true) {
            return ['error' => 'Failed to create ZIP file'];
        }

        $successCount = 0;

        foreach ($items as $item) {
            try {
                $result = $this->wrapperService->getWaybill($item['waybill_number']);

                if ($result->isSuccess()) {
                    $data = $result->getData();
                    $pdfBase64 = $data['waybill'] ?? null;

                    if ($pdfBase64) {
                        $pdfBinary = base64_decode($pdfBase64);
                        $filename = 'waybill_order_' . $item['id_order'] . '.pdf';
                        $zip->addFromString($filename, $pdfBinary);
                        ++$successCount;
                    }
                }
            } catch (\Exception $e) {
                error_log('Error downloading waybill for order ' . $item['id_order'] . ': ' . $e->getMessage());
                continue;
            }
        }

        $zip->close();

        if ($successCount === 0) {
            @unlink($tempZipPath);

            return ['error' => 'No waybills could be downloaded'];
        }

        return [
            'success' => true,
            'zip_path' => $tempZipPath,
            'count' => $successCount,
            'filename' => 'waybills_batch_' . $idBatch . '.zip',
        ];
    }

    public function downloadWaybillFile(int $orderId): array
    {
        try {
            $shipment = $this->db->getRow(
                'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_shipment`
             WHERE id_order = ' . (int) $orderId
            );

            if (!$shipment || empty($shipment['data'])) {
                return ['error' => 'No shipment data found'];
            }

            $data = json_decode($shipment['data'], true);
            $externalId = $this->resolveWaybillExternalId($data);

            if (!$externalId) {
                return ['error' => 'No external ID in shipment data'];
            }

            $result = $this->wrapperService->getWaybill($externalId);

            if (!$result->isSuccess()) {
                return ['error' => $result->getError()];
            }

            $responseData = $result->getData();
            $pdfBase64 = $responseData['waybill'] ?? null;

            if (!$pdfBase64) {
                return ['error' => 'No PDF data in response'];
            }

            return [
                'success' => true,
                'pdf_binary' => base64_decode($pdfBase64),
                'filename' => 'waybill_order_' . $orderId . '.pdf',
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function resolveCancelExternalId(array $data): ?string
    {
        $region = \Configuration::get('ALSENDO_REGION') ?: 'pl';
        if ($region === 'cz' && !empty($data['carrier_tracking_number'])) {
            return $data['carrier_tracking_number'];
        }

        return $data['id'] ?? null;
    }

    private function resolveWaybillExternalId(array $data): ?string
    {
        $region = \Configuration::get('ALSENDO_REGION') ?: 'pl';
        if ($region === 'cz' && !empty($data['carrier_tracking_number'])) {
            return $data['carrier_tracking_number'];
        }

        return $data['id'] ?? null;
    }

    // NOT USED COMMENTED FOR PRESTASHOP VALIDATION
    /*
    private function getCarrierServiceMapping(int $carrierId): array
    {
        if ($carrierId <= 0) {
            return [null, null, false];
        }

        $db = Db::getInstance();
        $shopId = (int)$this->context->shop->id;

        try {
            $sql = 'SELECT id_service, has_map FROM `' . _DB_PREFIX_ . 'alsendo_shipping_methods`
                WHERE id_carrier = ' . (int)$carrierId . ' AND id_shop = ' . $shopId;
            $row = $db->getRow($sql);

            if ($row && isset($row['id_service'])) {
                $serviceId = is_numeric($row['id_service']) ? (int)$row['id_service'] : $row['id_service'];
                return [(int)$carrierId, $serviceId, (bool)$row['has_map']];
            }

            return [null, null, false];
        } catch (Exception $e) {
            error_log('[Alsendo] Error in getCarrierServiceMapping: ' . $e->getMessage());
            return [null, null, false];
        }
    }
    */

    private function updatePrestaShopOrderStatus(int $orderId, string $alsendoStatus): void
    {
        try {
            $alsendoPreparingStatus = \Configuration::get('ALSENDO_OS_PREPARING');

            if (strtolower($alsendoStatus) === 'cancelled') {
                $shipmentRow = \Db::getInstance()->getRow(
                    'SELECT previous_order_state FROM `' . _DB_PREFIX_ . 'alsendo_order_shipment`
                     WHERE id_order = ' . (int) $orderId
                );
                if ($shipmentRow && !empty($shipmentRow['previous_order_state'])) {
                    $newStateId = (int) $shipmentRow['previous_order_state'];
                } else {
                    $newStateId = (int) \Configuration::get('PS_OS_CANCELED');
                }
            } else {
                $statusMap = [
                    'submitted' => $alsendoPreparingStatus ?: \Configuration::get('PS_OS_SHIPPING'),
                    'new' => $alsendoPreparingStatus ?: \Configuration::get('PS_OS_SHIPPING'),
                    'success' => $alsendoPreparingStatus ?: \Configuration::get('PS_OS_SHIPPING'),
                    'ordered' => $alsendoPreparingStatus ?: \Configuration::get('PS_OS_SHIPPING'),
                    'created' => $alsendoPreparingStatus ?: \Configuration::get('PS_OS_SHIPPING'),
                    'exported' => $alsendoPreparingStatus ?: \Configuration::get('PS_OS_SHIPPING'),
                    'onpickup' => $alsendoPreparingStatus ?: \Configuration::get('PS_OS_SHIPPING'),
                    'intransit' => \Configuration::get('PS_OS_SHIPPING'),
                    'ondelivery' => \Configuration::get('PS_OS_SHIPPING'),
                    'delivered' => \Configuration::get('PS_OS_DELIVERED'),
                ];

                $newStateId = $statusMap[strtolower($alsendoStatus)] ?? null;
            }

            if (!$newStateId) {
                \PrestaShopLogger::addLog(
                    '[Alsendo] Unknown shipment status: ' . $alsendoStatus . ' for order ' . $orderId,
                    2,
                    null,
                    'Order',
                    $orderId
                );

                return;
            }

            $order = new \Order($orderId);
            if (!\Validate::isLoadedObject($order)) {
                return;
            }

            if ((int) $order->current_state === (int) $newStateId) {
                return;
            }

            $history = new \OrderHistory();
            $history->id_order = $orderId;
            $history->changeIdOrderState((int) $newStateId, $order, true);
            $history->addWithemail(true);
        } catch (\Exception $e) {
            error_log('[Alsendo] Failed to update order status for order ' . $orderId . ': ' . $e->getMessage());
        }
    }

    private function flattenOrderData(array $data): array
    {
        $flat = [];

        if (!empty($data['sender'])) {
            $s = $data['sender'];
            $regionCountryMap = ['pl' => 'PL', 'cz' => 'CZ', 'ro' => 'RO'];
            $regionCountry = $regionCountryMap[\Configuration::get('ALSENDO_REGION') ?: 'pl'] ?? 'PL';
            $flat['sender_address_type'] = $s['address_type'] ?? 'company';
            $flat['sender_company_name'] = $s['company_name'] ?? '';
            $flat['sender_full_name'] = $s['full_name'] ?? '';
            $flat['sender_street'] = $s['street'] ?? '';
            $flat['sender_building_number'] = $s['building_number'] ?? '';
            $flat['sender_apartment_number'] = $s['apartment_number'] ?? '';
            $flat['sender_block'] = $s['block'] ?? '';
            $flat['sender_entrance'] = $s['entrance'] ?? '';
            $flat['sender_floor'] = $s['floor'] ?? '';
            $flat['sender_flat'] = $s['flat'] ?? '';
            $flat['sender_postal_code'] = $s['postal_code'] ?? '';
            $flat['sender_city'] = $s['city'] ?? '';
            $flat['sender_country'] = $s['country'] ?? $regionCountry;
            $flat['sender_phone_number'] = $s['phone_number'] ?? '';
            $flat['sender_email'] = $s['email'] ?? '';
            $flat['sender_contact_person'] = $s['contact_person'] ?? '';
            $flat['sender_bank_account_number'] = $s['bank_account_number'] ?? '';
            $flat['sender_bank_code'] = $s['bank_code'] ?? '';
            $flat['sender_additional_bank_account_number'] = $s['additional_bank_account_number'] ?? '';
            $flat['sender_external_id'] = $s['external_id'] ?? '';
        }

        if (!empty($data['package'])) {
            $p = $data['package'];
            $bsFlatPkgDefault = ((\Configuration::get('ALSENDO_REGION') ?: 'pl') === 'cz') ? 'PACKAGE' : (((\Configuration::get('ALSENDO_REGION') ?: 'pl') === 'ro') ? 'package' : 'PACZKA');
            $flat['package_shipment_type'] = $p['shipment_type'] ?? $p['shipment_packaging'] ?? $p['package_type'] ?? $bsFlatPkgDefault;
            $flat['package_is_nstd'] = (int) ($p['is_nstd'] ?? 0);
            $flat['package_width'] = (float) ($p['width'] ?? 0);
            $flat['package_length'] = (float) ($p['length'] ?? 0);
            $flat['package_height'] = (float) ($p['height'] ?? 0);
            $flat['package_weight'] = (float) ($p['weight'] ?? 0);
            $flat['package_content'] = $p['package_content'] ?? '';
            $flat['package_cod'] = (float) ($p['cod_value'] ?? 0);
            $flat['package_declared_value'] = (float) ($p['declared_value'] ?? 0);
        }

        if (!empty($data['shipment'])) {
            $s = $data['shipment'];
            $defaultPT = ((\Configuration::get('ALSENDO_REGION') ?: 'pl') === 'cz') ? 'ONDEMAND' : 'COURIER';
            $flat['selected_pickup_type'] = $s['pickup_type'] ?? $defaultPT;
            $flat['shipment_preferred_pickup_date'] = $s['preferred_pickup_date'] ?? '';
            $flat['shipment_preferred_pickup_hours_from'] = $s['preferred_pickup_hours_from'] ?? '08:00';
            $flat['shipment_preferred_pickup_hours_to'] = $s['preferred_pickup_hours_to'] ?? '17:00';
            $flat['shipment_selected_service'] = $s['selected_service'] ?? null;
            if (!empty($s['shipment_pickup_point'])) {
                $flat['shipment_pickup_point'] = json_encode($s['shipment_pickup_point']);
            }
        }

        if (!empty($data['recipient'])) {
            $r = $data['recipient'];
            $flat['shipping_first_name'] = $r['first_name'] ?? '';
            $flat['shipping_last_name'] = $r['last_name'] ?? '';

            if (!empty($r['street'])) {
                $flat['shipping_street'] = $r['street'];
                $flat['shipping_building_number'] = $r['building_number'] ?? '';
                $flat['shipping_apartment_number'] = $r['apartment_number'] ?? '';
                $flat['shipping_block'] = $r['block'] ?? '';
                $flat['shipping_entrance'] = $r['entrance'] ?? '';
                $flat['shipping_floor'] = $r['floor'] ?? '';
                $flat['shipping_flat'] = $r['flat'] ?? '';
            } else {
                $parsed = AddressParser::parseAddressToComponents($r['address'] ?? '', $r['address2'] ?? '');
                $flat['shipping_street'] = $parsed['street'];
                $flat['shipping_building_number'] = $parsed['building_number'];
                $flat['shipping_apartment_number'] = $parsed['apartment_number'];
                $region = \Configuration::get('ALSENDO_REGION') ?: 'pl';
                if ($region === 'ro') {
                    $roSource = $r['address2'] ?? '';
                    if (empty($roSource)) {
                        $roSource = $parsed['apartment_number'];
                    }
                    $roComponents = AddressParser::parseRomanianComponents($roSource);
                    $flat['shipping_block'] = $roComponents['block'];
                    $flat['shipping_entrance'] = $roComponents['entrance'];
                    $flat['shipping_floor'] = $roComponents['floor'];
                    $flat['shipping_flat'] = $roComponents['flat'];
                    $flat['shipping_apartment_number'] = '';
                }
            }

            $flat['shipping_city'] = $r['city'] ?? '';
            $flat['shipping_postal_code'] = $r['postal_code'] ?? '';
            $flat['shipping_country'] = $r['country'] ?? '';
            $flat['shipping_phone_number'] = $r['phone'] ?? '';
            $flat['shipping_email'] = $r['email'] ?? '';
        }

        return $flat;
    }
}
