<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Unit\Api\Geocoding;

use Alsendo\AlsendoWrapper\Api\Geocoding\GeoUtils;
use Alsendo\AlsendoWrapper\Api\Points\Model\Point;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class GeoUtilsTest extends TestCase
{
    public function testCalculateDistance(): void
    {
        // Praha (50.0755, 14.4378) -> Brno (49.1951, 16.6068) ~ 185 km
        $distance = GeoUtils::calculateDistance(50.0755, 14.4378, 49.1951, 16.6068);

        $this->assertGreaterThan(180, $distance);
        $this->assertLessThan(190, $distance);
    }

    public function testCalculateDistanceSamePoint(): void
    {
        $distance = GeoUtils::calculateDistance(50.0755, 14.4378, 50.0755, 14.4378);

        $this->assertEquals(0.0, $distance);
    }

    public function testSortByDistance(): void
    {
        $far = new Point();
        $far->code = 'FAR';
        $far->latitude = 49.1951;
        $far->longitude = 16.6068; // Brno

        $near = new Point();
        $near->code = 'NEAR';
        $near->latitude = 50.0833;
        $near->longitude = 14.4167; // Praha center

        // Pass in reverse order (far first)
        $sorted = GeoUtils::sortByDistance([$far, $near], 50.0755, 14.4378); // Praha

        $this->assertEquals('NEAR', $sorted[0]->code);
        $this->assertEquals('FAR', $sorted[1]->code);
    }

    public function testSortByDistanceSetsDistanceField(): void
    {
        $point = new Point();
        $point->code = 'TEST';
        $point->latitude = 49.1951;
        $point->longitude = 16.6068;

        $this->assertNull($point->distance);

        $sorted = GeoUtils::sortByDistance([$point], 50.0755, 14.4378);

        $this->assertNotNull($sorted[0]->distance);
        $this->assertGreaterThan(0, $sorted[0]->distance);
    }

    public function testSortByDistanceHandlesNullCoordinates(): void
    {
        $withCoords = new Point();
        $withCoords->code = 'WITH';
        $withCoords->latitude = 50.0833;
        $withCoords->longitude = 14.4167;

        $noCoords = new Point();
        $noCoords->code = 'WITHOUT';
        $noCoords->latitude = null;
        $noCoords->longitude = null;

        $sorted = GeoUtils::sortByDistance([$noCoords, $withCoords], 50.0755, 14.4378);

        // Point with coords should be first (closer), null coords -> PHP_FLOAT_MAX
        $this->assertEquals('WITH', $sorted[0]->code);
        $this->assertEquals('WITHOUT', $sorted[1]->code);
        $this->assertEquals(PHP_FLOAT_MAX, $sorted[1]->distance);
    }
}
