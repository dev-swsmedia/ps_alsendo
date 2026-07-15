<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Model\Error;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApiErrorResponse
{
    /** @var bool Always false */
    public bool $success = false;

    /** @var int|null HTTP status code from API, null for pre-request errors */
    public ?int $statusCode;

    /** @var string|null General error message */
    public ?string $message;

    /** @var ErrorMessage[] Field-level errors */
    public array $errors;

    /**
     * @param string|null $message General error message
     * @param ErrorMessage[] $errors Field-level errors
     * @param int|null $statusCode HTTP status code
     */
    public function __construct(string $message = null, array $errors = [], int $statusCode = null)
    {
        $this->message = $message;
        $this->errors = $errors;
        $this->statusCode = $statusCode;
    }
}
