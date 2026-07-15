<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Cod
{
    public ?string $amount;
    public ?string $bankaccount;
    public ?string $currency;
    public ?string $bankCode;
    public ?\DateTimeImmutable $receivedAt = null;

    public ?\DateTimeImmutable $returnedAt = null;
}
