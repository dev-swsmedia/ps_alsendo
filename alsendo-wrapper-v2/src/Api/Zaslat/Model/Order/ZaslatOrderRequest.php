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

use Alsendo\AlsendoWrapper\Model\Contact;
use Alsendo\AlsendoWrapper\Model\Request;

class ZaslatOrderRequest extends Request
{
    // Optional field
    public ?string $currency = null;
    // Optional field
    public ?string $paymentType = null;
    /**
     * @var ZaslatShipment[]
     */
    public array $shipments = [];
    // Optional field
    public Contact $payer;
    // Optional field
    public ?string $voucher;
    public ?bool $pickupBranch = null;
}
