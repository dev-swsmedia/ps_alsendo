<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Zaslat;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ValidationRules
{
    public const ORDER_CREATE = [
        'address' => 'required',
        'address.sender' => 'required',
        'address.receiver' => 'required',
        'address.receiver.phone' => 'required',
        'shipment' => 'required|array',
        'pickup' => 'required',
    ];
}
