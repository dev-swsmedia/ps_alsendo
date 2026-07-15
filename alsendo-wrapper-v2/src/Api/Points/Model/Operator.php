<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Points\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class Operator
{
    public const INPOST = 'INPOST';
    public const RUCH = 'RUCH';
    public const POCZTA = 'POCZTA';
    public const DPD = 'DPD';
    public const UPS = 'UPS';
    public const FEDEX = 'FEDEX';
}
