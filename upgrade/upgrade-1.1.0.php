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

function upgrade_module_1_1_0($module)
{
    $hooks = [
        'displayAdminOrdersListTop',
        'actionObjectCarrierUpdateAfter',
        'actionCarrierUpdate',
        'actionObjectCarrierDeleteAfter',
    ];

    foreach ($hooks as $hook) {
        if (!$module->isRegisteredInHook($hook)) {
            $module->registerHook($hook);
        }
    }

    $tableName = _DB_PREFIX_ . 'alsendo_bulk_send_item';
    try {
        $tableExists = Db::getInstance()->executeS(
            "SELECT 1 FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = '" . pSQL($tableName) . "'"
        );

        if (!empty($tableExists)) {
            $result = Db::getInstance()->executeS(
                "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                 AND TABLE_NAME = '" . pSQL($tableName) . "'
                 AND COLUMN_NAME = 'status'"
            );

            if ($result && isset($result[0]['COLUMN_TYPE'])) {
                if (strpos($result[0]['COLUMN_TYPE'], "'cancelled'") === false) {
                    Db::getInstance()->execute(
                        'ALTER TABLE `' . pSQL($tableName) . "`
                         MODIFY `status` ENUM('pending', 'processing', 'success', 'failed', 'cancelled') DEFAULT 'pending'"
                    );
                }
            }
        }
    } catch (Exception $e) {
        error_log('Alsendo upgrade 1.1.0: bulk_send_item migration error: ' . $e->getMessage());
    }

    $shipmentTable = _DB_PREFIX_ . 'alsendo_order_shipment';
    try {
        $colCheck = Db::getInstance()->executeS(
            "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = '" . pSQL($shipmentTable) . "'
             AND COLUMN_NAME = 'previous_order_state'"
        );
        if (empty($colCheck)) {
            Db::getInstance()->execute(
                'ALTER TABLE `' . pSQL($shipmentTable) . '`
                 ADD COLUMN `previous_order_state` INT(11) DEFAULT NULL AFTER `tracking_url`'
            );
        }
    } catch (Exception $e) {
        error_log('Alsendo upgrade 1.1.0: shipment migration error: ' . $e->getMessage());
    }

    if (!Configuration::get('ALSENDO_OS_PREPARING')) {
        $module->installOrderStatus();
    }

    Db::getInstance()->execute(
        'UPDATE `' . _DB_PREFIX_ . "tab` SET `enabled` = 1 WHERE `module` = 'alsendo'"
    );

    $hiddenTabs = [
        'AdminAlsendoOrder' => 'Order Shipment',
        'AdminAlsendoBulkSend' => 'Bulk Send',
        'AdminAlsendoShippingConfiguration' => 'Shipping Config',
        'AdminAlsendoPointTest' => 'Point Test',
    ];

    // Direct DB lookup avoids Tab::getIdFromClassName() which is deprecated since PS 1.7.1.
    $parentId = (int) Db::getInstance()->getValue(
        'SELECT `id_tab` FROM `' . _DB_PREFIX_ . 'tab` WHERE `class_name` = "AdminAlsendo"'
    );
    if ($parentId) {
        foreach ($hiddenTabs as $className => $defaultName) {
            $existingId = (int) Db::getInstance()->getValue(
                'SELECT `id_tab` FROM `' . _DB_PREFIX_ . 'tab` WHERE `class_name` = "' . pSQL($className) . '"'
            );
            if ($existingId) {
                Db::getInstance()->update('tab', ['active' => 0, 'enabled' => 1], 'id_tab = ' . $existingId);
            } else {
                $tab = new Tab();
                $tab->active = false;
                $tab->enabled = true;
                $tab->class_name = $className;
                $tab->id_parent = $parentId;
                $tab->module = 'alsendo';
                foreach (Language::getLanguages(true) as $lang) {
                    $tab->name[$lang['id_lang']] = $defaultName;
                }
                $tab->add();
            }
        }
    }

    return true;
}
