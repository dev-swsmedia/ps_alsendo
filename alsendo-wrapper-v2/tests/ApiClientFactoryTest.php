<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests;

use Alsendo\AlsendoWrapper\Api\Apaczka\ApiApaczkaClient;
use Alsendo\AlsendoWrapper\Api\Ecolet\ApiEcoletClient;
use Alsendo\AlsendoWrapper\Api\Zaslat\ApiZaslatClient;
use Alsendo\AlsendoWrapper\ApiClientFactory;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApiClientFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        // Ładowanie zmiennych z pliku .env
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../', '.env.test.local');
        $dotenv->load();
    }

    public function testCreateThrowsExceptionForInvalidApiName()
    {
        $factory = new ApiClientFactory();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown API name: foo');

        $factory->create('foo', []);
    }

    public function testCreateReturnsApiApaczkaClientForApaczkaApiName()
    {
        $factory = new ApiClientFactory();
        $config = [
            'app_id' => $_ENV['APACZKA_API_APP_ID'],
            'app_secret' => $_ENV['APACZKA_API_APP_SECRET'],
        ];

        $client = $factory->create(ApiClientFactory::APACZKA, $config);

        $this->assertInstanceOf(ApiApaczkaClient::class, $client);
    }

    //    public function testCreateReturnsApiEcoletClientForEcoletApiName()
    //    {
    //        $factory = new ApiClientFactory();
    //        $config = ['foo' => 'bar'];
    //
    //        $client = $factory->create(ApiClientFactory::ECOLET, $config);
    //
    //        $this->assertInstanceOf(ApiEcoletClient::class, $client);
    //
    //    }
    //
    //    public function testCreateReturnsApiZaslatClientForZaslatApiName()
    //    {
    //        $factory = new ApiClientFactory();
    //        $config = ['foo' => 'bar'];
    //
    //        $client = $factory->create(ApiClientFactory::ZASLAT, $config);
    //
    //        $this->assertInstanceOf(ApiZaslatClient::class, $client);
    //
    //    }
}
