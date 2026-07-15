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

class Shipment extends Request
{
    /**
     * @var int|null
     */
    public ?int $dimension1 = null;
    /**
     * @var int|null
     */
    public ?int $dimension2 = null;
    /**
     * @var int|null
     */
    public ?int $dimension3 = null;
    /**
     * @var int|null
     */
    public ?int $weight = null;
    /**
     * @var int|null
     */
    public ?int $weightBillable = null;
    /**
     * @var string|null
     */
    public ?string $content = null;
    /**
     * @var string|null
     */
    public ?string $comment = null;
    /**
     * @var string|null
     */
    public ?string $wayBillNumber = null;
    /**
     * @var int|null
     */
    public ?int $isNstd = null;
    /**
     * @var string|null
     */
    public ?string $shipmentTypeCode = null;
    /**
     * @var array|null
     */
    public ?array $customsData = null;
    /**
     * @var string|null
     */
    public ?string $price = null;
    /**
     * @var string|null
     */
    public ?string $priceVat = null;
    /**
     * @var string|null
     */
    public ?string $priceGross = null;

    // Additional fields only in Ecolet
    /**
     * @var int|null Amount
     */
    public ?int $amount = null;
    /**
     * @var string|null Observations
     */
    public ?string $observations = null;
    /**
     * @var string|null Shape
     */
    public ?string $shape = null;
    /**
     * @var string|null Declared value
     */
    public ?string $declaredValue = null;
    /**
     * @var string|null Status of the shipment
     */
    public ?string $status = null;

    /**
     * @var int|null For Zaslat: 1 = ship via pickup point
     */
    public ?int $pickupBranch = null;
}
