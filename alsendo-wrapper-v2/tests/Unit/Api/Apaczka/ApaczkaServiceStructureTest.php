<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Unit\Api\Apaczka;

use Alsendo\AlsendoWrapper\Api\Apaczka\Wrapper\ServiceStructureWrapper;
use Alsendo\AlsendoWrapper\Model\Service\Service;
use Alsendo\AlsendoWrapper\Model\Service\ServiceStructure;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApaczkaServiceStructureTest extends TestCase
{
    public function testServiceStructureFromJson()
    {
        $json = '{"services": [
      {
        "service_id": "21",
        "name": "DPD Kurier",
        "delivery_time": "",
        "supplier": "DPD",
        "domestic": "1",
        "pickup_courier": "1",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "1"
      },
      {
        "service_id": "22",
        "name": "DPD Kurier Europa",
        "delivery_time": "",
        "supplier": "DPD",
        "domestic": "0",
        "pickup_courier": "1",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "0"
      },
      {
        "service_id": "23",
        "name": "DPD Pickup Drzwi-Punkt",
        "delivery_time": "",
        "supplier": "DPD",
        "domestic": "1",
        "pickup_courier": "1",
        "door_to_door": "0",
        "door_to_point": "1",
        "point_to_point": "0",
        "point_to_door": "0"
      },
      {
        "service_id": "24",
        "name": "DPD Kurier do 9:30",
        "delivery_time": "do 9:30",
        "supplier": "DPD",
        "domestic": "1",
        "pickup_courier": "1",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "1"
      },
      {
        "service_id": "25",
        "name": "DPD Kurier do 12:00",
        "delivery_time": "do 12:00",
        "supplier": "DPD",
        "domestic": "1",
        "pickup_courier": "1",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "1"
      },
      {
        "service_id": "26",
        "name": "DPD Pickup Punkt-Punkt",
        "delivery_time": "",
        "supplier": "DPD",
        "domestic": "1",
        "pickup_courier": "0",
        "door_to_door": "0",
        "door_to_point": "0",
        "point_to_point": "1",
        "point_to_door": "0"
      },
      {
        "service_id": "27",
        "name": "DPD Max",
        "delivery_time": "",
        "supplier": "DPD",
        "domestic": "0",
        "pickup_courier": "2",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "0"
      },
      {
        "service_id": "28",
        "name": "Allegro SMART DPD Kurier",
        "delivery_time": "",
        "supplier": "DPD",
        "domestic": "1",
        "pickup_courier": "1",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "1"
      },
      {
        "service_id": "29",
        "name": "DPD Pickup Europa",
        "delivery_time": "",
        "supplier": "DPD",
        "domestic": "0",
        "pickup_courier": "1",
        "door_to_door": "0",
        "door_to_point": "1",
        "point_to_point": "1",
        "point_to_door": "0"
      },
      {
        "service_id": "40",
        "name": "Allegro SMART Paczkomat InPost",
        "delivery_time": "",
        "supplier": "INPOST",
        "domestic": "1",
        "pickup_courier": "0",
        "door_to_door": "0",
        "door_to_point": "0",
        "point_to_point": "1",
        "point_to_door": "0"
      },
      {
        "service_id": "41",
        "name": "InPost Paczkomat",
        "delivery_time": "",
        "supplier": "INPOST",
        "domestic": "1",
        "pickup_courier": "0",
        "door_to_door": "0",
        "door_to_point": "1",
        "point_to_point": "1",
        "point_to_door": "0"
      },
      {
        "service_id": "43",
        "name": "InPost Paczkomat-Drzwi",
        "delivery_time": "",
        "supplier": "INPOST",
        "domestic": "1",
        "pickup_courier": "0",
        "door_to_door": "0",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "1"
      },
      {
        "service_id": "60",
        "name": "Pocztex Kurier Drzwi-Drzwi",
        "delivery_time": "",
        "supplier": "POCZTA",
        "domestic": "1",
        "pickup_courier": "2",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "0"
      },
      {
        "service_id": "64",
        "name": "Pocztex Kurier Drzwi-Punkt",
        "delivery_time": "",
        "supplier": "POCZTA",
        "domestic": "1",
        "pickup_courier": "2",
        "door_to_door": "0",
        "door_to_point": "1",
        "point_to_point": "0",
        "point_to_door": "0"
      },
      {
        "service_id": "65",
        "name": "Pocztex Punkt Punkt-Drzwi",
        "delivery_time": "",
        "supplier": "POCZTA",
        "domestic": "1",
        "pickup_courier": "0",
        "door_to_door": "0",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "1"
      },
      {
        "service_id": "66",
        "name": "Pocztex Punkt Punkt-Punkt",
        "delivery_time": "",
        "supplier": "POCZTA",
        "domestic": "1",
        "pickup_courier": "0",
        "door_to_door": "0",
        "door_to_point": "0",
        "point_to_point": "1",
        "point_to_door": "0"
      },
      {
        "service_id": "67",
        "name": "Allegro SMART Pocztex",
        "delivery_time": "",
        "supplier": "POCZTA",
        "domestic": "1",
        "pickup_courier": "0",
        "door_to_door": "0",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "1"
      },
      {
        "service_id": "68",
        "name": "Allegro SMART Pocztex Punkty",
        "delivery_time": "",
        "supplier": "POCZTA",
        "domestic": "1",
        "pickup_courier": "0",
        "door_to_door": "0",
        "door_to_point": "0",
        "point_to_point": "1",
        "point_to_door": "0"
      },
      {
        "service_id": "69",
        "name": "Poczta Polska Palety",
        "delivery_time": "",
        "supplier": "POCZTA",
        "domestic": "1",
        "pickup_courier": "2",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "0"
      },
      {
        "service_id": "71",
        "name": "Poczta Polska Ukraina",
        "delivery_time": "",
        "supplier": "POCZTA",
        "domestic": "0",
        "pickup_courier": "2",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "0"
      },
      {
        "service_id": "82",
        "name": "DHL Parcel Kurier",
        "delivery_time": "",
        "supplier": "DHL",
        "domestic": "1",
        "pickup_courier": "1",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "0"
      },
      {
        "service_id": "83",
        "name": "DHL Parcel Kurier do 12:00",
        "delivery_time": "do 12:00",
        "supplier": "DHL",
        "domestic": "1",
        "pickup_courier": "1",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "0"
      },
      {
        "service_id": "84",
        "name": "DHL Parcel Kurier do 9:00",
        "delivery_time": "do 9:00",
        "supplier": "DHL",
        "domestic": "1",
        "pickup_courier": "1",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "0"
      },
      {
        "service_id": "86",
        "name": "DHL POP do punktu",
        "delivery_time": "",
        "supplier": "DHL_PARCEL",
        "domestic": "1",
        "pickup_courier": "1",
        "door_to_door": "0",
        "door_to_point": "1",
        "point_to_point": "1",
        "point_to_door": "0"
      },
      {
        "service_id": "87",
        "name": "DHL POP Punkt-Drzwi",
        "delivery_time": "",
        "supplier": "DHL",
        "domestic": "1",
        "pickup_courier": "0",
        "door_to_door": "0",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "1"
      },
      {
        "service_id": "191",
        "name": "Apaczka Niemcy",
        "delivery_time": "",
        "supplier": "DPD",
        "domestic": "0",
        "pickup_courier": "1",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "0"
      },
      {
        "service_id": "230",
        "name": "Hellmann",
        "delivery_time": "",
        "supplier": "HELLMANN",
        "domestic": "1",
        "pickup_courier": "2",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "0"
      },
      {
        "service_id": "231",
        "name": "Hellmann",
        "delivery_time": "",
        "supplier": "HELLMANN",
        "domestic": "0",
        "pickup_courier": "2",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "0"
      },
      {
        "service_id": "240",
        "name": "Rhenus Logistics",
        "delivery_time": "",
        "supplier": "RHENUS",
        "domestic": "1",
        "pickup_courier": "2",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "0"
      },
      {
        "service_id": "250",
        "name": "Geodis",
        "delivery_time": "",
        "supplier": "PEKAES",
        "domestic": "1",
        "pickup_courier": "2",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "0"
      },
      {
        "service_id": "260",
        "name": "Ambro Express",
        "delivery_time": "",
        "supplier": "AMBRO",
        "domestic": "1",
        "pickup_courier": "1",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "0"
      },
      {
        "service_id": "261",
        "name": "Ambro Express Zagranica",
        "delivery_time": "",
        "supplier": "AMBRO",
        "domestic": "0",
        "pickup_courier": "1",
        "door_to_door": "1",
        "door_to_point": "0",
        "point_to_point": "0",
        "point_to_door": "0"
      }
    ],
    "options": {
      "11": {
        "type": "bool",
        "name": "Zwrot dokumentów",
        "desc": "Zwrot dokumentów"
      },
      "19": {
        "type": "bool",
        "name": "Dostawa w sobotę",
        "desc": "Dla wybranych przewoźników"
      },
      "31": {
        "type": "bool",
        "name": "Powiadomienie SMS",
        "desc": ""
      },
      "44": {
        "type": "bool",
        "name": "Doręczenie do rąk własnych",
        "desc": "Doręczenie do rąk własnych aka Bezpośrednie doręczenie"
      },
      "58": {
        "type": "bool",
        "name": "Ostrożnie",
        "desc": "Usługa Poczty Polskiej"
      },
      "82": {
        "type": "bool",
        "name": "Opony",
        "desc": ""
      },
      "85": {
        "type": "bool",
        "name": "Podjazd z listem",
        "desc": "Kurier przywiezie list przewozowy dla twojej przesyłki."
      },
      "88": {
        "type": "bool",
        "name": "Preawizacja",
        "desc": "Informacja telefoniczna przed doręczeniem przesyłki."
      },
      "91": {
        "type": "bool",
        "name": "Nadanie na umowie własnej",
        "desc": ""
      },
      "92": {
        "type": "bool",
        "name": "Nadanie bez etykiety",
        "desc": ""
      },
      "128": {
        "type": "bool",
        "name": "Przedmioty kruche",
        "desc": ""
      },
      "129": {
        "type": "bool",
        "name": "Płyny lub gazy",
        "desc": ""
      },
      "130": {
        "type": "bool",
        "name": "Żywe owady",
        "desc": ""
      },
      "131": {
        "type": "bool",
        "name": "Żywe ptaki",
        "desc": ""
      },
      "132": {
        "type": "bool",
        "name": "Żywe rośliny",
        "desc": ""
      },
      "134": {
        "type": "bool",
        "name": "Sprawdzenie zawartości",
        "desc": "Sprawdzenie zawartości przy odbiorze"
      }
    },
    "package_type": {
      "LIST": {
        "type": "LIST",
        "desc": "Przesyłka zawierająca dokumenty w wersji papierowej, zapakowane w kopertę kurierską."
      },
      "PACZKA": {
        "type": "PACZKA",
        "desc": "Przesyłka zapakowana w prostopadłościenny karton o regularnych kształtach, bez wystających elementów lub w foliopak kurierski."
      },
      "PALETA": {
        "type": "PALETA",
        "desc": "Towar na palecie Euro o podstawie 120x80. Bez elementów wystających poza obrys palety."
      },
      "PALETA_60X40": {
        "type": "PALETA_60X40",
        "desc": "Towar na palecie o podstawie 60x40. Bez elementów wystających poza obrys palety."
      },
      "POLPALETA": {
        "type": "POLPALETA",
        "desc": "Towar na palecie o podstawie 60x80. Bez elementów wystających poza obrys palety."
      },
      "PALETA_PRZEMYSLOWA": {
        "type": "PALETA_PRZEMYSLOWA",
        "desc": "Towar na palecie o podstawie 120x100. Bez elementów wystających poza obrys palety."
      },
      "PALETA_PRZEMYSLOWA_B": {
        "type": "PALETA_PRZEMYSLOWA_B",
        "desc": "Towar na palecie o podstawie 120x120. Bez elementów wystających poza obrys palety."
      }
    },
    "points_type": [
      "UPS",
      "INPOST",
      "POCZTA",
      "DHL_PARCEL",
      "DPD",
      "PWR",
      "CBL",
      "PACKETA"
    ],
    "pickup_type": {
      "COURIER": {
        "type": "COURIER",
        "desc": "Podjazd kuriera."
      },
      "SELF": {
        "type": "SELF",
        "desc": "Dostarczę przesyłkę samodzielnie do punktu nadania."
      }
    },
    "unit_type": {
      "PCS": {
        "type": "PCS",
        "desc": "Jednostka w odprawie celnej określająca sztukę."
      },
      "PKG": {
        "type": "PKG",
        "desc": "Jednostka w odprawie celnej określająca opakowanie/paczkę."
      }
    }
    }';
        $data = json_decode($json, true);
        $serviceStructure = ServiceStructureWrapper::wrap($data);
        $this->assertInstanceOf(ServiceStructure::class, $serviceStructure);
        $this->assertNotEmpty($serviceStructure->services);
        $this->assertInstanceOf(Service::class, $serviceStructure->services[0]);
        $this->assertSame('21', $serviceStructure->services[0]->externalId);
        $this->assertSame('DPD Kurier', $serviceStructure->services[0]->name);
        $this->assertSame('DPD', $serviceStructure->services[0]->supplier);
    }
}
