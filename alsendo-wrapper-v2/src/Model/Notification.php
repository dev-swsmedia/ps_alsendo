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

class Notification
{
    public NotificationDetail $new;
    public NotificationDetail $sent;
    public NotificationDetail $exception;
    public NotificationDetail $delivered;

    public function __construct(
        NotificationDetail $new,
        NotificationDetail $sent,
        NotificationDetail $exception,
        NotificationDetail $delivered
    ) {
        $this->new = $new;
        $this->sent = $sent;
        $this->exception = $exception;
        $this->delivered = $delivered;
    }
}
