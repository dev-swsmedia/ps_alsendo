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

class Pickup extends Request
{
    public ?string $type = null;
    public $date;
    public ?string $hoursFrom = null;
    public ?string $hoursTo = null;
    /** @var int|string|null pickupBranch for Zaslat (1 = ship via pickup point) */
    public $pickupBranch;
}
