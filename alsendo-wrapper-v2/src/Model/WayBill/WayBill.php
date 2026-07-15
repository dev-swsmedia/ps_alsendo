<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Model\WayBill;

if (!defined('_PS_VERSION_')) {
    exit;
}

class WayBill
{
    public string $waybill;

    public ?string $type = null;

    /**
     * Returns the type of the waybill.
     *
     * @return string the type of the waybill
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns the waybill file.
     *
     * @return string the waybill file as a string
     */
    public function getWaybill(): string
    {
        return $this->waybill;
    }
}
