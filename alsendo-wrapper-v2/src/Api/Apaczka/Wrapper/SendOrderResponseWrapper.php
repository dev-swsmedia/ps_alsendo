<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Apaczka\Wrapper;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Model\Order\SendOrderResponse;

class SendOrderResponseWrapper
{
    /**
     * @param array $order The 'order' key from Apaczka's parsed response
     */
    public static function wrap(array $order): SendOrderResponse
    {
        $response = new SendOrderResponse();
        $response->id = isset($order['id']) ? (string) $order['id'] : null;
        $response->status = $order['status'] ?? null;
        $response->serviceId = isset($order['service_id']) ? (string) $order['service_id'] : null;
        $response->serviceName = $order['service_name'] ?? null;
        $response->waybillNumber = $order['waybill_number'] ?? null;
        $response->trackingUrl = $order['tracking_url'] ?? null;
        $response->created = !empty($order['created']) ? new \DateTimeImmutable($order['created']) : null;

        return $response;
    }
}
