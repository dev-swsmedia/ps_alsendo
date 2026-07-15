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
 * Upgrade hook for the 1.6.2 release.
 *
 * 1.6.2 is a third validation pass: extended PHP-CS-Fixer ruleset
 * (phpdoc_*, global_namespace_import, increment_style → pre, etc.) and
 * a single typing fix in resolvePackageContent (cast invoice number to string).
 * No database schema changes.
 *
 * @param Module $module the module instance being upgraded
 *
 * @return bool true on success
 */
function upgrade_module_1_6_2($module)
{
    return true;
}
