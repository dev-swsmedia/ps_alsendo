<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Zaslat\Model\Rates;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Api\Zaslat\Model\ZaslatContact;
use Alsendo\AlsendoWrapper\Model\Request;

class RatesGetRequest extends Request
{
    public ?string $type = null;
    public ?string $deliveryBranch = null;
    public ?string $pickupBranch = null;
    public ?string $carrier = null;
    public ?string $currency = null;
    public ?bool $pickupRequest = null;
    public ?string $pickupDate = null;
    public ?ZaslatContact $from = null;
    public ?ZaslatContact $to = null;
    public array $packages = [];
    public array $services = [];
}
