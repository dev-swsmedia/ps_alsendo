<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Unit\Api\Ecolet;

use Alsendo\AlsendoWrapper\Api\Ecolet\Model\Token;
use PHPUnit\Framework\TestCase;

if (!defined('_PS_VERSION_')) {
    exit;
}

class TokenResponseTest extends TestCase
{
    /**
     * Test that the `Token` object properly maps the token response
     * data to the correct properties.
     *
     * @return void
     */
    public function testTokenResponseMapping(): void
    {
        $data = [
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'access_token' => 'abc123',
            'refresh_token' => 'refresh456',
        ];

        $tokenResponse = new Token($data);

        $this->assertEquals('Bearer', $tokenResponse->tokenType);
        $this->assertEquals(time() + 3600, $tokenResponse->expiresAt);
        $this->assertEquals('abc123', $tokenResponse->accessToken);
        $this->assertEquals('refresh456', $tokenResponse->refreshToken);
    }

    /**
     * Tests that the `Token` object correctly identifies a token as being expired.
     *
     * @return void
     */
    public function testTokenExpiration(): void
    {
        $data = [
            'token_type' => 'Bearer',
            'expires_in' => -100, // Token expired
            'access_token' => 'expired_token',
            'refresh_token' => 'refresh_token',
        ];

        $tokenResponse = new Token($data);
        $this->assertTrue($tokenResponse->isExpired());
    }
}
