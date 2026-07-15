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
use Alsendo\AlsendoWrapper\Model\Error\ErrorMessage;
use Exception;

/**
 * Throws a validation exception when input data fails to meet required constraints.
 *
 * This exception is used to indicate that validation rules have been violated during data processing.
 * It carries a list of validation errors that describe the specific failures.
 *
 * @throws ValidationException when validation fails
 */
class ValidationException extends \Exception
{
    private array $errors;

    /**
     * Initializes a new instance of the class with validation errors, an optional error message, and an optional error code.
     *
     * @param array $errors list of validation errors encountered during processing
     * @param string $message Optional error message to include in the exception. Defaults to "Validation failed".
     * @param int $code Optional HTTP error code. Defaults to 422.
     *
     * @return void
     */
    public function __construct(array $errors, string $message = 'Validation failed', int $code = 422)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    /**
     * Returns an array of error messages or validation failures associated with the object.
     *
     * @return array List of error messages as strings. May be empty if no errors exist.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Returns the structured API error response.
     * Transforms the existing errors array (format: ['field.path' => ['msg1', 'msg2']])
     * into an ApiErrorResponse with ErrorMessage[] DTOs.
     */
    public function getApiErrorResponse(): ApiErrorResponse
    {
        $errorMessages = [];
        foreach ($this->errors as $field => $messages) {
            if (is_array($messages)) {
                foreach ($messages as $message) {
                    $errorMessages[] = new ErrorMessage($message, $field);
                }
            } else {
                $errorMessages[] = new ErrorMessage($messages, is_string($field) ? $field : null);
            }
        }

        return new ApiErrorResponse($this->getMessage(), $errorMessages);
    }
}
