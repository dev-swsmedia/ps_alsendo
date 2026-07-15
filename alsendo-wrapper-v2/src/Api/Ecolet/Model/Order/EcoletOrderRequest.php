<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Ecolet\Model\Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Api\Ecolet\Model\Parcel;
use Alsendo\AlsendoWrapper\Model\Contact;
use Alsendo\AlsendoWrapper\Model\Request;

/**
 * This class represents an order request in the Ecolet system.
 */
class EcoletOrderRequest extends Request
{
    /**
     * @var Contact the sender's contact information
     */
    public Contact $sender;

    /**
     * @var Contact the receiver's contact information
     */
    public Contact $receiver;

    /**
     * @var Parcel the parcel details associated with the order
     */
    public Parcel $parcel;

    /**
     * @var array|null a list of additional services requested for the order
     */
    public ?array $additionalServices;

    /**
     * @var Courier the courier responsible for delivering the order
     */
    public Courier $courier;
    /**
     * @var array|null a list of parcels associated with the order
     */
    public ?array $parcels;

    // fix Ecolet source.
    public string $source;
}
