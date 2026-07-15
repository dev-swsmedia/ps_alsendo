<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Apaczka\Wrapper;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Model\Service\PackageType;
use Alsendo\AlsendoWrapper\Model\Service\Service;
use Alsendo\AlsendoWrapper\Model\Service\ServiceStructure;

class ServiceStructureWrapper
{
    public static function wrap(array $data): ServiceStructure
    {
        $serviceStructure = new ServiceStructure();

        if (!empty($data['services'])) {
            foreach ($data['services'] as $serviceData) {
                $service = new Service();
                $service->externalId = (string) ($serviceData['service_id'] ?? '');
                $service->name = (string) ($serviceData['name'] ?? '');
                $service->supplier = $serviceData['supplier'] ?? null;
                $service->toPoint = self::resolveToPoint($serviceData);
                $service->comment = !empty($serviceData['delivery_time']) ? $serviceData['delivery_time'] : null;
                $serviceStructure->services[] = $service;
            }
        }

        if (!empty($data['package_type'])) {
            foreach ($data['package_type'] as $pt) {
                $serviceStructure->packageTypes[] = new PackageType(
                    (string) ($pt['type'] ?? ''),
                    (string) ($pt['description'] ?? $pt['desc'] ?? '')
                );
            }
        }

        return $serviceStructure;
    }

    private static function resolveToPoint(array $serviceData): ?bool
    {
        $doorToPoint = $serviceData['door_to_point'] ?? null;
        $pointToPoint = $serviceData['point_to_point'] ?? null;

        if ($doorToPoint === null && $pointToPoint === null) {
            return null;
        }

        return (bool) $doorToPoint || (bool) $pointToPoint;
    }
}
