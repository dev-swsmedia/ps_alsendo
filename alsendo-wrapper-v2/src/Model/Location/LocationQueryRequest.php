<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Model\Location;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Model\Request;

/**
 * Request object used to query geographic location information based on address or location details.
 */
class LocationQueryRequest extends Request
{
    public ?string $address = null;
    public ?string $city = null;
    public ?string $zip = null;
    public ?string $country = null;
    public ?string $countryCode = null;
    public ?string $state = null;
    public ?string $county = null;
}
