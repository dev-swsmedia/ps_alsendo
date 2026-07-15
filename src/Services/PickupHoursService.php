<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\Services;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PickupHoursService
{
    public static function getDefaultPickupHours(): array
    {
        return [
            'from' => \Configuration::get('ALSENDO_DEFAULT_PICKUP_HOURS_FROM', null, null, null, '08:00') ?: '08:00',
            'to' => \Configuration::get('ALSENDO_DEFAULT_PICKUP_HOURS_TO', null, null, null, '17:00') ?: '17:00',
        ];
    }

    public static function getDefaultPickupDate(): string
    {
        if ((int) \Configuration::get('ALSENDO_SAME_DAY_PICKUP')) {
            return date('Y-m-d');
        }

        return date('Y-m-d', strtotime('+1 day'));
    }

    public static function isDateInPast(string $date): bool
    {
        return strtotime($date) < strtotime(date('Y-m-d'));
    }

    public static function isDateTimeInPast(string $date, string $time): bool
    {
        $dateTime = strtotime($date . ' ' . $time);

        return $dateTime < time();
    }
}
