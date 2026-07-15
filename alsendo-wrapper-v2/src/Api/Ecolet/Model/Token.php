<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Ecolet\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Token
{
    public string $tokenType;
    public int $expiresAt;
    public string $accessToken;
    public string $refreshToken;

    public function __construct(array $data)
    {
        $this->tokenType = $data['token_type'] ?? 'Bearer';
        if (isset($data['expires_in'])) {
            $this->expiresAt = time() + $data['expires_in'];
        } else {
            $this->expiresAt = $data['expires_at'] ?? 0;
        }

        $this->accessToken = $data['access_token'] ?? '';
        $this->refreshToken = $data['refresh_token'] ?? '';
    }

    /**
     * Zwraca token dostępu
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * Sprawdza, czy token jest nadal ważny
     */
    public function isExpired(): bool
    {
        return time() > ($this->expiresAt - 60);
    }

    /**
     * Konwertuje obiekt na tablicę (np. do zapisu w pliku)
     */
    public function toArray(): array
    {
        return [
            'token_type' => $this->tokenType,
            'expires_at' => $this->expiresAt,
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
        ];
    }

    /**
     * Tworzy obiekt z JSON-a
     */
    public static function fromJson(string $json): ?self
    {
        $data = json_decode($json, true);

        return $data ? new self($data) : null;
    }

    /**
     * Zwraca JSON z obiektu
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
