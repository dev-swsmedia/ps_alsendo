<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Unit\Api\Apaczka;

use Alsendo\AlsendoWrapper\Api\Apaczka\Wrapper\OrderValuationResponseWrapper;
use Alsendo\AlsendoWrapper\Model\Order\Valuation\OrderValuation;
use Alsendo\AlsendoWrapper\Model\Order\Valuation\OrderValuationResponse;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApaczkaOrderValuationTest extends TestCase
{
    /**
     * Tests if the order valuation is correctly parsed from JSON.
     *
     * @covers \Alsendo\AlsendoWrapper\Api\Apaczka\ApiApaczkaClient::getOrderValuation
     */
    public function testOrderValuationFromJson()
    {
        $json = '{
    "price_table": {
      "82": {
        "price": 1868,
        "price_gross": 2298
      }
    }
  }';
        $decodedJson = json_decode($json, true);
        $result = OrderValuationResponseWrapper::wrap($decodedJson);
        $this->assertInstanceOf(OrderValuationResponse::class, $result);
        $this->assertCount(1, $result->valuations);
        $this->assertInstanceOf(OrderValuation::class, $result->valuations[0]);
        $this->assertSame('82', $result->valuations[0]->serviceId);
        $this->assertSame('PLN', $result->valuations[0]->priceTable->currency);
        $this->assertSame('1868', $result->valuations[0]->priceTable->price);
        $this->assertSame('2298', $result->valuations[0]->priceTable->priceGross);
    }
}
