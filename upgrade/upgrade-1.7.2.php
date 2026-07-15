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
 * Upgrade hook for the 1.7.2 release.
 *
 * Validation pass cleanup:
 * - alsendo.php: replace 4 remaining deprecated Tab::getIdFromClassName() calls with direct DB lookup
 * - remove unused PHPDoc-only `use` statements (validator stricter than CS-Fixer)
 * - reorder PHPDoc types: null|X -> X|null
 * - re-run PHP-CS-Fixer (26 files reformatted)
 * No database schema changes.
 *
 * @param Module $module the module instance being upgraded
 *
 * @return bool true on success
 */
function upgrade_module_1_7_2($module)
{
    return true;
}
