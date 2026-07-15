<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Model\Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SendOrderResponse
{
    public ?string $id = null;
    public ?string $status = null;
    public ?string $serviceId = null;
    public ?string $serviceName = null;
    public ?string $waybillNumber = null;
    public ?string $trackingUrl = null;
    public ?\DateTimeImmutable $created = null;
}
