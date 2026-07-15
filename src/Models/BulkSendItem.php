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

class BulkSendItem
{
    public $id;
    public $batchId;
    public $orderId;
    public $status;
    public $errorType;
    public $errorMessage;
    public $alsendoOrderId;
    public $waybillNumber;
    public $trackingUrl;
    public $attemptedAt;
    public $completedAt;
    public $retryCount;

    public function __construct($batchId, $orderId, $status = 'pending')
    {
        $this->batchId = (int) $batchId;
        $this->orderId = (int) $orderId;
        $this->status = (string) $status;
        $this->errorType = null;
        $this->errorMessage = null;
        $this->alsendoOrderId = null;
        $this->waybillNumber = null;
        $this->trackingUrl = null;
        $this->attemptedAt = null;
        $this->completedAt = null;
        $this->retryCount = 0;
    }

    public function toArray()
    {
        return [
            'batchId' => $this->batchId,
            'orderId' => $this->orderId,
            'status' => $this->status,
            'errorType' => $this->errorType,
            'errorMessage' => $this->errorMessage,
            'alsendoOrderId' => $this->alsendoOrderId,
            'waybillNumber' => $this->waybillNumber,
            'trackingUrl' => $this->trackingUrl,
            'attemptedAt' => $this->attemptedAt,
            'completedAt' => $this->completedAt,
            'retryCount' => $this->retryCount,
        ];
    }
}
