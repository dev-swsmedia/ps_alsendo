<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Geocoding\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

class GeocodingResult
{
    public float $lat;
    public float $lon;
    public string $displayName;

    public function __construct(float $lat, float $lon, string $displayName = '')
    {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->displayName = $displayName;
    }
}
