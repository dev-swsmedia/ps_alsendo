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

use Alsendo\AlsendoWrapper\Model\Order\Valuation\OrderValuation;
use Alsendo\AlsendoWrapper\Model\Order\Valuation\OrderValuationResponse;
use Alsendo\AlsendoWrapper\Model\Order\Valuation\PriceTableItem;

class OrderValuationResponseWrapper
{
    public static function wrap(array $response): OrderValuationResponse
    {
        $orderValuationResponse = new OrderValuationResponse();
        $form = $response['form'];
        $slugs = array_keys($form['statuses'] ?? []);

        foreach ($slugs as $slug) {
            if (empty($form['statuses'][$slug])) {
                continue;
            }

            $valuation = new OrderValuation();
            $valuation->serviceId = (string) $slug;

            $priceTable = new PriceTableItem();
            $priceNet = $form['prices_net'][$slug] ?? null;
            $priceGross = $form['prices_gross'][$slug] ?? null;
            $priceTable->price = $priceNet !== null ? (string) $priceNet : null;
            $priceTable->priceGross = $priceGross !== null ? (string) $priceGross : null;
            $priceTable->currency = 'RON';
            $valuation->priceTable = $priceTable;

            $pickupDates = $form['pickup_dates'][$slug] ?? [];
            if (!empty($pickupDates) && isset($pickupDates[0]['date'])) {
                $valuation->pickupDate = new \DateTimeImmutable($pickupDates[0]['date']);
            }

            $orderValuationResponse->valuations[] = $valuation;
        }

        return $orderValuationResponse;
    }
}
