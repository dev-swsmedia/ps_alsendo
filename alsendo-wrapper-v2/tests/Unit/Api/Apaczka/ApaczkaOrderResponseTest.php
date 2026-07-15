<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Unit\Api\Apaczka;

use Alsendo\AlsendoWrapper\Api\Apaczka\Model\Order\ApaczkaOrderResponse;
use Alsendo\AlsendoWrapper\Json;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApaczkaOrderResponseTest extends TestCase
{
    /**
     * Test creating ApaczkaOrderResponse from JSON
     *
     * @covers  \Alsendo\AlsendoWrapper\Api\Apaczka\Model\Order\ApaczkaOrderResponse
     * @covers  \Alsendo\AlsendoWrapper\Json
     *
     * @return void
     *
     * @throws \JsonException
     */
    public function testCreateOrderResponseFromJson()
    {
        $json = '{
  "id": "",
  "supplier": "",
  "service_id": "",
  "service_name": "",
  "waybill_number": "",
  "pickup": {
    "type": "",
    "date": "",
    "hours_from": "",
    "hours_to": ""
  },
  "pickup_number": "",
  "tracking_url": "",
  "status": "",
  "shipments_count": 1,
  "shipments": [
    {
      "shipment_type_code": "",
      "weight": "",
      "weight_billable": "",
      "length": "",
      "width": "",
      "height": "",
      "content": "",
      "comment": "",
      "waybill_number": "",
      "is_nstd": false,
      "price": "",
      "price_vat": "",
      "price_gross": ""
    }
  ],
  "content": "",
  "comment": "",
  "sender": {
    "name": "",
    "contact_person": "",
    "email": "",
    "phone": "",
    "line1": "",
    "line2": "",
    "postal_code": "",
    "city": "",
    "country_code": "",
    "foreign_address_id": ""
  },
  "receiver": {
    "name": "",
    "contact_person": "",
    "email": "",
    "phone": "",
    "line1": "",
    "line2": "",
    "postal_code": "",
    "city": "",
    "country_code": "",
    "foreign_address_id": ""
  },
  "created": "",
  "delivered": "",
  "price": "",
  "price_var": "",
  "price_gross": "",
  "cod": false,
  "declaration_value": false
}';

        $apaczkaOrderResponse = Json::mapJsonToObject($json, ApaczkaOrderResponse::class);
        $this->assertInstanceOf(ApaczkaOrderResponse::class, $apaczkaOrderResponse);
    }
}
