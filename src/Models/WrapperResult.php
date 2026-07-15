<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\Models;

if (!defined('_PS_VERSION_')) {
    exit;
}

class WrapperResult
{
    private bool $success;
    private string $message;
    private array $data;
    private ?string $error;

    public function __construct(bool $success, string $message, array $data = [], string $error = null)
    {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
        $this->error = $error;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function toArray(): array
    {
        $result = [
            'success' => $this->success,
            'message' => strip_tags($this->message),
        ];

        if ($this->success) {
            $result['data'] = $this->data;
        } else {
            $result['error'] = strip_tags($this->error ?? $this->message);
        }

        return $result;
    }
}
