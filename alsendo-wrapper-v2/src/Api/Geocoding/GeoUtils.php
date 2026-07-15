<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Geocoding;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Api\Points\Model\Point;

class GeoUtils
{
    private const EARTH_RADIUS_KM = 6371.0;

    /**
     * Haversine formula - distance between two points in km.
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_KM * $c;
    }

    /**
     * Sort Point[] by distance from given coordinates.
     * Sets the `distance` field on each point.
     *
     * @param Point[] $points
     *
     * @return Point[]
     */
    public static function sortByDistance(array $points, float $lat, float $lon): array
    {
        foreach ($points as $point) {
            if ($point->latitude !== null && $point->longitude !== null) {
                $point->distance = self::calculateDistance($lat, $lon, $point->latitude, $point->longitude);
            } else {
                $point->distance = PHP_FLOAT_MAX;
            }
        }

        usort($points, fn (Point $a, Point $b) => ($a->distance ?? PHP_FLOAT_MAX) <=> ($b->distance ?? PHP_FLOAT_MAX));

        return $points;
    }
}
