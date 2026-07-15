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

class ZaslatAddressBook
{
    public int $id = 0;
    public string $company = '';
    public string $firstname = '';
    public string $surname = '';
    public string $street = '';
    public string $city = '';
    public string $zip = '';
    public string $country = '';
    public string $phone = '';
    public string $email = '';
}
