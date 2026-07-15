<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\Models;

if (!defined('_PS_VERSION_')) {
    exit;
}

class BulkSendBatch
{
    public $id;
    public $reference;
    public $status;
    public $totalOrders;
    public $successfulOrders;
    public $failedOrders;
    public $createdAt;
    public $completedAt;

    public function __construct($id, $reference, $status = 'pending', $totalOrders = 0)
    {
        $this->id = (int) $id;
        $this->reference = (string) $reference;
        $this->status = (string) $status;
        $this->totalOrders = (int) $totalOrders;
        $this->successfulOrders = 0;
        $this->failedOrders = 0;
        $this->createdAt = date('Y-m-d H:i:s');
        $this->completedAt = null;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status,
            'totalOrders' => $this->totalOrders,
            'successfulOrders' => $this->successfulOrders,
            'failedOrders' => $this->failedOrders,
            'createdAt' => $this->createdAt,
            'completedAt' => $this->completedAt,
        ];
    }
}
