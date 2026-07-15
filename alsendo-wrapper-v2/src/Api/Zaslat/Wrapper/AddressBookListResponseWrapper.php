<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Zaslat\Wrapper;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Api\Zaslat\Model\AddressBookListResponse;
use Alsendo\AlsendoWrapper\Api\Zaslat\Model\ZaslatAddressBook;
use Alsendo\AlsendoWrapper\Json;

class AddressBookListResponseWrapper
{
    public static function wrap(array $data): AddressBookListResponse
    {
        $addresses = [];
        foreach ($data as $addressId => $contactData) {
            if (is_array($contactData)) {
                $addresses[(string) $addressId] = Json::mapArrayToObject($contactData, ZaslatAddressBook::class);
            }
        }

        return new AddressBookListResponse($addresses);
    }
}
