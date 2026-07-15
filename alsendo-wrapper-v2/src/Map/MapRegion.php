<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

declare(strict_types=1);

namespace Alsendo\AlsendoWrapper\Map;

if (!defined('_PS_VERSION_')) {
    exit;
}
class MapRegion
{
    public const PL = 'pl';
    public const CZ = 'cz';
    public const RO = 'ro';

    public const ALL = [self::PL, self::CZ, self::RO];

    public const DEFAULT_OPERATORS = [
        'pl' => ['INPOST', 'DHL', 'POCZTA', 'DPD', 'UPS', 'GLS', 'RUCH'],
        'cz' => ['PPL', 'PACKETA', 'BALIKOVNA', 'ONE_DELIVERY', 'GLS', 'DPD'],
        'ro' => ['DPD', 'SAMEDAY', 'FAN_COURIER', 'CARGUS', 'GLS'],
    ];

    public const COUNTRY_CODES = [
        'pl' => 'PL',
        'cz' => 'CZ',
        'ro' => 'RO',
    ];

    public const EU_COUNTRY_CODES = [
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT',
        'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE',
    ];

    public const LANGUAGES = [
        'pl' => 'pl',
        'cz' => 'cz',
        'ro' => 'ro',
    ];
}
