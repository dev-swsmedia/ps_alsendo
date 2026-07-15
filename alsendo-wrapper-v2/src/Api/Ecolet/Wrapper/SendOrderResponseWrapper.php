<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Ecolet\Wrapper;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Model\Order\SendOrderResponse;

class SendOrderResponseWrapper
{
    /**
     * @param array $orderToSend The raw 'order_to_send' object from Ecolet's response
     */
    public static function wrap(array $orderToSend): SendOrderResponse
    {
        $response = new SendOrderResponse();
        // order_id to prawdziwy ID zamówienia (dostępny po przetworzeniu),
        // id to order_to_send_id (dostępny od razu) — fallback
        $response->id = isset($orderToSend['order_id'])
            ? (string) $orderToSend['order_id']
            : (isset($orderToSend['id']) ? (string) $orderToSend['id'] : null);
        $response->status = $orderToSend['status'] ?? null;
        $response->serviceId = isset($orderToSend['order']['service_id']) ? (string) $orderToSend['order']['service_id'] : null;
        $response->serviceName = $orderToSend['order']['data']['courier']['service'] ?? null;
        $response->created = !empty($orderToSend['created_at']) ? new \DateTimeImmutable($orderToSend['created_at']) : null;

        return $response;
    }
}
