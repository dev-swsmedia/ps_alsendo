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

/**
 * Service details from Ecolet API order response
 * Class Service
 *
 * @property string|null $slug
 * @property string|null $fullName
 * @property string|null $courierSlug
 * @property string|null $courierName
 */
class Service
{
    public ?string $slug = null;
    public ?string $fullName = null;
    public ?string $courierSlug = null;
    public ?string $courierName = null;
}
