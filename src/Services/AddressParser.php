<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\Services;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AddressParser
{
    public static function parseAddressToComponents(string $address1, string $address2 = ''): array
    {
        $address1 = trim($address1);
        $address2 = trim($address2);

        $result = [
            'street' => '',
            'building_number' => '',
            'apartment_number' => '',
        ];

        if ($address1 === '') {
            return $result;
        }

        if (preg_match('/^(.+?)\s+(\d+\w{0,3})\s*\/\s*(\S+)\s*$/u', $address1, $m)) {
            $result['street'] = trim($m[1]);
            $result['building_number'] = $m[2];
            $result['apartment_number'] = $m[3];

            return $result;
        }

        if (preg_match('/^(.+?)\s+(\d+\w{0,3})\s+m\.?\s+(\S+)\s*$/iu', $address1, $m)) {
            $result['street'] = trim($m[1]);
            $result['building_number'] = $m[2];
            $result['apartment_number'] = $m[3];

            return $result;
        }

        if (preg_match('/^(.+?)\s+(\d+\w{0,3})\s*$/u', $address1, $m)) {
            $result['street'] = trim($m[1]);
            $result['building_number'] = $m[2];
            $result['apartment_number'] = $address2;

            return $result;
        }

        $result['street'] = $address1;
        $result['apartment_number'] = $address2;

        return $result;
    }

    public static function parseRomanianComponents(string $text): array
    {
        $result = ['block' => '', 'entrance' => '', 'floor' => '', 'flat' => ''];
        if (empty($text)) {
            return $result;
        }
        if (preg_match('/\b(?:bl(?:oc)?|block)\.?\s*([A-Za-z0-9]+)/iu', $text, $m)) {
            $result['block'] = $m[1];
        }
        if (preg_match('/\b(?:sc(?:ara)?|entrance)\.?\s*([A-Za-z0-9]+)/iu', $text, $m)) {
            $result['entrance'] = $m[1];
        }
        if (preg_match('/\b(?:et(?:aj)?|floor)\.?\s*([A-Za-z0-9]+)/iu', $text, $m)) {
            $result['floor'] = $m[1];
        }
        if (preg_match('/\b(?:ap(?:artament)?|apt)\.?\s*([A-Za-z0-9]+)/iu', $text, $m)) {
            $result['flat'] = $m[1];
        }

        return $result;
    }
}
