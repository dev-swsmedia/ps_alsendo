<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Unit\Api\Points;

use Alsendo\AlsendoWrapper\Api\Points\Model\Point;
use Alsendo\AlsendoWrapper\Json;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PointTest extends TestCase
{
    public function testMapFromApiResponse(): void
    {
        $data = [
            'operator' => 'INPOST',
            'operatorPretty' => 'InPost',
            'brand' => 'INPOST',
            'brandPretty' => 'InPost',
            'code' => 'KRA010',
            'street' => 'ul. Krakowska 10',
            'city' => 'Kraków',
            'postalCode' => '30-001',
            'latitude' => 50.0647,
            'longitude' => 19.9450,
            'cod' => true,
            'description' => 'Paczkomat InPost przy Galerii Krakowskiej',
            'available' => true,
            'postingPoint' => true,
            'deliveryPoint' => true,
            'district' => 'Stare Miasto',
            'province' => 'małopolskie',
            'pointTypes' => ['parcel_locker'],
            'openingHoursMap' => [
                'monday' => '00:00-23:59',
                'tuesday' => '00:00-23:59',
            ],
        ];

        $point = Json::mapArrayToObject($data, Point::class);

        $this->assertInstanceOf(Point::class, $point);
        $this->assertEquals('INPOST', $point->operator);
        $this->assertEquals('InPost', $point->operatorPretty);
        $this->assertEquals('INPOST', $point->brand);
        $this->assertEquals('InPost', $point->brandPretty);
        $this->assertEquals('KRA010', $point->code);
        $this->assertEquals('ul. Krakowska 10', $point->street);
        $this->assertEquals('Kraków', $point->city);
        $this->assertEquals('30-001', $point->postalCode);
        $this->assertEquals(50.0647, $point->latitude);
        $this->assertEquals(19.9450, $point->longitude);
        $this->assertTrue($point->cod);
        $this->assertEquals('Paczkomat InPost przy Galerii Krakowskiej', $point->description);
        $this->assertTrue($point->available);
        $this->assertTrue($point->postingPoint);
        $this->assertTrue($point->deliveryPoint);
        $this->assertEquals('Stare Miasto', $point->district);
        $this->assertEquals('małopolskie', $point->province);
        $this->assertEquals(['parcel_locker'], $point->pointTypes);
        $this->assertIsArray($point->openingHoursMap);
        $this->assertEquals('00:00-23:59', $point->openingHoursMap['monday']);
    }

    public function testMapFromApiResponsePartialFields(): void
    {
        $data = [
            'code' => 'WAW001',
            'city' => 'Warszawa',
            'operator' => 'DPD',
        ];

        $point = Json::mapArrayToObject($data, Point::class);

        $this->assertInstanceOf(Point::class, $point);
        $this->assertEquals('WAW001', $point->code);
        $this->assertEquals('Warszawa', $point->city);
        $this->assertEquals('DPD', $point->operator);
        $this->assertNull($point->street);
        $this->assertNull($point->latitude);
        $this->assertNull($point->openingHoursMap);
    }

    public function testMapFromApiResponseEmptyOpeningHours(): void
    {
        $data = [
            'code' => 'GDA005',
            'openingHoursMap' => [],
        ];

        $point = Json::mapArrayToObject($data, Point::class);

        $this->assertInstanceOf(Point::class, $point);
        $this->assertEquals('GDA005', $point->code);
        $this->assertEquals([], $point->openingHoursMap);
    }

    public function testMapArrayOfPoints(): void
    {
        $data = [
            [
                'code' => 'KRA001',
                'city' => 'Kraków',
                'operator' => 'INPOST',
            ],
            [
                'code' => 'KRA002',
                'city' => 'Kraków',
                'operator' => 'DPD',
            ],
            [
                'code' => 'KRA003',
                'city' => 'Kraków',
                'operator' => 'POCZTA',
            ],
        ];

        $points = array_map(
            fn (array $item) => Json::mapArrayToObject($item, Point::class),
            $data
        );

        $this->assertCount(3, $points);
        $this->assertInstanceOf(Point::class, $points[0]);
        $this->assertInstanceOf(Point::class, $points[1]);
        $this->assertInstanceOf(Point::class, $points[2]);
        $this->assertEquals('KRA001', $points[0]->code);
        $this->assertEquals('KRA002', $points[1]->code);
        $this->assertEquals('KRA003', $points[2]->code);
    }

    public function testMultiRegionFields(): void
    {
        $point = new Point();
        $point->code = 'PPL-PRAHA-1';
        $point->name = 'PPL Depo Praha';
        $point->country = 'CZ';
        $point->carrier = 'PPL';
        $point->isLocker = false;
        $point->distance = 1.5;
        $point->address = 'Vodickova 12, Praha, 11000';

        $this->assertEquals('PPL-PRAHA-1', $point->code);
        $this->assertEquals('PPL Depo Praha', $point->name);
        $this->assertEquals('CZ', $point->country);
        $this->assertEquals('PPL', $point->carrier);
        $this->assertFalse($point->isLocker);
        $this->assertEquals(1.5, $point->distance);
        $this->assertEquals('Vodickova 12, Praha, 11000', $point->address);

        // RO-specific
        $point->ecoletId = 1001;
        $this->assertEquals(1001, $point->ecoletId);
    }

    public function testNewFieldsDefaultToNull(): void
    {
        $point = new Point();

        $this->assertNull($point->country);
        $this->assertNull($point->carrier);
        $this->assertNull($point->isLocker);
        $this->assertNull($point->distance);
        $this->assertNull($point->ecoletId);
        $this->assertNull($point->name);
        $this->assertNull($point->address);
    }
}
