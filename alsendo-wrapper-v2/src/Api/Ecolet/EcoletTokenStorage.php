<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Ecolet;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Api\Ecolet\Model\Token;
use RuntimeException;

class EcoletTokenStorage
{
    private string $filePath;

    /**
     * Initializes the object by ensuring the specified file exists.
     *
     * If the file does not exist, it attempts to create an empty file.
     * If file creation fails, a RuntimeException is thrown with an error message.
     *
     * @param string $filePath the path to the file that should exist
     *
     * @return void
     *
     * @throws \RuntimeException if the file could not be created
     */
    public function __construct(string $filePath)
    {
        if (!file_exists($filePath)) {
            try {
                file_put_contents($filePath, '');
            } catch (\Throwable $e) {
                throw new \RuntimeException("Can't create file $filePath: " . $e->getMessage());
            }
        }
        $this->filePath = $filePath;
    }

    /**
     * Saves a token to a file.
     *
     * @param Token $token the token to save
     *
     * @throws \RuntimeException if the file cannot be written to
     */
    public function saveToken(Token $token): void
    {
        file_put_contents($this->filePath, $token->toJson());
    }

    /**
     * Retrieves a stored token from a file.
     *
     * @return Token|null the stored token if the file exists and is valid, null otherwise
     *
     * @throws \RuntimeException if the file cannot be read
     */
    public function getToken(): ?Token
    {
        if (!file_exists($this->filePath)) {
            return null;
        }

        return Token::fromJson(file_get_contents($this->filePath));
    }
}
