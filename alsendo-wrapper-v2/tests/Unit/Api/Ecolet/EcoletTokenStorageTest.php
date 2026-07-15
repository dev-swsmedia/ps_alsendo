<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Unit\Api\Ecolet;

use Alsendo\AlsendoWrapper\Api\Ecolet\EcoletTokenStorage;
use Alsendo\AlsendoWrapper\Api\Ecolet\Model\Token;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EcoletTokenStorageTest extends TestCase
{
    private string $testFilePath = __DIR__ . '/test_tokens.json';

    protected function setUp(): void
    {
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
    }

    public function testSaveAndLoadToken(): void
    {
        $tokenData = [
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
        ];

        $tokenResponse = new Token($tokenData);
        $tokenStorage = new EcoletTokenStorage($this->testFilePath);
        $tokenStorage->saveToken($tokenResponse);

        $loadedToken = $tokenStorage->getToken();
        $this->assertInstanceOf(Token::class, $loadedToken);
        $this->assertEquals('test_access_token', $loadedToken->accessToken);
    }
}
