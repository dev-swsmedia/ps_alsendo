<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Unit\Api\Ecolet;

use Alsendo\AlsendoWrapper\Api\Ecolet\Wrapper\OrderValuationResponseWrapper;
use Alsendo\AlsendoWrapper\Model\Order\Valuation\OrderValuation;
use Alsendo\AlsendoWrapper\Model\Order\Valuation\OrderValuationResponse;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EcoletOrderValuationResponseTest extends TestCase
{
    public function testCreateOrderValuationResponseFromJson()
    {
        $json = '{
  "form" : {
    "statuses" : {
      "fan_marfa" : true,
      "dpd_standard" : true,
      "urgent_cargus_standard" : true,
      "fan_courier_standard" : true,
      "boo_kurier_bucuresti_24" : true,
      "sameday_nextday" : true,
      "boo_kurier_metropolitan" : true,
      "gls_standard" : true
    },
    "additional_services" : {
      "fan_marfa" : {
        "cod" : true,
        "rod" : false,
        "rop" : false,
        "open_package" : false,
        "saturday_delivery" : true,
        "sms_notify" : false,
        "swap" : false,
        "epod" : false
      },
      "dpd_standard" : {
        "cod" : true,
        "rod" : true,
        "rop" : false,
        "open_package" : true,
        "saturday_delivery" : false,
        "sms_notify" : false,
        "swap" : true,
        "epod" : false
      },
      "urgent_cargus_standard" : {
        "cod" : true,
        "rod" : true,
        "rop" : false,
        "open_package" : true,
        "saturday_delivery" : false,
        "sms_notify" : false,
        "swap" : true,
        "epod" : false
      },
      "fan_courier_standard" : {
        "cod" : true,
        "rod" : false,
        "rop" : false,
        "open_package" : true,
        "saturday_delivery" : true,
        "sms_notify" : false,
        "swap" : false,
        "epod" : false
      },
      "boo_kurier_bucuresti_24" : {
        "cod" : true,
        "rod" : true,
        "rop" : false,
        "open_package" : false,
        "saturday_delivery" : false,
        "sms_notify" : false,
        "swap" : false,
        "epod" : false
      },
      "sameday_nextday" : {
        "cod" : true,
        "rod" : true,
        "rop" : false,
        "open_package" : true,
        "saturday_delivery" : false,
        "sms_notify" : false,
        "swap" : true,
        "epod" : false
      },
      "boo_kurier_metropolitan" : {
        "cod" : true,
        "rod" : true,
        "rop" : false,
        "open_package" : false,
        "saturday_delivery" : false,
        "sms_notify" : false,
        "swap" : false,
        "epod" : false
      },
      "gls_standard" : {
        "cod" : true,
        "rod" : true,
        "rop" : false,
        "open_package" : false,
        "saturday_delivery" : false,
        "sms_notify" : false,
        "swap" : false,
        "epod" : false
      }
    },
    "conditions" : {
      "fan_marfa" : {
        "pickup_types" : [ "self", "courier" ]
      },
      "dpd_standard" : {
        "pickup_types" : [ "self", "courier" ]
      },
      "urgent_cargus_standard" : {
        "pickup_types" : [ "courier" ]
      },
      "fan_courier_standard" : {
        "pickup_types" : [ "self", "courier" ]
      },
      "boo_kurier_bucuresti_24" : {
        "pickup_types" : [ "courier" ]
      },
      "sameday_nextday" : {
        "pickup_types" : [ "self", "courier" ]
      },
      "boo_kurier_metropolitan" : {
        "pickup_types" : [ "courier" ]
      },
      "gls_standard" : {
        "pickup_types" : [ "courier" ]
      }
    },
    "pickup_dates" : {
      "fan_marfa" : [ {
        "day" : "Monday",
        "date" : "2025-04-07",
        "hours" : [ "12:00", "13:00", "14:00", "15:00" ]
      }, {
        "day" : "Tuesday",
        "date" : "2025-04-08",
        "hours" : [ "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00" ]
      } ],
      "dpd_standard" : [ {
        "day" : "Monday",
        "date" : "2025-04-07",
        "hours" : [ "12:00", "13:00", "14:00", "15:00", "16:00", "17:00" ]
      } ],
      "urgent_cargus_standard" : [ {
        "day" : "Monday",
        "date" : "2025-04-07",
        "hours" : [ "12:00", "13:00", "14:00", "15:00", "16:00", "17:00" ]
      } ],
      "fan_courier_standard" : [ {
        "day" : "Monday",
        "date" : "2025-04-07",
        "hours" : [ "12:00", "13:00", "14:00", "15:00", "16:00" ]
      } ],
      "boo_kurier_bucuresti_24" : [ {
        "day" : "Monday",
        "date" : "2025-04-07",
        "hours" : [ "12:00", "13:00", "14:00", "15:00", "16:00" ]
      } ],
      "sameday_nextday" : [ {
        "day" : "Monday",
        "date" : "2025-04-07",
        "hours" : [ "12:00", "13:00", "14:00", "15:00", "16:00", "17:00" ]
      } ],
      "boo_kurier_metropolitan" : [ {
        "day" : "Monday",
        "date" : "2025-04-07",
        "hours" : [ "12:00", "13:00", "14:00", "15:00", "16:00" ]
      } ],
      "gls_standard" : [ {
        "day" : "Monday",
        "date" : "2025-04-07",
        "hours" : [ "12:00", "13:00", "14:00", "15:00", "16:00", "17:00" ]
      } ]
    },
    "billing_weight" : {
      "fan_marfa" : 10,
      "dpd_standard" : 10
    },
    "is_standard" : {
      "fan_marfa" : true,
      "dpd_standard" : true,
      "urgent_cargus_standard" : true,
      "fan_courier_standard" : true,
      "boo_kurier_bucuresti_24" : true,
      "sameday_nextday" : true,
      "boo_kurier_metropolitan" : true,
      "gls_standard" : true
    },
    "prices_net" : {
      "fan_marfa" : 81.05,
      "dpd_standard" : 24.880000000000003,
      "urgent_cargus_standard" : 29.94,
      "fan_courier_standard" : 37.06,
      "boo_kurier_bucuresti_24" : 68.2,
      "sameday_nextday" : 34.47,
      "boo_kurier_metropolitan" : 69.2,
      "gls_standard" : 23.65
    },
    "prices_gross" : {
      "fan_marfa" : 96.45,
      "dpd_standard" : 29.61,
      "urgent_cargus_standard" : 35.63,
      "fan_courier_standard" : 44.1,
      "boo_kurier_bucuresti_24" : 81.16,
      "sameday_nextday" : 41.02,
      "boo_kurier_metropolitan" : 82.35,
      "gls_standard" : 28.14
    },
    "fees" : {
      "fan_marfa" : { "base" : { "value" : 69.36 } },
      "dpd_standard" : { "base" : { "value" : 14.3 } }
    },
    "payment_amount" : {
      "fan_marfa" : 96.45,
      "dpd_standard" : 29.61
    },
    "vat" : 19,
    "info" : {
      "fan_marfa" : [],
      "dpd_standard" : []
    },
    "errors" : {
      "fan_marfa" : [],
      "dpd_standard" : []
    }
  }
}';
        $decodedJson = json_decode($json, true, 12, JSON_THROW_ON_ERROR);

        $result = OrderValuationResponseWrapper::wrap($decodedJson);

        $this->assertInstanceOf(OrderValuationResponse::class, $result);
        $this->assertCount(8, $result->valuations);
        $this->assertInstanceOf(OrderValuation::class, $result->valuations[0]);
        $this->assertSame('fan_marfa', $result->valuations[0]->serviceId);
        $this->assertSame('RON', $result->valuations[0]->priceTable->currency);
        $this->assertNotNull($result->valuations[0]->pickupDate);
    }
}
