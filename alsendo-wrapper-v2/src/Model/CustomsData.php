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

class CustomsData
{
    public string $name;
    public string $description;
    public string $madeIn;
    public string $unitType;
    public int $unitPrice;
    public int $unitWeight;
    public int $quantity;

    public function __construct(
        string $name,
        string $description,
        string $madeIn,
        string $unitType,
        int $unitPrice,
        int $unitWeight,
        int $quantity
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->madeIn = $madeIn;
        $this->unitType = $unitType;
        $this->unitPrice = $unitPrice;
        $this->unitWeight = $unitWeight;
        $this->quantity = $quantity;
    }
}
