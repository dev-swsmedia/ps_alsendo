<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Upgrade hook for the 1.6.0 release.
 *
 * 1.6.0 covers a PrestaShop Addons validation pass: code-style refactor,
 * compatibility/PHPStan fixes, license headers, _PS_VERSION_ guards and
 * index.php files. No database schema changes are required.
 *
 * @param Module $module the module instance being upgraded
 *
 * @return bool true on success
 */
function upgrade_module_1_6_0($module)
{
    return true;
}
