<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Ecolet\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Model\Request;

class AdditionalService extends Request
{
    public bool $status;
    // public ?int $amount = null;
    public ?string $amount = null;

    /**
     * @param bool $status
     * @param string|null $amount
     */
    public function __construct(bool $status, ?string $amount)
    {
        $this->status = $status;
        $this->amount = $amount;
    }
}
