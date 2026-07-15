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

use Alsendo\AlsendoWrapper\Api\Zaslat\Model\ZaslatContact;
use Alsendo\AlsendoWrapper\Model\Request;

class ZaslatShipment extends Request
{
    // Optional field
    public ?string $carrier = null;
    // Optional field
    public ?int $serviceId = null;
    // Optional field
    public ?string $type = null;

    // Optional Field
    public ?bool $pickupRequest = null;

    // Optional field, required format YYYY-MM-DD
    public ?string $pickupDate = null;
    // Optional field
    // public ?int $pickupBranchLocation = null;
    public ?string $pickupBranch = null;

    // Optional Field
    public ?string $deliveryBranch = null;
    // Required field
    public ZaslatContact $from;
    public ZaslatContact $to;
    /**
     * Optional field
     *
     * @var ZaslatService[]
     */
    public array $services = [];

    /**
     * Required field
     *
     * @var ZaslatPackage[]
     */
    public array $packages = [];
    // Optional field - self-marking of the consignment
    public ?string $reference = null;
    // Optional field - note for drivers
    public ?string $note = null;

    public function toArray(array $exclude = [], bool $convertToSnakeCase = true): array
    {
        if (!$this->type) {
            $exclude[] = 'type';
        }

        return parent::toArray($exclude, $convertToSnakeCase);
    }
}
