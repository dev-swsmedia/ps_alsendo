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

use Alsendo\AlsendoWrapper\Model\Request;

class ZaslatContact extends Request
{
    public $id;
    public $firstname;
    public $surname;
    public $company;
    public $street;
    public $city;
    public $zip;
    public $country;
    public $phone;
    public $email;

    public function toArray(array $exclude = [], bool $convertToSnakeCase = true): array
    {
        $result = parent::toArray($exclude, $convertToSnakeCase);

        return array_filter($result, fn ($v) => $v !== null);
    }
}
