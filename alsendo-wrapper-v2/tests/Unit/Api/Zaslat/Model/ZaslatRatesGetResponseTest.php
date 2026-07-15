<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Unit\Api\Zaslat;

use Alsendo\AlsendoWrapper\Api\Zaslat\Wrapper\OrderValuationResponseWrapper;
use Alsendo\AlsendoWrapper\Model\Order\Valuation\OrderValuation;
use Alsendo\AlsendoWrapper\Model\Order\Valuation\OrderValuationResponse;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ZaslatRatesGetResponseTest extends TestCase
{
    public function testRatesGetResponse()
    {
        $json = '{
        "status": 200,
        "message": "Found 3 offers",
        "rates": [{
            "carrier": "UPS",
            "service": "Export SK",
            "service_id": 21,
            "pickup_date": "2016-07-10",
            "delivery_date": "2016-07-12",
            "price": {
                "value": 178.51,
                "currency": "CZK"
            },
            "price_vat": {
                "value": 216,
                "currency": "CZK"
            }
        }]
    }';
        $decodedJson = json_decode($json, true, 12, JSON_THROW_ON_ERROR);

        $result = OrderValuationResponseWrapper::wrap($decodedJson);

        $this->assertInstanceOf(OrderValuationResponse::class, $result);
        $this->assertCount(1, $result->valuations);
        $this->assertInstanceOf(OrderValuation::class, $result->valuations[0]);
        $this->assertSame('21', $result->valuations[0]->serviceId);
        $this->assertSame('UPS', $result->valuations[0]->carrier);
        $this->assertSame('CZK', $result->valuations[0]->priceTable->currency);
        $this->assertSame('178.51', $result->valuations[0]->priceTable->price);
        $this->assertSame('216', $result->valuations[0]->priceTable->priceGross);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result->valuations[0]->pickupDate);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result->valuations[0]->deliveryDate);
    }
}
