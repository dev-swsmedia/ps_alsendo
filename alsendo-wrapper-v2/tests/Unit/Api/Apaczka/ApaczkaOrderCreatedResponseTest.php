<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Unit\Api\Apaczka;

use Alsendo\AlsendoWrapper\Api\Apaczka\Wrapper\SendOrderResponseWrapper;
use Alsendo\AlsendoWrapper\Model\Order\SendOrderResponse;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApaczkaOrderCreatedResponseTest extends TestCase
{
    /**
     * Test if SendOrderResponse can be created from JSON response.
     *
     * @return void
     *
     * @throws \JsonException
     */
    public function testCreateOrderResponseFromJson()
    {
        $json = '{
  "order": {
    "id": 462544297,
    "service_id": 41,
    "service_name": "InPost Paczkomat",
    "waybill_number": "602797138500704122133602",
    "pickup_number": "",
    "tracking_url": "https://www.apaczka.pl/sledz-przesylke/?waybill=602797138500704122133602",
    "status": "NEW",
    "shipments_count": 1,
    "content": "test Apaczka Wrapper",
    "comment": "test Apaczka Wrapper",
    "receiver": {
      "name": "Test Rumunia",
      "contact_person": "Aleksander AAAA",
      "email": "test@ecolet-testowy.pl",
      "phone": "+48573000000",
      "line1": "Mare test",
      "line2": 33,
      "postal_code": "20-002",
      "city": "Warszawa",
      "country_code": "PL",
      "foreign_address_id": "WAW684M"
    },
    "created": "2025-06-18 12:07:34",
    "delivered": ""
  }
}';
        $jsonDecoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $response = SendOrderResponseWrapper::wrap($jsonDecoded['order']);
        $this->assertInstanceOf(SendOrderResponse::class, $response);
        $this->assertEquals('462544297', $response->id);
        $this->assertEquals('NEW', $response->status);
        $this->assertEquals('41', $response->serviceId);
        $this->assertEquals('InPost Paczkomat', $response->serviceName);
        $this->assertEquals('602797138500704122133602', $response->waybillNumber);
        $this->assertNotNull($response->trackingUrl);
        $this->assertNotNull($response->created);
    }
}
