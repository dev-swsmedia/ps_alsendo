<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Ecolet;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ValidationRules
{
    public const ORDER_CREATE = [
        'address' => 'required',
        'address.sender' => 'required',
        'address.sender.countryCode' => 'required|string',
        'address.sender.name' => 'required|string',
        'address.sender.city' => 'required|string',
        'address.sender.phone' => 'required|string',
        'address.receiver' => 'required',
        'address.receiver.countryCode' => 'required|string',
        'address.receiver.name' => 'required|string',
        'address.receiver.city' => 'required|string',
        'address.receiver.phone' => 'required|string',
        'shipment' => 'required|array',
        'pickup' => 'required',
        'pickup.type' => 'required|string',
        // 'pickup.date' => 'required',
    ];
    public const LOCATION_QUERY = [
        'countryCode' => 'required|string',
        'city' => 'required|string|min_length:3',
    ];
}
