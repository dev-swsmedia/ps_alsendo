<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Zaslat\Wrapper;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Model\Order\SendOrderResponse;

class SendOrderResponseWrapper
{
    /**
     * @param array $createData The 'data' key from Zaslat's shipments/create response
     * @param array $detailData The shipment detail from shipments/detail response (single shipment)
     */
    public static function wrap(array $createData, array $detailData = []): SendOrderResponse
    {
        $response = new SendOrderResponse();

        $response->id = $createData['order_number'];
        $response->status = $detailData['status'] ?? null;
        $response->serviceId = isset($detailData['service_id']) ? (string) $detailData['service_id'] : null;
        $response->serviceName = $detailData['carrier'] ?? null;
        $response->waybillNumber = !empty($createData['shipments'][0]) ? $createData['shipments'][0] : null;
        $response->trackingUrl = !empty($detailData['packages'][0]['carrier_tracking']) ? $detailData['packages'][0]['carrier_tracking'] : null;
        $response->created = !empty($detailData['order_date']) ? new \DateTimeImmutable($detailData['order_date']) : null;

        return $response;
    }
}
