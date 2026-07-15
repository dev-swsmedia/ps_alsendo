<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Unit\Api\Ecolet;

use Alsendo\AlsendoWrapper\Api\Ecolet\Model\Order\EcoletOrderResponse;
use Alsendo\AlsendoWrapper\Json;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EcoletOrderResponseTest extends TestCase
{
    public function testCreateOrderResponseFromJson()
    {
        $json = '{
    "id": 1,
    "service": {
      "slug": "dpd_standard",
      "full_name": "DPD Standard",
      "courier_slug": "dpd",
      "courier_name": "DPD"
    },
    "shipment_type": "primary",
    "primary_order_awb": null,
    "sender": {
      "id": 1,
      "name": "Test Company",
      "locality_id": 323,
      "country": "ro",
      "county": "Bucuresti",
      "locality": "Bucuresti",
      "postal_code": "011318",
      "has_streets": true,
      "street_name": "Bucuresti-Ploiesti",
      "street_number": "172-176",
      "block": "1",
      "entrance": "A2",
      "floor": "1",
      "flat": "A3a",
      "contact_person": "Test Test",
      "email": "test@ecolet.ro",
      "phone": "0214824089",
      "map_point_id": null,
      "map_point_name": null,
      "updated_at": null,
      "created_at": "2025-03-26T11:38:19.617Z"
    },
    "receiver": {
      "id": 1,
      "name": "Test Company",
      "locality_id": 323,
      "country": "ro",
      "county": "Bucuresti",
      "locality": "Bucuresti",
      "postal_code": "011318",
      "has_streets": true,
      "street_name": "Bucuresti-Ploiesti",
      "street_number": "172-176",
      "block": "1",
      "entrance": "A2",
      "floor": "1",
      "flat": "A3a",
      "contact_person": "Test Test",
      "email": "test@ecolet.ro",
      "phone": "0214824089",
      "map_point_id": null,
      "map_point_name": null,
      "updated_at": null,
      "created_at": "2025-03-26T11:38:19.617Z"
    },
    "awb": "80438360579",
    "waybill_extension": "pdf",
    "waybill_has_been_downloaded": false,
    "status": "new",
    "type": "package",
    "amount": 1,
    "price": 16.28,
    "content": "books",
    "shape": "standard",
    "weight": 1,
    "length": 10,
    "width": 15,
    "height": 10,
    "declared_value": null,
    "cod": 500,
    "cod_received_at": null,
    "cod_returned_at": null,
    "observations": null,
    "pickup_date": "2022-01-21",
    "pickup_hour": "13:00",
    "fees": [
      {
        "type": "base",
        "value": "15.50"
      }
    ],
    "vat": 19,
    "statuses": [
      {
        "name": "new",
        "real_name": "new",
        "created_at": "2025-03-26T11:38:19.617Z"
      }
    ],
    "updated_at": "2025-03-26T11:38:19.617Z",
    "created_at": "2025-03-26T11:38:19.617Z"
  }';

        $ecoletOrderResponse = Json::mapJsonToObject($json, EcoletOrderResponse::class);
        $this->assertInstanceOf(EcoletOrderResponse::class, $ecoletOrderResponse);
    }
}
