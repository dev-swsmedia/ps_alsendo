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

class Contact extends Request
{
    public ?string $countryCode = null;
    public ?string $name = null;
    public ?string $line1 = null;
    public ?string $line2 = null;
    public ?string $postalCode = null;
    public ?string $stateCode = null;
    public ?string $city = null;
    public ?int $isResidential = null;
    public ?string $contactPerson = null;
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $foreignAddressId = null;

    // Field in Zaslat, Ecolet
    public ?int $id = null;

    /**
     * @var string|null Country only in Ecolet mapped and strlower from
     */
    public ?string $country = null;
    public ?int $localityId = null;        // Only in Ecolet
    public ?string $streetName = null;     // Only in Ecolet
    public ?string $streetNumber = null;   // Only in Ecolet
    public ?string $block = null;         // Only in Ecolet
    public ?string $entrance = null;      // Only in Ecolet
    public ?string $floor = null;         // Only in Ecolet
    public ?string $flat = null;          // Only in Ecolet
    public ?bool $hasMapPoint = null;      // Only in Ecolet
    public $mapPointId;       // Used in Ecolet, Zaslat
    public ?string $mapPointName = null;
    /**
     * @var string|null County only in Ecolet is mapped form
     */
    public ?string $county = null;
    /**
     * @var string|null Locality only in Ecolet is mapped form
     */
    public ?string $locality = null;
    public ?string $company = null;

    public function __construct(
        string $countryCode = null,
        string $name = null,
        string $line1 = null,
        string $line2 = null,
        string $postalCode = null,
        string $stateCode = null,
        string $city = null,
        int $isResidential = null,
        string $contactPerson = null,
        string $email = null,
        string $phone = null,
        string $foreignAddressId = null,
        int $localityId = null,
        string $streetName = null,
        string $streetNumber = null,
        string $block = null,
        string $entrance = null,
        string $floor = null,
        string $flat = null,
        bool $hasMapPoint = null,
        $mapPointId = null
    ) {
        $this->countryCode = $countryCode;
        $this->name = $name;
        $this->line1 = $line1;
        $this->line2 = $line2;
        $this->postalCode = $postalCode;
        $this->stateCode = $stateCode;
        $this->city = $city;
        $this->isResidential = $isResidential;
        $this->contactPerson = $contactPerson;
        $this->email = $email;
        $this->phone = $phone;
        $this->foreignAddressId = $foreignAddressId;

        // Additional fields only in Ecolet
        $this->localityId = $localityId;
        $this->streetName = $streetName;
        $this->streetNumber = $streetNumber;
        $this->block = $block;
        $this->entrance = $entrance;
        $this->floor = $floor;
        $this->flat = $flat;
        $this->hasMapPoint = $hasMapPoint;
        $this->mapPointId = $mapPointId;
    }
}
