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
/**
 * Znormalizowana struktura punktu odbioru zwracana przez callback widgetu.
 * W JS callback otrzymujesz obiekt z tymi polami.
 */
class PickupPoint
{
    public string $code;
    public string $operator;
    public string $name;
    public string $street;
    public string $postalCode;
    public string $city;
    public ?string $province = null;
    public ?string $latitude = null;
    public ?string $longitude = null;
    public bool $hasCod = false;
    public ?array $raw = null;
}
