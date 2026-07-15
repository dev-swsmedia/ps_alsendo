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

class Address extends Request
{
    public Contact $sender;
    public Contact $receiver;

    public function __construct(Contact $sender = null, Contact $receiver = null)
    {
        $this->sender = $sender;
        $this->receiver = $receiver;
    }
}
