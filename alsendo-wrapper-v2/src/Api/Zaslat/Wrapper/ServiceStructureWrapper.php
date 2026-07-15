<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Zaslat\Wrapper;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Model\Service\PackageType;
use Alsendo\AlsendoWrapper\Model\Service\Service;
use Alsendo\AlsendoWrapper\Model\Service\ServiceStructure;

class ServiceStructureWrapper
{
    private const TO_ADDRESS_SERVICES = [
        ['code' => 'WEDO', 'name' => 'One by Allegro', 'supplier' => 'wedo', 'door_to_door' => true, 'point_to_door' => true],
        ['code' => 'PPL', 'name' => 'PPL', 'supplier' => 'ppl', 'door_to_door' => true, 'point_to_door' => true],
        ['code' => 'GLS', 'name' => 'GLS', 'supplier' => 'gls', 'door_to_door' => true, 'point_to_door' => true],
        ['code' => 'DPD', 'name' => 'DPD', 'supplier' => 'dpd', 'door_to_door' => true, 'point_to_door' => true],
        ['code' => 'TOPTRANS', 'name' => 'TopTrans', 'supplier' => 'toptrans', 'door_to_door' => true, 'point_to_door' => null],
        ['code' => 'UPS', 'name' => 'UPS', 'supplier' => 'ups', 'door_to_door' => true, 'point_to_door' => null],
        ['code' => 'BALIKOVNA', 'name' => 'Balíkovna', 'supplier' => 'balikovna', 'door_to_door' => null, 'point_to_door' => true],
        ['code' => 'ZASILKOVNA', 'name' => 'Zásilkovna', 'supplier' => 'zasilkovna', 'door_to_door' => true, 'point_to_door' => true],
    ];

    private const TO_POINT_SERVICES = [
        ['code' => 'ZASILKOVNA', 'name' => 'Zásilkovna', 'supplier' => 'zasilkovna', 'point_to_point' => true, 'door_to_point' => null],
        ['code' => 'BALIKOVNA', 'name' => 'Balíkovna', 'supplier' => 'balikovna', 'point_to_point' => true, 'door_to_point' => null],
        ['code' => 'WEDO', 'name' => 'One by Allegro', 'supplier' => 'wedo', 'point_to_point' => true, 'door_to_point' => true],
        ['code' => 'PPL', 'name' => 'PPL', 'supplier' => 'ppl', 'point_to_point' => true, 'door_to_point' => true],
        ['code' => 'GLS', 'name' => 'GLS', 'supplier' => 'gls', 'point_to_point' => true, 'door_to_point' => true],
        ['code' => 'DPD', 'name' => 'DPD', 'supplier' => 'dpd', 'point_to_point' => true, 'door_to_point' => true],
    ];

    public static function wrap(): ServiceStructure
    {
        $serviceStructure = new ServiceStructure();

        foreach (self::TO_ADDRESS_SERVICES as $item) {
            $service = new Service();
            $service->externalId = $item['code'];
            $service->name = $item['name'];
            $service->supplier = $item['supplier'];
            $service->toPoint = false;
            $service->comment = null;
            $service->pointToPoint = null;
            $service->doorToPoint = null;
            $service->pointToDoor = $item['point_to_door'] ?? null;
            $service->doorToDoor = $item['door_to_door'] ?? null;
            $serviceStructure->services[] = $service;
        }

        foreach (self::TO_POINT_SERVICES as $item) {
            $service = new Service();
            $service->externalId = $item['code'] . '_topoint';
            $service->name = sprintf('%s (Pickup point)', $item['name']);
            $service->supplier = $item['supplier'];
            $service->toPoint = true;
            $service->comment = null;
            $service->pointToPoint = $item['point_to_point'];
            $service->doorToPoint = $item['door_to_point'];
            $service->pointToDoor = null;
            $service->doorToDoor = null;
            $serviceStructure->services[] = $service;
        }

        $serviceStructure->packageTypes = [
            new PackageType('PACZKA', 'Standardní balík'),
        ];

        return $serviceStructure;
    }
}
