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
 * Upgrade hook for the 1.7.3 release.
 *
 * Final validation pass cleanup:
 * - disable PHP-CS-Fixer global_namespace_import (was conflicting with
 *   PrestaShop validator's no_unused_imports for PHPDoc-only classes)
 * - definitively remove `use X;` statements referenced only in PHPDoc
 *   (8 wrapper-v2/src files), prefix \X in PHPDoc instead
 * - reorder PHPDoc types null|X to X|null (19 files)
 * - re-run CS-Fixer (19 files reformatted)
 * No database schema changes.
 *
 * @param Module $module the module instance being upgraded
 *
 * @return bool true on success
 */
function upgrade_module_1_7_3($module)
{
    return true;
}
