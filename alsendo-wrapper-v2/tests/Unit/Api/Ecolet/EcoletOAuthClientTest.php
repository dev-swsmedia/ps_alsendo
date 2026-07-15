<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Unit\Api\Ecolet;

use Alsendo\AlsendoWrapper\Api\Ecolet\EcoletOAuthClient;
use Alsendo\AlsendoWrapper\Api\Ecolet\Model\Token;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EcoletOAuthClientTest extends TestCase
{
    private EcoletOAuthClient $oauthClient;

    protected function setUp(): void
    {
        // Create a mock HTTP handler
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'access_token' => 'mock_access_token',
                'refresh_token' => 'mock_refresh_token',
            ])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->oauthClient = new EcoletOAuthClient([
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => 'https://your-app.com/callback',
            'auth_url' => 'https://panel.ecolet.ro/oauth/authorize',
            'token_url' => 'https://panel.ecolet.ro/api/v1/oauth/token',
        ]);

        // Replace real HTTP client with mocked one
        $reflection = new \ReflectionClass($this->oauthClient);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($this->oauthClient, $client);
    }

    public function testGetAccessToken(): void
    {
        $token = $this->oauthClient->getAccessToken('mock_code');

        $this->assertInstanceOf(Token::class, $token);
        $this->assertEquals('mock_access_token', $token->accessToken);
    }

    public function testGetAccessTokenWithPassword(): void
    {
        $token = $this->oauthClient->getAccessTokenWithPassword('mock_username', 'mock_password');

        $this->assertInstanceOf(Token::class, $token);
        $this->assertEquals('mock_access_token', $token->accessToken);
    }

    public function testRefreshAccessToken(): void
    {
        $token = $this->oauthClient->refreshAccessToken('mock_refresh_token');

        $this->assertInstanceOf(Token::class, $token);
        $this->assertEquals('mock_access_token', $token->accessToken);
    }
}
