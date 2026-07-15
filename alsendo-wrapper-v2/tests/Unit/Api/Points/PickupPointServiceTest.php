<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Unit\Api\Points;

use Alsendo\AlsendoWrapper\Api\Geocoding\NominatimGeocodingService;
use Alsendo\AlsendoWrapper\Api\Points\BliskaPaczkaPointService;
use Alsendo\AlsendoWrapper\Api\Points\Model\Point;
use Alsendo\AlsendoWrapper\Api\Points\PickupPointService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PickupPointServiceTest extends TestCase
{
    protected function setUp(): void
    {
        PickupPointService::clearCache();
    }

    private function createMockClient(array $responses): Client
    {
        $mock = new MockHandler($responses);

        return new Client(['handler' => HandlerStack::create($mock)]);
    }

    private function createMockGeocoding(float $lat, float $lon): NominatimGeocodingService
    {
        $mockClient = $this->createMockClient([
            new Response(200, [], json_encode([
                ['lat' => (string) $lat, 'lon' => (string) $lon, 'display_name' => 'Mock'],
            ])),
        ]);

        $geocoding = new NominatimGeocodingService();
        $ref = new \ReflectionClass($geocoding);
        $prop = $ref->getProperty('httpClient');
        $prop->setAccessible(true);
        $prop->setValue($geocoding, $mockClient);

        return $geocoding;
    }

    // ── PL (BliskaPaczka delegation) ────────────────────────────────────

    public function testPlSearchDelegatesToBliskaPaczka(): void
    {
        $point1 = new Point();
        $point1->code = 'KRA010';
        $point1->operator = 'INPOST';
        $point1->city = 'Kraków';
        $point1->pointTypes = ['parcel_locker'];

        $point2 = new Point();
        $point2->code = 'KRA020';
        $point2->operator = 'DPD';
        $point2->city = 'Kraków';
        $point2->pointTypes = ['delivery_point'];

        $mockBliskaPaczka = $this->createMock(BliskaPaczkaPointService::class);
        $mockBliskaPaczka->expects($this->once())
            ->method('searchByAddress')
            ->with('Kraków', 50.0, 'KM', 20, [])
            ->willReturn([$point1, $point2]);

        $service = new PickupPointService('pl', [], false, null, null, $mockBliskaPaczka);
        $points = $service->search('Kraków');

        $this->assertCount(2, $points);
        $this->assertEquals('KRA010', $points[0]->code);
        $this->assertEquals('KRA020', $points[1]->code);
    }

    public function testPlSearchSetsCountryAndCarrierFields(): void
    {
        $point = new Point();
        $point->code = 'KRA010';
        $point->operator = 'INPOST';
        $point->city = 'Kraków';
        $point->pointTypes = ['parcel_locker'];

        $mockBliskaPaczka = $this->createMock(BliskaPaczkaPointService::class);
        $mockBliskaPaczka->method('searchByAddress')->willReturn([$point]);

        $service = new PickupPointService('pl', [], false, null, null, $mockBliskaPaczka);
        $points = $service->search('Kraków');

        $this->assertEquals('PL', $points[0]->country);
        $this->assertEquals('INPOST', $points[0]->carrier);
    }

    public function testPlLockerDetection(): void
    {
        $locker = new Point();
        $locker->code = 'KRA010';
        $locker->operator = 'INPOST';
        $locker->pointTypes = ['parcel_locker'];

        $nonLocker = new Point();
        $nonLocker->code = 'KRA020';
        $nonLocker->operator = 'DPD';
        $nonLocker->pointTypes = ['delivery_point'];

        $mockBliskaPaczka = $this->createMock(BliskaPaczkaPointService::class);
        $mockBliskaPaczka->method('searchByAddress')->willReturn([$locker, $nonLocker]);

        $service = new PickupPointService('pl', [], false, null, null, $mockBliskaPaczka);
        $points = $service->search('Kraków');

        $this->assertTrue($points[0]->isLocker);
        $this->assertFalse($points[1]->isLocker);
    }

    // ── CZ delegation ──────────────────────────────────────────────────

    public function testCzSearchDelegatesToZaslatPointService(): void
    {
        $geocoding = $this->createMockGeocoding(50.0833, 14.4167);
        $httpClient = $this->createMockClient([
            new Response(200, [], json_encode([
                'status' => 200,
                'data' => [
                    '101' => [
                        'code' => 'PPL-PRAHA-1',
                        'name' => 'PPL Depo Praha',
                        'street' => 'Vodickova 12',
                        'city' => 'Praha',
                        'zip' => '11000',
                        'country' => 'CZ',
                        'carrier' => 'PPL',
                        'is_locker' => 0,
                        'location' => ['lat' => '50.0833', 'lng' => '14.4167'],
                    ],
                ],
            ])),
        ]);

        $service = new PickupPointService('cz', ['api_key' => 'test'], true, $httpClient, $geocoding);
        $points = $service->search('Praha');

        $this->assertCount(1, $points);
        $this->assertEquals('CZ', $points[0]->country);
        $this->assertEquals('PPL', $points[0]->carrier);
    }

    // ── RO delegation ──────────────────────────────────────────────────

    public function testRoSearchDelegatesToEcoletPointService(): void
    {
        $httpClient = $this->createMockClient([
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id' => 5001,
                        'name' => 'Bucuresti',
                        'municipality' => 'Bucuresti',
                        'postal_code' => '010001',
                        'county' => ['name' => 'Bucuresti', 'code' => 'B'],
                    ],
                ],
            ])),
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id' => 1001,
                        'name' => 'Ecolet Centru',
                        'street' => 'Str. Victoriei 10',
                        'postal_code' => '010001',
                        'country_code' => 'RO',
                        'lat' => 44.4268,
                        'lng' => 26.1025,
                        'courier_slug' => 'sameday',
                        'type' => 'point',
                        'locality' => ['name' => 'Bucuresti'],
                    ],
                ],
            ])),
        ]);

        $service = new PickupPointService('ro', ['token' => 'test'], true, $httpClient);
        $points = $service->search('Bucuresti');

        $this->assertCount(1, $points);
        $this->assertEquals('RO', $points[0]->country);
        $this->assertEquals('sameday', $points[0]->carrier);
    }

    // ── General ────────────────────────────────────────────────────────

    public function testEmptyAddressThrows(): void
    {
        $service = new PickupPointService('cz', ['api_key' => 'test']);

        $this->expectException(\InvalidArgumentException::class);

        $service->search('');
    }

    public function testUnsupportedCountryThrows(): void
    {
        $service = new PickupPointService('de');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported country');

        $service->search('Berlin');
    }
}
