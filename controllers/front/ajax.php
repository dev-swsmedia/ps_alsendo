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

class AlsendoAjaxModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $ajax = true;

    public function initContent()
    {
        header('Content-Type: application/json');

        $action = Tools::getValue('action');
        $response = ['success' => false, 'error' => 'Invalid action'];

        try {
            switch ($action) {
                case 'save_pickup_point':
                    $response = $this->handleSavePickupPoint();
                    break;
                case 'get_pickup_point':
                    $response = $this->handleGetPickupPoint();
                    break;
                default:
                    $response = ['success' => false, 'error' => 'Unknown action'];
                    break;
            }
        } catch (Exception $e) {
            $response = ['success' => false, 'error' => $e->getMessage()];
        }

        exit(json_encode($response));
    }

    private function handleSavePickupPoint(): array
    {
        $id_cart = (int) Tools::getValue('id_cart');
        $pickup_point = Tools::getValue('pickup_point');
        $pickup_display = '';

        if ($pickup_point) {
            $data = json_decode($pickup_point, true);
            if (is_array($data)) {
                $pickup_display = $data['code'] ?? '';
                if (!empty($data['description'])) {
                    $pickup_display .= ' - ' . $data['description'];
                } elseif (!empty($data['street'])) {
                    $pickup_display .= ' - ' . $data['street'];
                }
            } else {
                $pickup_display = (string) $pickup_point;
            }
        }

        if ($id_cart && $pickup_point) {
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'alsendo_order_pickup`
                        (id_cart, pickup_point, pickup_point_display)
                    VALUES (' . (int) $id_cart . ', "' . pSQL($pickup_point) . '", "' . pSQL($pickup_display) . '")
                    ON DUPLICATE KEY UPDATE 
                        pickup_point = VALUES(pickup_point),
                        pickup_point_display = VALUES(pickup_point_display)';
            $ok = Db::getInstance()->execute($sql);

            return [
                'success' => (bool) $ok,
                'id_cart' => $id_cart,
                'pickup_point' => $pickup_point,
                'pickup_point_display' => $pickup_display,
            ];
        }

        return ['success' => false, 'error' => 'Missing cart id or pickup point'];
    }

    private function handleGetPickupPoint(): array
    {
        $id_cart = (int) Tools::getValue('id_cart');
        if (!$id_cart) {
            return [
                'success' => false,
                'error' => 'Missing cart id',
            ];
        }

        $sql = 'SELECT pickup_point, pickup_point_display 
                FROM `' . _DB_PREFIX_ . 'alsendo_order_pickup`
                WHERE id_cart = ' . (int) $id_cart;
        $row = Db::getInstance()->getRow($sql);

        if ($row) {
            return [
                'success' => true,
                'id_cart' => $id_cart,
                'pickup_point' => $row['pickup_point'],
                'pickup_point_display' => $row['pickup_point_display'],
            ];
        }

        return ['success' => false, 'error' => 'No pickup point saved'];
    }
}
