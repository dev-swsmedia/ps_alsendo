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

class ErrorMessage
{
    /** @var string|null Field path (null if error is not field-specific) */
    public ?string $field;

    /** @var string Error message */
    public string $message;

    public function __construct(string $message, string $field = null)
    {
        $this->message = $message;
        $this->field = $field;
    }
}
