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

use Alsendo\AlsendoWrapper\Model\Contact;
use Alsendo\AlsendoWrapper\Model\Pickup;

class OrderResponse
{
    public $id;
    public $supplier;
    public $serviceId;
    public $serviceName;
    public $waybillNumber;
    public Pickup $pickup;
    public $pickupNumber;
    public $trackingUrl;
    public $status;
    public $shipmentsCount;
    public array $shipments;
    public $content;
    public $comment;
    public Contact $sender;
    public Contact $receiver;
    public $created;
    public $delivered;
    public $price;
    public $priceVar;
    public $priceGross;
    public $cod;
    public $declarationValue;

    public function __construct()
    {
        $this->pickup = new Pickup();
        $this->shipments = [];
        $this->sender = new Contact();
        $this->receiver = new Contact();
    }
}
