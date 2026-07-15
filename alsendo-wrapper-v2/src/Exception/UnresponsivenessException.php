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
 * Throws when a system or service fails to respond within a defined time limit.
 * This exception is used to indicate that a request has timed out due to lack of response.
 * It extends the base Exception class and is typically raised during asynchronous or blocking operations
 * where a response is expected but not received within a specified timeout period.
 *
 * @throws UnresponsivenessException when a response is not received within the expected time frame
 */
class UnresponsivenessException extends \Exception
{
    public function __construct(int $code = 400, \Throwable $previous = null)
    {
        $message = 'Error in response';
        parent::__construct($message, $code, $previous);
    }
}
