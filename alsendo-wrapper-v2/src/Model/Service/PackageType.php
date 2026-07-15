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

class PackageType
{
    public string $type;
    public string $desc;

    public function __construct(string $type = '', string $desc = '')
    {
        $this->type = $type;
        $this->desc = $desc;
    }
}
