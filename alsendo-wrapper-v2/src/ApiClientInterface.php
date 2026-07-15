<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Model\Contact;
use Alsendo\AlsendoWrapper\Model\Order\OrderRequest;
use Alsendo\AlsendoWrapper\Model\Order\OrderResponse;
use Alsendo\AlsendoWrapper\Model\Order\SendOrderResponse;
use Alsendo\AlsendoWrapper\Model\Order\Valuation\OrderValuationResponse;
use Alsendo\AlsendoWrapper\Model\Service\ServiceStructure;
use Alsendo\AlsendoWrapper\Model\TurnIn\TurnIn;
use Alsendo\AlsendoWrapper\Model\WayBill\WayBill;

interface ApiClientInterface
{
    public function getServiceStructure(): ServiceStructure;

    public function getOrderValuation(OrderRequest $order): OrderValuationResponse;

    public function sendOrder(OrderRequest $order): SendOrderResponse;

    public function getOrder(string $orderId): OrderResponse;

    /**
     * @param string[] $orderIds
     *
     * @return OrderResponse[]
     */
    public function getOrders(array $orderIds): array;

    public function getWayBill(string $orderId): WayBill;

    public function cancelOrder(string $orderId): bool;

    /**
     * @param string[] $orderIds
     */
    public function getTurnInList(array $orderIds): TurnIn;

    /**
     * @return Contact[]
     */
    public function getContactList(): array;

    public function setPlatformName(string $platformName): void;
}
