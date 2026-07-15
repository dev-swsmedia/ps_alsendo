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
use Alsendo\AlsendoWrapper\Api\Points\Model\Point;
use Alsendo\AlsendoWrapper\Api\Points\ZaslatPointService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ZaslatPointServiceTest extends TestCase
{
    protected function setUp(): void
    {
        ZaslatPointService::clearCache();
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

    private function getSampleZaslatResponse(): string
    {
        return json_encode([
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
                '102' => [
                    'code' => 'PPL-BRNO-1',
                    'name' => 'PPL Depo Brno',
                    'street' => 'Masarykova 5',
                    'city' => 'Brno',
                    'zip' => '60200',
                    'country' => 'CZ',
                    'carrier' => 'PPL',
                    'is_locker' => 0,
                    'location' => ['lat' => '49.1951', 'lng' => '16.6068'],
                ],
                '103' => [
                    'code' => 'ZASILKOVNA-PRAHA-1',
                    'name' => 'Zasilkovna Box Praha',
                    'street' => 'Narodni 10',
                    'city' => 'Praha',
                    'zip' => '11000',
                    'country' => 'CZ',
                    'carrier' => 'ZASILKOVNA',
                    'is_locker' => 1,
                    'location' => ['lat' => '50.0810', 'lng' => '14.4140'],
                ],
            ],
        ]);
    }

    public function testSearchReturnsNormalizedPoints(): void
    {
        $geocoding = $this->createMockGeocoding(50.0833, 14.4167);
        $httpClient = $this->createMockClient([
            new Response(200, [], $this->getSampleZaslatResponse()),
        ]);

        $service = new ZaslatPointService(['api_key' => 'test'], true, $httpClient, $geocoding);
        $points = $service->search('Praha');

        $this->assertCount(3, $points);
        $this->assertInstanceOf(Point::class, $points[0]);
        $this->assertEquals('CZ', $points[0]->country);
        $this->assertNotNull($points[0]->distance);
        // Praha points should be first (closest)
        $this->assertStringContainsString('Praha', $points[0]->city);
    }

    public function testSearchSortsByDistance(): void
    {
        $geocoding = $this->createMockGeocoding(50.0833, 14.4167); // Praha
        $httpClient = $this->createMockClient([
            new Response(200, [], $this->getSampleZaslatResponse()),
        ]);

        $service = new ZaslatPointService(['api_key' => 'test'], true, $httpClient, $geocoding);
        $points = $service->search('Praha', null, 3);

        // First point should be closest to Praha
        $this->assertLessThanOrEqual($points[1]->distance, $points[0]->distance);
        $this->assertLessThanOrEqual($points[2]->distance, $points[1]->distance);
    }

    public function testSearchWithLimit(): void
    {
        $geocoding = $this->createMockGeocoding(50.0833, 14.4167);
        $httpClient = $this->createMockClient([
            new Response(200, [], $this->getSampleZaslatResponse()),
        ]);

        $service = new ZaslatPointService(['api_key' => 'test'], true, $httpClient, $geocoding);
        $points = $service->search('Praha', null, 2);

        $this->assertCount(2, $points);
    }

    public function testLockerDetection(): void
    {
        $geocoding = $this->createMockGeocoding(50.0833, 14.4167);
        $httpClient = $this->createMockClient([
            new Response(200, [], $this->getSampleZaslatResponse()),
        ]);

        $service = new ZaslatPointService(['api_key' => 'test'], true, $httpClient, $geocoding);
        $points = $service->search('Praha');

        $lockers = array_filter($points, fn (Point $p) => $p->isLocker === true);
        $this->assertNotEmpty($lockers);
        $locker = array_values($lockers)[0];
        $this->assertEquals('ZASILKOVNA', $locker->carrier);
    }

    public function testEmptyResponse(): void
    {
        $geocoding = $this->createMockGeocoding(50.0833, 14.4167);
        $httpClient = $this->createMockClient([
            new Response(200, [], json_encode(['status' => 200, 'data' => []])),
        ]);

        $service = new ZaslatPointService(['api_key' => 'test'], true, $httpClient, $geocoding);
        $points = $service->search('Praha');

        $this->assertIsArray($points);
        $this->assertEmpty($points);
    }

    public function testApiError(): void
    {
        $geocoding = $this->createMockGeocoding(50.0833, 14.4167);
        $httpClient = $this->createMockClient([
            new \GuzzleHttp\Exception\RequestException(
                'Server Error',
                new \GuzzleHttp\Psr7\Request('GET', '/api/v1/parcelpoints/list'),
                new Response(500)
            ),
        ]);

        $service = new ZaslatPointService(['api_key' => 'test'], true, $httpClient, $geocoding);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Zaslat API request failed');

        $service->search('Praha');
    }

    public function testEmptyAddressThrows(): void
    {
        $service = new ZaslatPointService(['api_key' => 'test']);

        $this->expectException(\InvalidArgumentException::class);

        $service->search('');
    }
}
