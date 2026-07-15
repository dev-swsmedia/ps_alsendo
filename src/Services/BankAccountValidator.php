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

class BankAccountValidator
{
    public static function isValid(string $account, string $country = ''): bool
    {
        $cleaned = trim(str_replace(' ', '', $account));

        if (empty($cleaned)) {
            return false;
        }

        $country = strtoupper($country);

        if ($country === 'PL' && self::isValidPolishLocal($cleaned)) {
            return true;
        }
        if ($country === 'CZ' && self::isValidCzechLocal($cleaned)) {
            return true;
        }
        if ($country === 'RO' && self::isValidRomanianIban($cleaned)) {
            return true;
        }

        if (empty($country)) {
            if (self::isValidPolishLocal($cleaned) || self::isValidCzechLocal($cleaned) || self::isValidRomanianIban($cleaned)) {
                return true;
            }
        }

        return self::isValidIban($cleaned);
    }

    private static function isValidPolishLocal(string $account): bool
    {
        $upper = strtoupper($account);

        if (strpos($upper, 'PL') === 0) {
            $upper = substr($upper, 2);
        }

        return preg_match('/^\d{26}$/', $upper) === 1;
    }

    public static function isValidCzechLocal(string $account): bool
    {
        return preg_match('/^\d{0,6}-?\d{1,10}\/\d{4}$/', $account) === 1;
    }

    private static function isValidRomanianIban(string $account): bool
    {
        $upper = strtoupper($account);

        return preg_match('/^RO\d{2}[A-Z]{4}[A-Z0-9]{16}$/', $upper) === 1;
    }

    public static function isValidCzechAccountPart(string $account): bool
    {
        $cleaned = trim(str_replace(' ', '', $account));

        return preg_match('/^\d{0,6}-?\d{1,10}$/', $cleaned) === 1;
    }

    public static function isValidCzechBankCode(string $code): bool
    {
        $cleaned = trim($code);

        return preg_match('/^\d{4}$/', $cleaned) === 1;
    }

    public static function isValidIban(string $account): bool
    {
        $iban = strtoupper($account);

        if (!preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{1,30}$/', $iban)) {
            return false;
        }

        $moved = substr($iban, 4) . substr($iban, 0, 4);
        $converted = '';
        for ($i = 0; $i < strlen($moved); ++$i) {
            $ch = $moved[$i];
            $converted .= ctype_alpha($ch) ? (ord($ch) - 55) : $ch;
        }

        $remainder = 0;
        $len = strlen($converted);
        for ($i = 0; $i < $len; ++$i) {
            $remainder = ($remainder * 10 + (int) $converted[$i]) % 97;
        }

        return $remainder === 1;
    }
}
