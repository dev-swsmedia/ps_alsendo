<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Ecolet\Wrapper;

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
                if (!array_key_exists('slug', $serviceData)) {
                    continue;
                }

                $service = new Service();
                $service->externalId = (string) $serviceData['slug'];
                $service->name = (string) ($serviceData['full_name'] ?? $serviceData['name'] ?? '');
                $service->supplier = $serviceData['courier']['slug'] ?? null;
                $service->toPoint = null;
                $service->comment = null;
                $service->logoUrl = $serviceData['logo_url'] ?? null;

                $serviceStructure->services[] = $service;
            }
        }

        $serviceStructure->packageTypes = [
            new PackageType('package', 'Colet standard'),
            new PackageType('envelope', 'Plic'),
            new PackageType('pallet', 'Palet'),
        ];

        return $serviceStructure;
    }
}
