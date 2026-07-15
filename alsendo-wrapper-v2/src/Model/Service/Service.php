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

class Service
{
    public string $externalId;
    public string $name;
    public ?string $supplier = null;
    public ?bool $toPoint = null;
    public ?string $comment = null;
    public ?bool $pointToPoint = null;
    public ?bool $doorToPoint = null;
    public ?bool $pointToDoor = null;
    public ?bool $doorToDoor = null;
    public ?string $logoUrl = null;
}
