<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Points;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Api\Points\Model\Point;
use Alsendo\AlsendoWrapper\Api\Points\Model\PointSearchRequest;

interface PointServiceInterface
{
    /**
     * @return Point[]
     */
    public function search(PointSearchRequest $request): array;

    /**
     * @param string $operator Operator constant value
     * @param string $code Point code
     */
    public function getPoint(string $operator, string $code): Point;
}
