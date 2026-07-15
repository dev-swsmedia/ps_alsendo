<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Zaslat\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AddressBookListResponse
{
    /** @var array<string, ZaslatAddressBook> */
    public array $addresses = [];

    public function __construct(array $addresses = [])
    {
        $this->addresses = $addresses;
    }

    public function get(string $addressId): ?ZaslatAddressBook
    {
        return $this->addresses[$addressId] ?? null;
    }
}
