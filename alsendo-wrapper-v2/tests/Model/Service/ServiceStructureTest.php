<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
namespace Alsendo\AlsendoWrapper\Tests\Model\Service;

use Alsendo\AlsendoWrapper\Model\Service\Service;
use Alsendo\AlsendoWrapper\Model\Service\ServiceStructure;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ServiceStructureTest extends ServiceStructure
{
    public static function getPropertyTypeMap(): array
    {
        return [
            'services' => ['mappedTo' => 'services', 'type' => Service::class],
        ];
    }
}
