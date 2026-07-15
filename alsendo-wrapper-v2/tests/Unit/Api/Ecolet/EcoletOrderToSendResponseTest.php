<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Unit\Api\Ecolet;

use Alsendo\AlsendoWrapper\Api\Ecolet\Wrapper\SendOrderResponseWrapper;
use Alsendo\AlsendoWrapper\Model\Order\SendOrderResponse;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EcoletOrderToSendResponseTest extends TestCase
{
    /**
     * @throws \JsonException
     */
    public function testCreateOrderToSendResponseFromJson(): void
    {
        $json = '{
  "order_to_send" : {
  "id": 3080906,
  "status": "ordered",
  "error": "",
  "order_id": 2526443,
  "order": {
    "data": {
      "sender": {
        "name": "Test Ecolet Company",
        "postal_code": 800001,
        "contact_person": "-",
        "email": "testowy-ecolet-test@test-ecolet.pl",
        "phone": "0725942215",
        "country": "ro",
        "locality_id": 4980,
        "street_name": "or. GALATI [800275] str. PODUL INALT Nr. 1",
        "street_number": 1,
        "block": "-",
        "entrance": "-",
        "floor": "-",
        "flat": "-",
        "has_map_point": 1,
        "map_point_id": 15650,
        "county": "Galati",
        "locality": "Galati",
        "company": ""
      },
      "receiver": {
        "name": "Test Rumunia",
        "postal_code": 905900,
        "contact_person": "-",
        "email": "test@ecolet-testowy.pl",
        "phone": "0737659039",
        "country": "ro",
        "locality_id": 8409,
        "street_name": "or. CONSTANTA [900330] str. INTERIOARA intr. 3",
        "street_number": 1,
        "block": "-",
        "entrance": "-",
        "floor": "-",
        "flat": "-",
        "has_map_point": 1,
        "map_point_id": 2303,
        "county": "Constanta",
        "locality": "Ovidiu",
        "company": ""
      },
      "parcel": {
        "type": "package",
        "shape": "standard",
        "amount": "",
        "observations": ""
      },
      "parcels": [
        {
          "weight": 1,
          "dimensions": {
            "length": 9,
            "width": 12,
            "height": 5
          },
          "declared_value": "",
          "content": "books box"
        }
      ],
      "additional_services": {
        "cod": {
          "status": 1,
          "amount": 0
        }
      },
      "courier": {
        "service": "dpd_office_point_to_point",
        "pickup": {
          "type": "self",
          "date": "2025-06-17",
          "time": "15:00"
        }
      }
    },
    "shipment_type": "primary",
    "user_id": 80926,
    "user_bank_account_id": 20807,
    "courier_id": 2,
    "service_id": 46,
    "api_id": 2505,
    "sender_locality": {},
    "receiver_locality": {},
    "sender_map_point": {},
    "receiver_map_point": {},
    "volumetric_weight_divider": 5000,
    "billing_weight": 1,
    "price_net": 11,
    "price_gross": 13,
    "fees": {},
    "payment_amount": 13
  },
  "source": "external_api",
  "source_order_id": "",
  "created_at": "2025-06-17T07:46:16.000000Z",
  "imported_order_created_at": "2025-06-17T07:46:16.000000Z"
}
}';
        $decodedJson = json_decode($json, true, 12, JSON_THROW_ON_ERROR);
        $decodedJson = $decodedJson['order_to_send'];

        $response = SendOrderResponseWrapper::wrap($decodedJson);

        $this->assertInstanceOf(SendOrderResponse::class, $response);
        $this->assertEquals('2526443', $response->id);
        $this->assertEquals('ordered', $response->status);
        $this->assertEquals('46', $response->serviceId);
        $this->assertEquals('dpd_office_point_to_point', $response->serviceName);
        $this->assertNotNull($response->created);
    }
}
