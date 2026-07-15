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
use Alsendo\AlsendoWrapper\Api\Points\EcoletPointService;
use Alsendo\AlsendoWrapper\Api\Points\Model\Point;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EcoletPointServiceTest extends TestCase
{
    protected function setUp(): void
    {
        EcoletPointService::clearCache();
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

    private function getSampleEcoletLocalitiesResponse(): string
    {
        return json_encode([
            'data' => [
                [
                    'id' => 5001,
                    'name' => 'Bucuresti',
                    'municipality' => 'Bucuresti',
                    'postal_code' => '010001',
                    'county' => ['name' => 'Bucuresti', 'code' => 'B'],
                ],
            ],
        ]);
    }

    private function getSampleEcoletPointsResponse(): string
    {
        return json_encode([
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
                [
                    'id' => 1002,
                    'name' => 'Ecolet Locker',
                    'street' => 'Bd. Unirii 22',
                    'postal_code' => '030167',
                    'country_code' => 'RO',
                    'lat' => 44.4230,
                    'lng' => 26.1100,
                    'courier_slug' => 'sameday',
                    'type' => 'locker',
                    'locality' => ['name' => 'Bucuresti'],
                ],
            ],
        ]);
    }

    private function getSampleEcoletServicesResponse(): string
    {
        return json_encode([
            'services' => [
                [
                    'id' => 10,
                    'slug' => 'sameday_locker',
                    'full_name' => 'Sameday Locker',
                    'courier' => ['id' => 3, 'slug' => 'sameday', 'name' => 'Sameday', 'status' => true],
                ],
                [
                    'id' => 11,
                    'slug' => 'sameday_standard',
                    'full_name' => 'Sameday Standard',
                    'courier' => ['id' => 3, 'slug' => 'sameday', 'name' => 'Sameday', 'status' => true],
                ],
                [
                    'id' => 20,
                    'slug' => 'dpd_standard',
                    'full_name' => 'DPD Standard',
                    'courier' => ['id' => 2, 'slug' => 'dpd', 'name' => 'DPD', 'status' => true],
                ],
                [
                    'id' => 30,
                    'slug' => 'fan_standard',
                    'full_name' => 'FAN Standard',
                    'courier' => ['id' => 1, 'slug' => 'fan', 'name' => 'FAN Courier', 'status' => true],
                ],
            ],
        ]);
    }

    public function testSearchUsesLocalityThenPoints(): void
    {
        // Response 1: localities, Response 2: map-points
        $httpClient = $this->createMockClient([
            new Response(200, [], $this->getSampleEcoletLocalitiesResponse()),
            new Response(200, [], $this->getSampleEcoletPointsResponse()),
        ]);

        $service = new EcoletPointService(['token' => 'test'], true, $httpClient);
        $points = $service->search('Bucuresti');

        $this->assertCount(2, $points);
        $this->assertInstanceOf(Point::class, $points[0]);
        $this->assertEquals('RO', $points[0]->country);
        $this->assertEquals('sameday', $points[0]->carrier);
    }

    public function testEcoletIdIsPreserved(): void
    {
        $httpClient = $this->createMockClient([
            new Response(200, [], $this->getSampleEcoletLocalitiesResponse()),
            new Response(200, [], $this->getSampleEcoletPointsResponse()),
        ]);

        $service = new EcoletPointService(['token' => 'test'], true, $httpClient);
        $points = $service->search('Bucuresti');

        $this->assertEquals(1001, $points[0]->ecoletId);
        $this->assertEquals('1001', $points[0]->code);
    }

    public function testLockerDetection(): void
    {
        $httpClient = $this->createMockClient([
            new Response(200, [], $this->getSampleEcoletLocalitiesResponse()),
            new Response(200, [], $this->getSampleEcoletPointsResponse()),
        ]);

        $service = new EcoletPointService(['token' => 'test'], true, $httpClient);
        $points = $service->search('Bucuresti');

        $this->assertFalse($points[0]->isLocker);
        $this->assertTrue($points[1]->isLocker);
    }

    public function testFallbackToNominatimWhenNoLocalities(): void
    {
        $geocoding = $this->createMockGeocoding(44.4268, 26.1025);

        // Response 1: empty localities, Response 2: map-points
        $httpClient = $this->createMockClient([
            new Response(200, [], json_encode(['data' => []])),
            new Response(200, [], $this->getSampleEcoletPointsResponse()),
        ]);

        $service = new EcoletPointService(['token' => 'test'], true, $httpClient, $geocoding);
        $points = $service->search('UnknownPlace');

        $this->assertNotEmpty($points);
        $this->assertNotNull($points[0]->distance);
    }

    public function testSearchWithCarrierConvertsSlugToId(): void
    {
        // Response 1: localities, Response 2: services (for courier map), Response 3: map-points
        $httpClient = $this->createMockClient([
            new Response(200, [], $this->getSampleEcoletLocalitiesResponse()),
            new Response(200, [], $this->getSampleEcoletServicesResponse()),
            new Response(200, [], $this->getSampleEcoletPointsResponse()),
        ]);

        $service = new EcoletPointService(['token' => 'test'], true, $httpClient);
        $points = $service->search('Bucuresti', 'sameday');

        $this->assertCount(2, $points);
        $this->assertEquals('sameday', $points[0]->carrier);
    }

    public function testSearchWithUnknownCarrierSkipsFilter(): void
    {
        // Response 1: localities, Response 2: services (for courier map), Response 3: map-points (unfiltered)
        $httpClient = $this->createMockClient([
            new Response(200, [], $this->getSampleEcoletLocalitiesResponse()),
            new Response(200, [], $this->getSampleEcoletServicesResponse()),
            new Response(200, [], $this->getSampleEcoletPointsResponse()),
        ]);

        $service = new EcoletPointService(['token' => 'test'], true, $httpClient);
        $points = $service->search('Bucuresti', 'unknown_carrier');

        // Should still return points (filter skipped for unknown carrier)
        $this->assertCount(2, $points);
    }

    public function testSearchWithCarrierCaseInsensitive(): void
    {
        // Response 1: localities, Response 2: services (for courier map), Response 3: map-points
        $httpClient = $this->createMockClient([
            new Response(200, [], $this->getSampleEcoletLocalitiesResponse()),
            new Response(200, [], $this->getSampleEcoletServicesResponse()),
            new Response(200, [], $this->getSampleEcoletPointsResponse()),
        ]);

        $service = new EcoletPointService(['token' => 'test'], true, $httpClient);
        // "Sameday" with capital S should still match
        $points = $service->search('Bucuresti', 'Sameday');

        $this->assertCount(2, $points);
    }

    public function testApiError(): void
    {
        // Localities OK, but map-points fails
        $httpClient = $this->createMockClient([
            new Response(200, [], $this->getSampleEcoletLocalitiesResponse()),
            new \GuzzleHttp\Exception\RequestException(
                'Unauthorized',
                new \GuzzleHttp\Psr7\Request('POST', '/api/v1/map-points/ro'),
                new Response(401)
            ),
        ]);

        $service = new EcoletPointService(['token' => 'bad'], true, $httpClient);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Ecolet API request failed');

        $service->search('Bucuresti');
    }

    public function testEmptyAddressThrows(): void
    {
        $service = new EcoletPointService(['token' => 'test']);

        $this->expectException(\InvalidArgumentException::class);

        $service->search('');
    }
}
