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

abstract class PointField
{
    public const OPERATOR = 'operator';
    public const OPERATOR_PRETTY = 'operatorPretty';
    public const BRAND = 'brand';
    public const BRAND_PRETTY = 'brandPretty';
    public const CODE = 'code';
    public const STREET = 'street';
    public const CITY = 'city';
    public const POSTAL_CODE = 'postalCode';
    public const LATITUDE = 'latitude';
    public const LONGITUDE = 'longitude';
    public const COD = 'cod';
    public const DESCRIPTION = 'description';
    public const AVAILABLE = 'available';
    public const POINT_TYPES = 'pointTypes';
    public const POSTING_POINT = 'postingPoint';
    public const DELIVERY_POINT = 'deliveryPoint';
    public const OPENING_HOURS_MAP = 'openingHoursMap';
}
