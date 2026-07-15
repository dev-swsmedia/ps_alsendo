<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Ecolet\Model\Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Json;
use Alsendo\AlsendoWrapper\Model\Cod;
use Alsendo\AlsendoWrapper\Model\Contact;
use Alsendo\AlsendoWrapper\Model\Order\OrderResponse;
use Alsendo\AlsendoWrapper\Model\Pickup;
use Alsendo\AlsendoWrapper\Model\Shipment;

class EcoletOrderResponse extends OrderResponse
{
    public Service $service;
    public string $shipmentType;
    public ?string $primaryOrderAwb;
    public string $awb;
    public string $waybillExtension;
    public bool $waybillHasBeenDownloaded = false;
    public int $vat;
    public array $fees = [];
    public \DateTimeImmutable $updatedAt;
    public \DateTimeImmutable $createdAt;
    public array $statuses = [];

    public static function getPropertyTypeMap(): array
    {
        return [
            'id' => ['mappedTo' => 'id'],
            'service' => ['mappedTo' => 'service', 'type' => Service::class, 'mapFields' => [
                'slug' => ['mappedTo' => 'slug'],
                'full_name' => ['mappedTo' => 'fullName'],
                'courier_slug' => ['mappedTo' => 'courierSlug'],
                'courier_name' => ['mappedTo' => 'courierName'],
            ]],
            'service_id' => ['mappedTo' => 'serviceId'],
            'shipment_type' => ['mappedTo' => 'shipmentType'],
            'primary_order_awb' => ['mappedTo' => 'primaryOrderAwb'],
            'sender' => ['mappedTo' => 'sender', 'type' => Contact::class, 'mapFields' => [
                'id' => ['mappedTo' => 'id'],
                'name' => ['mappedTo' => 'name'],
                'locality_id' => ['mappedTo' => 'localityId'],
                'country' => ['mappedTo' => 'countryCode'],
                'county' => ['mappedTo' => 'stateCode'],
                'locality' => ['mappedTo' => 'city'],
                'postal_code' => ['mappedTo' => 'postalCode'],
                'has_street' => ['mappedTo' => 'hasStreet'],
                'street_name' => ['mappedTo' => 'streetName'],
                'street_number' => ['mappedTo' => 'streetNumber'],
                'block' => ['mappedTo' => 'block'],
                'entrance' => ['mappedTo' => 'entrance'],
                'floor' => ['mappedTo' => 'floor'],
                'flat' => ['mappedTo' => 'flat'],
                'contact_person' => ['mappedTo' => 'contactPerson'],
                'email' => ['mappedTo' => 'email'],
                'phone' => ['mappedTo' => 'phone'],
                'map_point_id' => ['mappedTo' => 'mapPointId'],
                'map_point_name' => ['mappedTo' => 'mapPointName'],
                'updated_at' => ['mappedTo' => 'updatedAt', 'convert' => Json::DATE_TIME],
                'created_at' => ['mappedTo' => 'createdAt', 'convert' => Json::DATE_TIME],
            ]],
            'receiver' => ['mappedTo' => 'receiver', 'type' => Contact::class, 'mapFields' => [
                'id' => ['mappedTo' => 'id'],
                'name' => ['mappedTo' => 'name'],
                'locality_id' => ['mappedTo' => 'localityId'],
                'country' => ['mappedTo' => 'countryCode'],
                'county' => ['mappedTo' => 'stateCode'],
                'locality' => ['mappedTo' => 'city'],
                'postal_code' => ['mappedTo' => 'postalCode'],
                'has_street' => ['mappedTo' => 'hasStreet'],
                'street_name' => ['mappedTo' => 'streetName'],
                'street_number' => ['mappedTo' => 'streetNumber'],
                'block' => ['mappedTo' => 'block'],
                'entrance' => ['mappedTo' => 'entrance'],
                'floor' => ['mappedTo' => 'floor'],
                'flat' => ['mappedTo' => 'flat'],
                'contact_person' => ['mappedTo' => 'contactPerson'],
                'email' => ['mappedTo' => 'email'],
                'phone' => ['mappedTo' => 'phone'],
                'map_point_id' => ['mappedTo' => 'mapPointId'],
                'map_point_name' => ['mappedTo' => 'mapPointName'],
                'updated_at' => ['mappedTo' => 'updatedAt', 'convert' => Json::DATE_TIME],
                'created_at' => ['mappedTo' => 'createdAt', 'convert' => Json::DATE_TIME],
            ]],
            'awb' => ['mappedTo' => 'awb'],
            'waybill_extension' => ['mappedTo' => 'waybillExtension'],
            'waybill_has_been_downloaded' => ['mappedTo' => 'waybillHasBeenDownloaded'],
            'status' => ['mappedTo' => 'status'],
            'type' => ['mappedTo' => 'shipments.0.shipmentTypeCode', 'type' => Shipment::class],
            'amount' => ['mappedTo' => 'shipments.0.amount', 'type' => Shipment::class, 'convert' => Json::INTEGER],
            'weight' => ['mappedTo' => 'shipments.0.weight', 'type' => Shipment::class],
            'length' => ['mappedTo' => 'shipments.0.dimension1', 'type' => Shipment::class],
            'width' => ['mappedTo' => 'shipments.0.dimension2', 'type' => Shipment::class],
            'height' => ['mappedTo' => 'shipments.0.dimension3', 'type' => Shipment::class],
            'shape' => ['mappedTo' => 'shipments.0.shape', 'type' => Shipment::class],
            'content' => ['mappedTo' => 'shipments.0.content', 'type' => Shipment::class],
            'observations' => ['mappedTo' => 'shipments.0.observations', 'type' => Shipment::class],
            'declared_value' => ['mappedTo' => 'declarationValue'],
            'cod' => ['mappedTo' => 'cod', 'type' => Cod::class, 'mapFields' => 'amount'],
            'cod_received_at' => ['mappedTo' => 'cod', 'type' => Cod::class, 'convert' => Json::DATE_TIME, 'mapFields' => 'receivedAt'],
            'cod_returned_at' => ['mappedTo' => 'cod', 'type' => Cod::class, 'convert' => Json::DATE_TIME, 'mapFields' => 'returnedAt'],
            'pickup_type' => ['mappedTo' => 'pickup', 'type' => Pickup::class, 'mapFields' => 'type'],
            'pickup_date' => ['mappedTo' => 'pickup', 'type' => Pickup::class, 'convert' => Json::DATE_TIME, 'mapFields' => 'date'],
            'pickup_hour' => ['mappedTo' => 'pickup', 'type' => Pickup::class, 'mapFields' => 'hoursFrom'],
            'fees' => ['mappedTo' => 'fees', 'type' => FeeItem::class, 'mapFields' => [
                'type' => 'type',
                'value' => 'value',
            ]],
            'shipments' => ['mappedTo' => 'shipments', 'type' => Shipment::class, 'mapFields' => [
                'weight' => 'weight',
                'dimension.length' => 'dimension1',
                'dimension.width' => 'dimension2',
                'dimension.height' => 'dimension3',
                'declared_value' => 'declarationValue',
                'content' => 'content',
            ]],
            'vat' => ['mappedTo' => 'vat'],
            'statuses' => ['mappedTo' => 'statuses', 'type' => StatusItem::class],
            'updated_at' => ['mappedTo' => 'updatedAt', 'convert' => Json::DATE_TIME],
            'created_at' => ['mappedTo' => 'createdAt', 'convert' => Json::DATE_TIME],
        ];
    }
}
