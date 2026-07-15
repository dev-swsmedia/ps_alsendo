<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Apaczka\Model\Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Json;
use Alsendo\AlsendoWrapper\Model\Contact;
use Alsendo\AlsendoWrapper\Model\Order\OrderResponse;
use Alsendo\AlsendoWrapper\Model\Pickup;
use Alsendo\AlsendoWrapper\Model\Shipment;

class ApaczkaOrderResponse extends OrderResponse
{
    public static function getPropertyTypeMap(): array
    {
        return [
            'id' => ['mappedTo' => 'id'],
            'supplier' => ['mappedTo' => 'supplier'],
            'service_id' => ['mappedTo' => 'serviceId'],
            'waybill_number' => ['mappedTo' => 'waybillNumber'],
            'pickup' => ['mappedTo' => 'pickup', 'type' => Pickup::class, 'mapFields' => [
                'type' => 'type',
                'date' => ['mappedTo' => 'date', 'convert' => Json::DATE_TIME],
                'hours_from' => 'hoursFrom',
                'hours_to' => 'hoursTo',
            ]],
            'pickup_number' => ['mappedTo' => 'pickupNumber'],
            'tracking_url' => ['mappedTo' => 'trackingUrl'],
            'status' => ['mappedTo' => 'status'],
            'shipments_count' => ['mappedTo' => 'shipmentsCount'],
            'shipments' => ['mappedTo' => 'shipments', 'type' => Shipment::class, 'mapFields' => [
                'shipment_type_code' => 'shipmentTypeCode',
                'weight' => ['mappedTo' => 'weight', 'convert' => Json::INTEGER],
                'weight_billable' => ['mappedTo' => 'weightBillable', 'convert' => Json::INTEGER],
                'length' => ['mappedTo' => 'dimension1', 'convert' => Json::INTEGER],
                'width' => ['mappedTo' => 'dimension2', 'convert' => Json::INTEGER],
                'height' => ['mappedTo' => 'dimension3', 'convert' => Json::INTEGER],
                'content' => 'content',
                'comment' => 'comment',
                'waybill_number' => 'waybillNumber',
                'is_nstd' => ['mappedTo' => 'isNstd', 'convert' => Json::BOOLEAN],
                'price' => 'price',
                'price_vat' => 'priceVat',
                'price_gross' => 'priceGross',
            ]],
            'content' => ['mappedTo' => 'content'],
            'comment' => ['mappedTo' => 'comment'],
            'sender' => ['mappedTo' => 'sender', 'type' => Contact::class, [
                'name' => 'name',
                'contact_person' => 'contactPerson',
                'email' => 'email',
                'phone' => 'phone',
                'line1' => 'line1',
                'line2' => 'line2',
                'postal_code' => 'postalCode',
                'city' => 'city',
                'country_code' => 'countryCode',
                'foreign_address_id' => 'foreignAddressId',
            ]],
            'receiver' => ['mappedTo' => 'receiver', 'type' => Contact::class, [
                'name' => 'name',
                'contact_person' => 'contactPerson',
                'email' => 'email',
                'phone' => 'phone',
                'line1' => 'line1',
                'line2' => 'line2',
                'postal_code' => 'postalCode',
                'city' => 'city',
                'country_code' => 'countryCode',
                'foreign_address_id' => 'foreignAddressId',
            ]],
            'created' => ['mappedTo' => 'created', 'covert' => Json::DATE_TIME],
            'delivered' => ['mappedTo' => 'delivered', 'covert' => Json::DATE_TIME],
            'price' => ['mappedTo' => 'price'],
            'price_var' => ['mappedTo' => 'priceVar'],
            'price_gross' => ['mappedTo' => 'priceGross'],
            'cod' => ['mappedTo' => 'cod', 'convert' => Json::BOOLEAN],
            'declaration_value' => ['mappedTo' => 'declarationValue'],
        ];
    }
}
