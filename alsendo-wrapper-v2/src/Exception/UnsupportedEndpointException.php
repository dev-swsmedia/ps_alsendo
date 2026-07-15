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
 * Throws when an attempted request is made to an endpoint that is not supported by the API.
 *
 * This exception is raised when a client attempts to access a resource or perform an operation
 * on an endpoint that does not exist or is not implemented in the current API version.
 *
 * The exception includes the name of the unsupported endpoint in its message, and can be
 * customized with a HTTP status code and a previous exception if applicable.
 *
 * @param string $endpoint The name of the endpoint that is not supported
 * @param int $code HTTP status code to use for the response (default 400)
 * @param \Throwable|null $previous Previous exception that caused this one (optional)
 *
 * @throws UnsupportedEndpointException when an unsupported endpoint is accessed
 */
class UnsupportedEndpointException extends \Exception
{
    public function __construct(string $endpoint, int $code = 400, \Throwable $previous = null)
    {
        $message = "The endpoint '{$endpoint}' is not supported by this API.";
        parent::__construct($message, $code, $previous);
    }
}
