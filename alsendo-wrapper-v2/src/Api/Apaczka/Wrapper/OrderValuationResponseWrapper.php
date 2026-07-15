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

use Alsendo\AlsendoWrapper\Model\Order\Valuation\OrderValuation;
use Alsendo\AlsendoWrapper\Model\Order\Valuation\OrderValuationResponse;
use Alsendo\AlsendoWrapper\Model\Order\Valuation\PriceTableItem;

class OrderValuationResponseWrapper
{
    public static function wrap(array $response): OrderValuationResponse
    {
        $orderValuationResponse = new OrderValuationResponse();

        foreach ($response['price_table'] as $serviceId => $priceData) {
            $valuation = new OrderValuation();
            $valuation->serviceId = (string) $serviceId;
            $priceTable = new PriceTableItem();
            $priceTable->price = (string) $priceData['price'];
            $priceTable->priceGross = (string) $priceData['price_gross'];
            $priceTable->currency = 'PLN';
            $valuation->priceTable = $priceTable;
            $orderValuationResponse->valuations[] = $valuation;
        }

        return $orderValuationResponse;
    }
}
