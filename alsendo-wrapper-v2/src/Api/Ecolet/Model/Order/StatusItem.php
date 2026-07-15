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

use Alsendo\AlsendoWrapper\Json;

class StatusItem
{
    public ?string $name = null;
    public ?string $realName = null;
    public ?\DateTimeImmutable $createdAt = null;

    public static function getPropertyTypeMap(): array
    {
        return [
            'name' => ['mappedTo' => 'name'],
            'real_name' => ['mappedTo' => 'realName'],
            'created_at' => ['mappedTo' => 'createdAt', 'convert' => Json::DATE_TIME],
        ];
    }
}
