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
 * Upgrade hook for the 1.7.0 release.
 *
 * Internal restructuring: composer-managed third-party libraries previously
 * bundled in alsendo-wrapper-v2/vendor/ have been relocated to the module
 * root (alsendo/vendor/). Autoload paths in plugin code were updated. The
 * Alsendo wrapper sources still live at alsendo-wrapper-v2/src/.
 *
 * No database schema changes. No public API changes for installed shops.
 *
 * Old vendor folder (if upgrading from 1.6.x) is cleaned up by PrestaShop
 * during file replacement; we remove any leftover empty path defensively.
 *
 * @param Module $module the module instance being upgraded
 *
 * @return bool true on success
 */
function upgrade_module_1_7_0($module)
{
    $oldVendorDir = _PS_MODULE_DIR_ . 'alsendo/alsendo-wrapper-v2/vendor';
    if (is_dir($oldVendorDir)) {
        // Best-effort cleanup; failure here is not fatal — autoload no longer points there.
        @recursive_rmdir($oldVendorDir);
    }

    return true;
}

if (!function_exists('recursive_rmdir')) {
    function recursive_rmdir($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                recursive_rmdir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
