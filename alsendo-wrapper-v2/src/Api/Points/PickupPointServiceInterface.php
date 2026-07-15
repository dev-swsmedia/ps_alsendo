<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Points;

use Alsendo\AlsendoWrapper\Api\Points\Model\Point;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface PickupPointServiceInterface
{
    /**
     * Search pickup points near an address.
     *
     * @param string $address Address or city to search near
     * @param string|null $carrier Filter by carrier (e.g. 'PPL', 'INPOST', 'sameday')
     * @param int $limit Max number of results
     *
     * @return Point[]
     */
    public function search(string $address, string $carrier = null, int $limit = 20): array;
}
