<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Points\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Point
{
    public ?string $operator = null;
    public ?string $operatorPretty = null;
    public ?string $brand = null;
    public ?string $brandPretty = null;
    public ?bool $postingPoint = null;
    public ?bool $deliveryPoint = null;
    public ?bool $cod = null;
    public ?string $code = null;
    public ?string $street = null;
    public ?string $city = null;
    public ?string $postalCode = null;
    public ?float $longitude = null;
    public ?float $latitude = null;
    public ?array $openingHoursMap = null;
    public ?string $description = null;
    public ?bool $available = null;
    public ?array $pointTypes = null;
    public ?string $district = null;
    public ?string $province = null;

    // Multi-region fields (CZ/RO)
    public ?string $country = null;
    public ?string $carrier = null;
    public ?bool $isLocker = null;
    public ?float $distance = null;
    public ?int $ecoletId = null;
    public ?string $name = null;
    public ?string $address = null;

    public static function getPropertyTypeMap(): array
    {
        return [
            'openingHoursMap' => [
                'mappedTo' => 'openingHoursMap',
            ],
            'pointTypes' => [
                'mappedTo' => 'pointTypes',
            ],
        ];
    }
}
