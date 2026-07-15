<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Zaslat\Model\Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Json;
use Alsendo\AlsendoWrapper\Model\Contact;
use Alsendo\AlsendoWrapper\Model\Order\OrderResponse;
use Alsendo\AlsendoWrapper\Model\Pickup;
use Alsendo\AlsendoWrapper\Model\Shipment;

class ZaslatOrderResponse extends OrderResponse
{
    public static function getPropertyTypeMap(): array
    {
        return [
            'carrier' => ['mappedTo' => 'serviceName'],
            'service_id' => ['mappedTo' => 'serviceId', 'convert' => Json::INTEGER],
            'pickup_date' => ['mappedTo' => 'pickup', 'type' => Pickup::class, 'convert' => Json::DATE_TIME, 'mapFields' => 'date'],
            'delivery_date' => ['mappedTo' => 'delivery', 'convert' => Json::DATE_TIME],
            'status' => ['mappedTo' => 'status'],
            'from' => ['mappedTo' => 'sender', 'type' => Contact::class, 'mapFields' => [
                'firstname' => 'name',
                'surname' => 'name',
                'company' => 'company',
                'street' => 'line1',
                'zip' => 'postalCode',
                'city' => 'city',
                'country' => 'countryCode',
                'phone' => 'phone',
                'email' => 'email',
            ]],
            'to' => ['mappedTo' => 'receiver', 'type' => Contact::class, 'mapFields' => [
                'firstname' => 'name',
                'surname' => 'name',
                'company' => 'company',
                'street' => 'line1',
                'zip' => 'postalCode',
                'city' => 'city',
                'country' => 'countryCode',
                'phone' => 'phone',
                'email' => 'email',
            ]],
            'packages' => ['mappedTo' => 'shipments', 'type' => Shipment::class, 'mapFields' => [
                'weight' => 'weight',
                'width' => 'dimension1',
                'height' => 'dimension2',
                'length' => 'dimension3',
            ]],
        ];
    }
}
