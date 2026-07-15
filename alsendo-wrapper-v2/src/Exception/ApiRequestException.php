<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Exception;

/**
 * Internal exception thrown by makeRequest() when an HTTP request fails.
 * Preserves the raw response body so provider clients can parse it into
 * provider-specific structured errors.
 */
class ApiRequestException extends \Exception
{
    /** @var string|null Raw HTTP response body */
    private ?string $responseBody;

    public function __construct(string $message, int $code = 0, \Throwable $previous = null, string $responseBody = null)
    {
        parent::__construct($message, $code, $previous);
        $this->responseBody = $responseBody;
    }

    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }
}
