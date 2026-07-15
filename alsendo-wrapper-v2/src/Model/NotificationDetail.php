<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

class NotificationDetail
{
    public int $isReceiverEmail;
    public int $isReceiverSms;
    public int $isSenderEmail;
    public int $isSenderSms;

    public function __construct(
        int $isReceiverEmail = 0,
        int $isReceiverSms = 0,
        int $isSenderEmail = 0,
        int $isSenderSms = 0
    ) {
        $this->isReceiverEmail = $isReceiverEmail;
        $this->isReceiverSms = $isReceiverSms;
        $this->isSenderEmail = $isSenderEmail;
        $this->isSenderSms = $isSenderSms;
    }
}
