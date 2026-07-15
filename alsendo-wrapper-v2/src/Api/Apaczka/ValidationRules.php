<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Apaczka;

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
        'address.sender.line1' => 'required|string',
        'address.sender.postalCode' => 'required|string',
        'address.sender.city' => 'required|string',
        'address.sender.email' => 'required|email',
        'address.sender.phone' => 'required|string',
        'address.receiver' => 'required',
        'address.receiver.countryCode' => 'required|string',
        'address.receiver.name' => 'required|string',
        'address.receiver.line1' => 'required|string',
        'address.receiver.postalCode' => 'required|string',
        'address.receiver.city' => 'required|string',
        'address.receiver.email' => 'required|email',
        'address.receiver.phone' => 'required|string',
        'shipment' => 'required|array',
        'pickup' => 'required',
        'pickup.type' => 'required|string',
    ];
}
