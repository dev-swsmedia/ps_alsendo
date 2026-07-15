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

use Alsendo\AlsendoWrapper\Model\Order\Valuation\OrderValuation;
use Alsendo\AlsendoWrapper\Model\Order\Valuation\OrderValuationResponse;
use Alsendo\AlsendoWrapper\Model\Order\Valuation\PriceTableItem;

class OrderValuationResponseWrapper
{
    public static function wrap(array $response): OrderValuationResponse
    {
        $orderValuationResponse = new OrderValuationResponse();

        if (empty($response['rates'])) {
            return $orderValuationResponse;
        }

        foreach ($response['rates'] as $rate) {
            $valuation = new OrderValuation();
            $valuation->serviceId = isset($rate['service_id']) ? (string) $rate['service_id'] : null;
            $valuation->carrier = $rate['carrier'] ?? null;
            $valuation->service = $rate['service'] ?? null;

            $priceTable = new PriceTableItem();
            $priceTable->price = isset($rate['price']['value']) ? (string) $rate['price']['value'] : null;
            $priceTable->priceGross = isset($rate['price_vat']['value']) ? (string) $rate['price_vat']['value'] : null;
            $priceTable->currency = $rate['price']['currency'] ?? null;
            $valuation->priceTable = $priceTable;

            if (!empty($rate['pickup_date'])) {
                $valuation->pickupDate = new \DateTimeImmutable($rate['pickup_date']);
            }
            if (!empty($rate['delivery_date'])) {
                $valuation->deliveryDate = new \DateTimeImmutable($rate['delivery_date']);
            }

            $orderValuationResponse->valuations[] = $valuation;
        }

        return $orderValuationResponse;
    }
}
