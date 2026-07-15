<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Ecolet\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Model\Request;

class Parcel extends Request
{
    public $type;
    public $weight;
    public Dimension $dimensions;
    public $shape;
    public $declaredValue;
    public $amount;
    public $content;
    public $observations;
}
