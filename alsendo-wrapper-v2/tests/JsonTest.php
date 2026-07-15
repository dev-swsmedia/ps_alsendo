<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests;

use Alsendo\AlsendoWrapper\Json;
use Alsendo\AlsendoWrapper\Model\Service\Service;
use Alsendo\AlsendoWrapper\Model\Service\ServiceStructure;
use Alsendo\AlsendoWrapper\Tests\Model\Service\ServiceStructureTest;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class JsonTest extends TestCase
{
    private string $json;

    protected function setUp(): void
    {
        // Przykładowy JSON
        $this->json = json_encode([
            'services' => [
                ['service_id' => '1', 'name' => 'UPS Standard'],
                ['service_id' => '2', 'name' => 'UPS Express'],
            ],
            'wrong_field_name' => 'Prawidłowe dane',
        ]);
    }

    /** Test 1: Sprawdzamy poprawne mapowanie tablicy obiektów */
    public function testServiceMapping(): void
    {
        $serviceStructure = Json::mapJsonToObject($this->json, ServiceStructureTest::class);

        $this->assertIsArray($serviceStructure->services);
        $this->assertCount(2, $serviceStructure->services);
        $this->assertInstanceOf(Service::class, $serviceStructure->services[0]);

        $this->assertEquals('1', $serviceStructure->services[0]->service_id);
        $this->assertEquals('UPS Standard', $serviceStructure->services[0]->name);

        $this->assertEquals('2', $serviceStructure->services[1]->service_id);
        $this->assertEquals('UPS Express', $serviceStructure->services[1]->name);
    }

    /** Test 2: Sprawdzamy domyślne mapowanie pól */
    public function testDefaultFieldMapping(): void
    {
        $json = json_encode([
            'services' => [],
            'unknown_field' => 'Brak danych',
        ]);

        $serviceStructure = Json::mapJsonToObject($json, ServiceStructure::class);

        $this->assertObjectNotHasProperty('unknown_field', $serviceStructure);
    }

    public function testDeserializeJsonToObjectFailureInvalidJson()
    {
        $json = 'Invalid JSON';
        $className = 'stdClass';
        $jsonObject = new Json();

        $this->expectException(\JsonException::class);
        Json::mapJsonToObject($json, $className);
    }

    public function testDeserializeJsonToObjectFailureNonExistingClass()
    {
        $json = '{"name":"John","age":30}';
        $className = 'NonExistingClass';
        $jsonObject = new Json();

        $this->expectException(\RuntimeException::class);
        Json::mapJsonToObject($json, $className);
    }
}
