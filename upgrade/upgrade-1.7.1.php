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
 * Upgrade hook for the 1.7.1 release.
 *
 * Validation pass cleanup:
 * - upgrade-1.1.0.php: replace deprecated Tab::getIdFromClassName() with direct DB lookup
 * - extend PHP-CS-Fixer ruleset (type_declaration_spaces, types_spaces) and re-fix 5 wrapper files
 * No database schema changes.
 *
 * @param Module $module the module instance being upgraded
 *
 * @return bool true on success
 */
function upgrade_module_1_7_1($module)
{
    return true;
}
