<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Model\Order\Valuation;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderValuation
{
    public $serviceId;
    public ?string $carrier = null;
    public ?string $service = null;
    public ?PriceTableItem $priceTable = null;

    public ?\DateTimeImmutable $pickupDate = null;

    public ?\DateTimeImmutable $deliveryDate = null;
}
