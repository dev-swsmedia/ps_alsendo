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

use Alsendo\AlsendoWrapper\Api\Ecolet\Model\AdditionalService;
use Alsendo\AlsendoWrapper\Model\Address;
use Alsendo\AlsendoWrapper\Model\Cod;
use Alsendo\AlsendoWrapper\Model\Notification;
use Alsendo\AlsendoWrapper\Model\Pickup;
use Alsendo\AlsendoWrapper\Model\Request;
use Alsendo\AlsendoWrapper\Model\Shipment;

/**
 * Class OrderRequest
 * Represents a model of an order request based on the Alsendo API Model.
 *
 * @property int|string $serviceId
 *                                 The service ID, which can be an integer for Apaczka and Zaslat,
 *                                 or a string (slug) for Ecolet.
 * @property Address $address
 *                            The address associated with the order.
 * @property ?array $option
 *                          Optional array of additional options.
 * @property Notification $notification
 *                                      Notification details for the order.
 * @property int $shipmentValue
 *                              The value of the shipment.
 * @property ?Cod $cod
 *                     Cash on delivery details, if applicable.
 * @property Pickup $pickup
 *                          Pickup details for the order.
 * @property Shipment[] $shipment
 *                                Details of the shipment.
 * @property string $comment
 *                           Any additional comments for the order.
 * @property string $content
 *                           Description of the content being shipped.
 * @property ?int $isZebra
 *                         Indicator for Zebra-related functionality (optional, if not provided label follows account settings).
 * @property ?string $currency
 *                             The currency used for the order.
 * @property ?AdditionalService[] $additionalServices
 *                                                    Additional services applicable only in Ecolet.
 * @property ?int $contractId
 *                            The contract ID, applicable only in Ecolet.
 * @property ?string $paymentType
 *                                Payment type for Zaslat, applicable only in Zaslat.
 * @property ?string $reference
 *                              Self-marking of the consignment, applicable only in Zaslat
 * @property ?string $promoCode
 *                              Promo code or voucher, applicable only in Zaslat
 */
class OrderRequest extends Request
{
    public $serviceId;
    public Address $address;
    public ?array $option;
    public Notification $notification;
    public int $shipmentValue;
    public ?Cod $cod = null;
    public Pickup $pickup;
    public array $shipment;
    public ?string $comment = null;
    public ?string $content = null;
    public ?int $isZebra = null;
    public ?string $currency = null;
    public ?string $shipmentCurrency = null;

    // Used in Ecolet, Zaslat
    public ?array $additionalServices = null;
    // Only in Zaslat
    public ?string $paymentType = null;
    // Only in Zaslat
    public ?string $carrier = null;
    /**
     * @var string|null
     *                  Only in Zaslat
     *                  Optional Field
     */
    public ?string $reference = null;
    /**
     * @var string|null
     *                  Only in Zaslat
     *                  Promo code or voucher
     */
    public ?string $promoCode = null;

    // Only in Zaslat
    public ?bool $pickupRequest = null;

    /**
     * @var string|null
     *                  Define the source of the order, plugin name, service name, etc.
     *                  Optional Field
     */
    public ?string $source;

    public function toArray(array $exclude = [], bool $convertToSnakeCase = true): array
    {
        if ($this->isZebra === null) {
            $exclude[] = 'is_zebra';
        }

        return parent::toArray($exclude, $convertToSnakeCase);
    }
}
