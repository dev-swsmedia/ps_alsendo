<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Unit\Api\Points;

use Alsendo\AlsendoWrapper\Api\Points\BliskaPaczkaPointService;
use Alsendo\AlsendoWrapper\Api\Points\Model\Operator;
use Alsendo\AlsendoWrapper\Api\Points\Model\Point;
use Alsendo\AlsendoWrapper\Api\Points\Model\PointSearchRequest;
use Alsendo\AlsendoWrapper\Exception\ValidationException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class BliskaPaczkaPointServiceTest extends TestCase
{
    private function createServiceWithMockResponse(string $body, int $status = 200, bool $test = false): BliskaPaczkaPointService
    {
        $mock = new MockHandler([
            new Response($status, ['Content-Type' => 'application/json'], $body),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        $service = new BliskaPaczkaPointService($test);

        $reflection = new \ReflectionClass($service);
        $httpClientProp = $reflection->getProperty('httpClient');
        $httpClientProp->setAccessible(true);
        $httpClientProp->setValue($service, $mockClient);

        return $service;
    }

    public function testSearchReturnsPoints(): void
    {
        $responseBody = json_encode([
            [
                'operator' => 'INPOST',
                'code' => 'KRA010',
                'city' => 'Kraków',
                'street' => 'ul. Krakowska 10',
                'postalCode' => '30-001',
                'latitude' => 50.0647,
                'longitude' => 19.9450,
            ],
            [
                'operator' => 'DPD',
                'code' => 'KRA020',
                'city' => 'Kraków',
                'street' => 'ul. Długa 5',
                'postalCode' => '30-002',
                'latitude' => 50.0650,
                'longitude' => 19.9460,
            ],
        ]);

        $service = $this->createServiceWithMockResponse($responseBody);

        $request = new PointSearchRequest();
        $request->searchText = 'Kraków';
        $request->size = 10;

        $result = $service->search($request);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(Point::class, $result[0]);
        $this->assertInstanceOf(Point::class, $result[1]);
        $this->assertEquals('KRA010', $result[0]->code);
        $this->assertEquals('INPOST', $result[0]->operator);
        $this->assertEquals('KRA020', $result[1]->code);
        $this->assertEquals('DPD', $result[1]->operator);
    }

    public function testSearchEmptyResults(): void
    {
        $service = $this->createServiceWithMockResponse('[]');

        $request = new PointSearchRequest();
        $request->searchText = 'NieistniejąceMiejsce123';

        $result = $service->search($request);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testSearchValidationError(): void
    {
        $service = $this->createServiceWithMockResponse('[]');

        $request = new PointSearchRequest();
        // searchText is empty - should fail validation

        $this->expectException(ValidationException::class);

        $service->search($request);
    }

    public function testGetPointReturnsPoint(): void
    {
        $responseBody = json_encode([
            'operator' => 'INPOST',
            'code' => 'KRA010',
            'city' => 'Kraków',
            'street' => 'ul. Krakowska 10',
            'postalCode' => '30-001',
            'latitude' => 50.0647,
            'longitude' => 19.9450,
            'available' => true,
            'postingPoint' => true,
            'deliveryPoint' => true,
        ]);

        $service = $this->createServiceWithMockResponse($responseBody);

        $result = $service->getPoint(Operator::INPOST, 'KRA010');

        $this->assertInstanceOf(Point::class, $result);
        $this->assertEquals('KRA010', $result->code);
        $this->assertEquals('INPOST', $result->operator);
        $this->assertEquals('Kraków', $result->city);
        $this->assertTrue($result->available);
    }

    public function testGetPointNotFound(): void
    {
        $mock = new MockHandler([
            new \GuzzleHttp\Exception\RequestException(
                'Not Found',
                new \GuzzleHttp\Psr7\Request('GET', '/api/v1/pos/INPOST/NONEXISTENT'),
                new Response(404, [], '{"error":"Point not found"}')
            ),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        $service = new BliskaPaczkaPointService(false);

        $reflection = new \ReflectionClass($service);
        $httpClientProp = $reflection->getProperty('httpClient');
        $httpClientProp->setAccessible(true);
        $httpClientProp->setValue($service, $mockClient);

        $this->expectException(\RuntimeException::class);

        $service->getPoint(Operator::INPOST, 'NONEXISTENT');
    }

    public function testCorrectUrlForProduction(): void
    {
        $service = new BliskaPaczkaPointService(false);

        $reflection = new \ReflectionClass($service);
        $configProp = $reflection->getProperty('config');
        $configProp->setAccessible(true);
        $config = $configProp->getValue($service);

        $this->assertEquals('https://pos.bliskapaczka.pl/', $config['apiUrl']);
    }

    public function testCorrectUrlForSandbox(): void
    {
        $service = new BliskaPaczkaPointService(true);

        $reflection = new \ReflectionClass($service);
        $configProp = $reflection->getProperty('config');
        $configProp->setAccessible(true);
        $config = $configProp->getValue($service);

        $this->assertEquals('https://pos.sandbox-bliskapaczka.pl/', $config['apiUrl']);
    }
}
