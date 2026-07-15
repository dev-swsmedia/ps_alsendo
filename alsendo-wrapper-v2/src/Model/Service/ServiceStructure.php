<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Model\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ServiceStructure
{
    /** @var Service[] */
    public array $services = [];

    /** @var PackageType[] */
    public array $packageTypes = [];
}
