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

use Alsendo\Services\BulkSendService;
use Alsendo\Services\WrapperService;

class AdminAlsendoBulkSendController extends ModuleAdminController
{
    private $bulkSendService;

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        $this->bulkSendService = new BulkSendService(Db::getInstance(), $this->context);
    }

    public function checkAccess()
    {
        if (Tools::getIsset('ajax')) {
            return true;
        }

        if (Tools::isSubmit('submitBulkActionAslendobulk')) {
            return true;
        }

        return parent::checkAccess();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitBulkActionAslendobulk')) {
            $orderIds = [];

            if (Tools::getIsset('order_ids')) {
                $orderIdsString = Tools::getValue('order_ids', '');
                $orderIds = array_filter(array_map('intval', explode(',', $orderIdsString)));
            }

            if (empty($orderIds)) {
                $this->errors[] = $this->module->l('No orders selected', 'AdminAlsendoBulkSendController');

                return;
            }

            $_SESSION['alsendo_bulk_order_ids'] = $orderIds;
        }
    }

    private function loadExistingBatch(int $batchId)
    {
        $batchRow = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'alsendo_bulk_send_batch`
             WHERE id_batch = ' . (int) $batchId
        );

        if (!$batchRow) {
            $this->errors[] = $this->module->l('Batch not found', 'AdminAlsendoBulkSendController');

            return parent::initContent();
        }

        $batchItems = Db::getInstance()->executeS(
            'SELECT id_order, status, error_message, waybill_number
             FROM `' . _DB_PREFIX_ . 'alsendo_bulk_send_item`
             WHERE id_batch = ' . (int) $batchId
        );

        if (empty($batchItems)) {
            $this->errors[] = $this->module->l('No items in batch', 'AdminAlsendoBulkSendController');

            return parent::initContent();
        }

        $orders = [];
        foreach ($batchItems as $item) {
            try {
                $orderId = (int) $item['id_order'];
                $order = new Order($orderId);
                $address = new Address($order->id_address_delivery);

                $orderDetailsRow = Db::getInstance()->getRow(
                    'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_details`
                     WHERE id_order = ' . (int) $orderId
                );
                $orderData = $orderDetailsRow
                    ? json_decode($orderDetailsRow['data'], true)
                    : [];

                $shippingMethod = Db::getInstance()->getRow(
                    'SELECT method_name FROM `' . _DB_PREFIX_ . 'alsendo_shipping_methods`
                     WHERE id_carrier = ' . (int) $order->id_carrier
                );

                $hasCustomData = !empty($orderData['sender']['full_name']) || !empty($orderData['sender']['company_name'])
                    || !empty($orderData['package']['width']) || !empty($orderData['package']['weight'])
                    || !empty($orderData['recipient']['first_name']) || !empty($orderData['recipient']['address']);

                $recipient = $orderData['recipient'] ?? [];
                $orders[] = [
                    'id_order' => $orderId,
                    'reference' => $order->reference,
                    'address' => [
                        'firstname' => !empty($recipient['first_name']) ? $recipient['first_name'] : $address->firstname,
                        'lastname' => !empty($recipient['last_name']) ? $recipient['last_name'] : $address->lastname,
                        'company' => !empty($recipient['company']) ? $recipient['company'] : $address->company,
                        'address' => !empty($recipient['address']) ? $recipient['address'] : $address->address1,
                        'address2' => !empty($recipient['address2']) ? $recipient['address2'] : $address->address2,
                        'postcode' => !empty($recipient['postal_code']) ? $recipient['postal_code'] : $address->postcode,
                        'city' => !empty($recipient['city']) ? $recipient['city'] : $address->city,
                        'country' => !empty($recipient['country']) ? $recipient['country'] : Country::getNameById($this->context->language->id, $address->id_country),
                    ],
                    'template_name' => $orderData['package']['template_name'] ?? 'No template',
                    'shipping_method' => $shippingMethod['method_name'] ?? '-',
                    'package_details' => [
                        'width' => $orderData['package']['width'] ?? '-',
                        'length' => $orderData['package']['length'] ?? '-',
                        'height' => $orderData['package']['height'] ?? '-',
                        'weight' => $orderData['package']['weight'] ?? '-',
                    ],
                    'status' => $item['status'],
                    'error' => $item['error_message'],
                    'already_sent' => false,
                    'has_custom_data' => $hasCustomData,
                ];
            } catch (Exception $e) {
                continue;
            }
        }

        $this->context->smarty->assign([
            'batch_id' => $batchId,
            'batch_reference' => $batchRow['batch_reference'],
            'orders' => $orders,
            'total_orders' => count($orders),
            'ajax_url' => $this->context->link->getAdminLink('AdminAlsendoBulkSend'),
            'alsendo_order_link' => $this->context->link->getAdminLink('AdminAlsendoOrder'),
        ]);

        $this->setTemplate('bulk_send.tpl');
    }

    public function initContent()
    {
        if (Tools::getIsset('ajax')) {
            $this->handleAjax();

            return;
        }

        $batchId = (int) Tools::getValue('batch_id');

        if ($batchId > 0) {
            $this->loadExistingBatch($batchId);

            return;
        }

        $orderIds = isset($_SESSION['alsendo_bulk_order_ids'])
            ? (array) $_SESSION['alsendo_bulk_order_ids']
            : [];

        unset($_SESSION['alsendo_bulk_order_ids']);

        if (empty($orderIds)) {
            $this->errors[] = $this->module->l('No orders provided', 'AdminAlsendoBulkSendController');

            return parent::initContent();
        }

        $batch = $this->bulkSendService->createBatch($orderIds, $this->context->employee->id);

        $orders = [];
        foreach ($orderIds as $orderId) {
            try {
                $order = new Order($orderId);
                $address = new Address($order->id_address_delivery);

                $orderDetailsRow = Db::getInstance()->getRow(
                    'SELECT data FROM `' . _DB_PREFIX_ . 'alsendo_order_details` 
                     WHERE id_order = ' . (int) $orderId
                );
                $orderData = $orderDetailsRow
                    ? json_decode($orderDetailsRow['data'], true)
                    : [];

                $shippingMethod = Db::getInstance()->getRow(
                    'SELECT method_name FROM `' . _DB_PREFIX_ . 'alsendo_shipping_methods` 
                     WHERE id_carrier = ' . (int) $order->id_carrier
                );

                $hasCustomData = !empty($orderData['sender']['full_name']) || !empty($orderData['sender']['company_name'])
                    || !empty($orderData['package']['width']) || !empty($orderData['package']['weight'])
                    || !empty($orderData['recipient']['first_name']) || !empty($orderData['recipient']['address']);

                $recipient = $orderData['recipient'] ?? [];
                $orders[] = [
                    'id_order' => $orderId,
                    'reference' => $order->reference,
                    'address' => [
                        'firstname' => !empty($recipient['first_name']) ? $recipient['first_name'] : $address->firstname,
                        'lastname' => !empty($recipient['last_name']) ? $recipient['last_name'] : $address->lastname,
                        'company' => !empty($recipient['company']) ? $recipient['company'] : $address->company,
                        'address' => !empty($recipient['address']) ? $recipient['address'] : $address->address1,
                        'address2' => !empty($recipient['address2']) ? $recipient['address2'] : $address->address2,
                        'postcode' => !empty($recipient['postal_code']) ? $recipient['postal_code'] : $address->postcode,
                        'city' => !empty($recipient['city']) ? $recipient['city'] : $address->city,
                        'country' => !empty($recipient['country']) ? $recipient['country'] : Country::getNameById($this->context->language->id, $address->id_country),
                    ],
                    'template_name' => $orderData['package']['template_name'] ?? 'No template',
                    'shipping_method' => $shippingMethod['method_name'] ?? '-',
                    'package_details' => [
                        'width' => $orderData['package']['width'] ?? '-',
                        'length' => $orderData['package']['length'] ?? '-',
                        'height' => $orderData['package']['height'] ?? '-',
                        'weight' => $orderData['package']['weight'] ?? '-',
                    ],
                    'status' => 'pending',
                    'error' => null,
                    'already_sent' => false,
                    'has_custom_data' => $hasCustomData,
                ];

                $existingShipment = Db::getInstance()->getRow(
                    'SELECT status, waybill_number FROM `' . _DB_PREFIX_ . 'alsendo_order_shipment`
                     WHERE id_order = ' . (int) $orderId
                );

                if ($existingShipment) {
                    $lastIndex = count($orders) - 1;
                    if ($existingShipment['status'] === 'submitted' || $existingShipment['status'] === 'success' || $existingShipment['status'] === 'NEW') {
                        $orders[$lastIndex]['status'] = 'already_sent';
                        $orders[$lastIndex]['error'] = 'Already sent (Waybill: ' . $existingShipment['waybill_number'] . ')';
                        $orders[$lastIndex]['already_sent'] = true;
                    } elseif ($existingShipment['status'] === 'cancelled') {
                        $orders[$lastIndex]['status'] = 'cancelled';
                        $orders[$lastIndex]['error'] = 'Previously cancelled';
                    }
                }
            } catch (Exception $e) {
                continue;
            }
        }

        if (empty($orders)) {
            $this->errors[] = $this->module->l('No orders could be processed', 'AdminAlsendoBulkSendController');

            return parent::initContent();
        }

        $this->context->smarty->assign([
            'batch_id' => $batch->id,
            'batch_reference' => $batch->reference,
            'orders' => $orders,
            'total_orders' => count($orders),
            'ajax_url' => $this->context->link->getAdminLink('AdminAlsendoBulkSend'),
            'alsendo_order_link' => $this->context->link->getAdminLink('AdminAlsendoOrder'),
        ]);

        $this->setTemplate('bulk_send.tpl');
    }

    private function handleAjax()
    {
        $action = Tools::getValue('action');

        switch ($action) {
            case 'send_bulk':
                $this->sendBulk();
                break;
            case 'retry_failed':
                $this->retryFailed();
                break;
            case 'retry_single':
                $this->retrySingle();
                break;
            case 'cancel_order':
                $this->cancelOrder();
                break;
            case 'cancel_batch':
                $this->cancelBatch();
                break;
            case 'download_waybill':
                $this->downloadWaybill();
                break;
            case 'download_all_waybills_zip':
                $this->downloadAllWaybillsZip();
                break;
            case 'get_batch_status':
                $this->getBatchStatus();
                break;
            case 'get_package_templates':
                $this->getPackageTemplates();
                break;
        }
    }

    private function getPackageTemplates()
    {
        $packageTemplates = json_decode(Configuration::get('ALSENDO_SHIPPING_SETTINGS_LIST', null, null, null, '[]'), true) ?: [];
        $this->jsonResponse(['success' => true, 'templates' => $packageTemplates]);
    }

    private function sendBulk()
    {
        $batchId = (int) Tools::getValue('batch_id');
        $templateOverridesJson = Tools::getValue('template_overrides', '');
        $templateOverrides = !empty($templateOverridesJson) ? json_decode($templateOverridesJson, true) : [];
        $intKeyedOverrides = [];
        if (is_array($templateOverrides)) {
            foreach ($templateOverrides as $oid => $tidx) {
                $intKeyedOverrides[(int) $oid] = (int) $tidx;
            }
        }
        $results = $this->bulkSendService->sendBatch($batchId, $intKeyedOverrides);
        $this->jsonResponse($results);
    }

    private function retryFailed()
    {
        $batchId = (int) Tools::getValue('batch_id');
        $templateOverridesJson = Tools::getValue('template_overrides', '');
        $templateOverrides = !empty($templateOverridesJson) ? json_decode($templateOverridesJson, true) : [];
        $intKeyedOverrides = [];
        if (is_array($templateOverrides)) {
            foreach ($templateOverrides as $oid => $tidx) {
                $intKeyedOverrides[(int) $oid] = (int) $tidx;
            }
        }
        $results = $this->bulkSendService->retryFailedOrders($batchId, $intKeyedOverrides);
        $this->jsonResponse($results);
    }

    private function retrySingle()
    {
        $batchId = (int) Tools::getValue('batch_id');
        $orderId = (int) Tools::getValue('id_order');
        $results = $this->bulkSendService->retrySingleOrder($batchId, $orderId);
        $this->jsonResponse($results);
    }

    private function cancelOrder()
    {
        $orderId = (int) Tools::getValue('id_order');
        $batchId = (int) Tools::getValue('batch_id');
        $result = $this->bulkSendService->cancelOrderShipment($orderId, $batchId);
        $this->jsonResponse($result);
    }

    private function downloadWaybill()
    {
        $orderId = (int) Tools::getValue('id_order');

        $row = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'alsendo_order_shipment` WHERE id_order=' . (int) $orderId
        );

        if (!$row) {
            $this->jsonResponse(['success' => false, 'error' => 'Shipment not found']);

            return;
        }

        $data = json_decode($row['data'], true);
        $region = Configuration::get('ALSENDO_REGION') ?: 'pl';
        if ($region === 'cz' && !empty($data['carrier_tracking_number'])) {
            $externalId = $data['carrier_tracking_number'];
        } else {
            $externalId = $data['id'] ?? null;
        }

        if (!$externalId) {
            $this->jsonResponse(['success' => false, 'error' => 'No external ID']);

            return;
        }

        $w = new WrapperService();
        $res = $w->getWaybill((string) $externalId);

        if ($res->isSuccess() && !empty($res->getData()['waybill'])) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="waybill_' . $externalId . '.pdf"');
            echo base64_decode($res->getData()['waybill']);
            exit;
        }

        $this->jsonResponse($res->toArray());
    }

    private function downloadAllWaybillsZip()
    {
        $batchId = (int) Tools::getValue('batch_id');

        $items = Db::getInstance()->executeS(
            'SELECT id_order FROM `' . _DB_PREFIX_ . 'alsendo_bulk_send_item`
             WHERE id_batch = ' . (int) $batchId . ' AND status = "success"'
        );

        if (empty($items)) {
            $this->jsonResponse(['error' => 'No successful orders in batch']);

            return;
        }

        $zipPath = sys_get_temp_dir() . '/waybills_batch_' . $batchId . '_' . time() . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->jsonResponse(['error' => 'Could not create ZIP file. Check directory permissions.']);

            return;
        }

        $w = new WrapperService();
        $addedFiles = 0;

        foreach ($items as $item) {
            $orderId = (int) $item['id_order'];

            $row = Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'alsendo_order_shipment` WHERE id_order=' . (int) $orderId
            );

            if (!$row) {
                continue;
            }

            $data = json_decode($row['data'], true);
            $region = Configuration::get('ALSENDO_REGION') ?: 'pl';
            if ($region === 'cz' && !empty($data['carrier_tracking_number'])) {
                $externalId = $data['carrier_tracking_number'];
            } else {
                $externalId = $data['id'] ?? null;
            }

            if (!$externalId) {
                continue;
            }

            $res = $w->getWaybill((string) $externalId);

            if ($res->isSuccess() && !empty($res->getData()['waybill'])) {
                $pdfContent = base64_decode($res->getData()['waybill']);
                $zip->addFromString('waybill_order_' . $orderId . '_' . $externalId . '.pdf', $pdfContent);
                ++$addedFiles;
            }
        }

        $zip->close();

        if ($addedFiles === 0) {
            @unlink($zipPath);
            $this->jsonResponse(['error' => 'No waybills could be downloaded']);

            return;
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="waybills_batch_' . $batchId . '.zip"');
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);
        @unlink($zipPath);
        exit;
    }

    private function cancelBatch()
    {
        $batchId = (int) Tools::getValue('batch_id');

        if (!$batchId) {
            $this->jsonResponse(['success' => false, 'message' => $this->l('Invalid batch ID')]);

            return;
        }

        $result = $this->bulkSendService->cancelBatch($batchId);
        $this->jsonResponse($result);
    }

    private function getBatchStatus()
    {
        $batchId = (int) Tools::getValue('batch_id');

        $batch = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'alsendo_bulk_send_batch` 
             WHERE id_batch = ' . (int) $batchId
        );

        $items = Db::getInstance()->executeS(
            'SELECT id_order, status, error_type, error_message, waybill_number 
             FROM `' . _DB_PREFIX_ . 'alsendo_bulk_send_item` 
             WHERE id_batch = ' . (int) $batchId
        );

        $this->jsonResponse([
            'batch' => $batch,
            'items' => $items,
        ]);
    }

    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        exit(json_encode($data));
    }
}
