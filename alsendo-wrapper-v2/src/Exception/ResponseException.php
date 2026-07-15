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

use Alsendo\AlsendoWrapper\Model\Error\ApiErrorResponse;
use Exception;

/**
 * Exception thrown when a response fails to be processed or returns an error status.
 * This exception is used to indicate issues that occur during the handling of a response,
 * such as invalid data, server errors, or communication failures.
 *
 * It extends the built-in Exception class and provides a standardized way to handle
 * response-related errors with a specific HTTP status code.
 *
 * @throws ResponseException when a response processing error occurs
 */
class ResponseException extends \Exception
{
    private ?ApiErrorResponse $apiErrorResponse;

    public function __construct(string $message, int $code = 400, \Throwable $previous = null, ApiErrorResponse $apiErrorResponse = null)
    {
        parent::__construct($message, $code, $previous);
        $this->apiErrorResponse = $apiErrorResponse;
    }

    /**
     * Returns the structured API error response.
     * If none was set, constructs a fallback from the exception message and code.
     */
    public function getApiErrorResponse(): ApiErrorResponse
    {
        if ($this->apiErrorResponse !== null) {
            return $this->apiErrorResponse;
        }

        return new ApiErrorResponse($this->getMessage(), [], $this->getCode());
    }
}
