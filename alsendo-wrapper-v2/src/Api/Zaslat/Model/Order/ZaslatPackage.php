<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Zaslat\Model\Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ZaslatPackage
{
    public int $weight = 0;
    public int $width = 0;
    public int $height = 0;
    public int $length = 0;
}
