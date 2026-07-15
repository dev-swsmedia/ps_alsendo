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
 * Upgrade hook for the 1.6.1 release.
 *
 * 1.6.1 is a follow-up validation pass: tightens type compatibility, removes
 * remaining unused code paths and fixes Smarty escape directives. No database
 * schema changes.
 *
 * @param Module $module the module instance being upgraded
 *
 * @return bool true on success
 */
function upgrade_module_1_6_1($module)
{
    return true;
}
