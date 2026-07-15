<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\DTO\Entity;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AlsendoRegionEntity
{
    public ?int $id = null;

    public ?string $code = null;

    public ?string $name = null;

    public function __construct(int $id = null, string $code = null, string $name = null)
    {
        $this->id = $id;
        $this->code = $code;
        $this->name = $name;
    }

    public static function fromConfigRegion(string $region): self
    {
        $region = strtolower($region);
        $map = [
            'pl' => ['id' => 1, 'name' => 'Poland'],
            'cz' => ['id' => 2, 'name' => 'Czechia'],
            'ro' => ['id' => 3, 'name' => 'Romania'],
        ];

        $id = $map[$region]['id'] ?? null;
        $name = $map[$region]['name'] ?? ucfirst($region);

        return new self($id, $region, $name);
    }
}
