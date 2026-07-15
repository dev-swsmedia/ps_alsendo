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

class AlsendoCarrier extends CarrierModule
{
    public $id_carrier;

    public function __construct()
    {
    }

    public function install()
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }

    public function disable($forceAll = false)
    {
        return true;
    }

    public function enable($forceAll = false)
    {
        return true;
    }

    public function getPackageShippingCost($cart, $shipping_cost, $products)
    {
        return (float) $shipping_cost;
    }

    public function getOrderShippingCost($cart, $shipping_cost)
    {
        return (float) $shipping_cost;
    }

    public function getOrderShippingCostExternal($cart)
    {
        return (float) 12;
    }

    public function installModuleCarrier($carrierProperties)
    {
        try {
            $carrier = new Carrier();
            $carrier->hydrate($carrierProperties);

            if (!$carrier->add()) {
                return false;
            }

            $id_carrier = (int) $carrier->id;

            $groupIDs = $this->getGroupsIDs();
            if (!empty($groupIDs)) {
                $carrier->setGroups($groupIDs);
            }

            $rangePrices = [];
            if (isset($carrierProperties['ranges']) && is_array($carrierProperties['ranges'])) {
                foreach ($carrierProperties['ranges'] as $range) {
                    $rangeWeight = new RangeWeight();
                    $rangeWeight->hydrate([
                        'id_carrier' => $id_carrier,
                        'delimiter1' => (float) $range['delimiter1'],
                        'delimiter2' => (float) $range['delimiter2'],
                    ]);
                    $rangeWeight->add();

                    $rangePrices[] = [
                        'id_range_weight' => $rangeWeight->id,
                        'price' => $range['price'],
                    ];
                }
            }

            $carrier->setTaxRulesGroup(0, true);

            $zones = Zone::getZones(true);
            foreach ($zones as $zone) {
                $id_zone = (int) $zone['id_zone'];

                $exists = (int) Db::getInstance()->getValue(
                    'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'carrier_zone`
                     WHERE `id_carrier` = ' . $id_carrier . '
                     AND `id_zone` = ' . $id_zone
                );

                if (!$exists) {
                    $carrier->addZone($id_zone);
                }
            }

            foreach ($rangePrices as $rangePrice) {
                $data = ['price' => (float) $rangePrice['price']];
                $where = '`id_range_weight` = ' . (int) $rangePrice['id_range_weight'];
                Db::getInstance()->update('delivery', $data, $where);
            }

            if (!empty($carrierProperties['img']) && file_exists($carrierProperties['img'])) {
                @copy($carrierProperties['img'], _PS_SHIP_IMG_DIR_ . '/' . $id_carrier . '.jpg');
            }

            $this->setupCarrierPaymentRestrictions($id_carrier);

            return $id_carrier;
        } catch (Exception $e) {
            error_log('[Alsendo] Error creating carrier: ' . $e->getMessage());

            return false;
        }
    }

    private function setupCarrierPaymentRestrictions($id_carrier)
    {
        try {
            $id_carrier = (int) $id_carrier;
            $id_shop = (int) Context::getContext()->shop->id;

            Db::getInstance()->execute(
                'DELETE FROM `' . _DB_PREFIX_ . 'module_carrier`
             WHERE `id_reference` = ' . $id_carrier
            );

            $modules = Module::getPaymentModules();

            if (!is_array($modules) || empty($modules)) {
                return true;
            }

            foreach ($modules as $module) {
                $id_module = (int) $module['id_module'];

                Db::getInstance()->insert('module_carrier', [
                    'id_module' => $id_module,
                    'id_reference' => $id_carrier,
                    'id_shop' => $id_shop,
                ], true);
            }

            return true;
        } catch (Exception $e) {
            error_log('[Alsendo] Error setting payment restrictions: ' . $e->getMessage());

            return false;
        }
    }

    public static function deleteCarrier($id_carrier)
    {
        $id_carrier = (int) $id_carrier;

        if ($id_carrier <= 0) {
            return false;
        }

        try {
            $db = Db::getInstance();
            $prefix = _DB_PREFIX_;

            $rangeWeights = $db->executeS(
                'SELECT `id_range_weight` FROM `' . $prefix . 'range_weight`
             WHERE `id_carrier` = ' . $id_carrier
            );

            $rangeWeightIds = [];
            if (is_array($rangeWeights)) {
                foreach ($rangeWeights as $range) {
                    $rangeWeightIds[] = (int) $range['id_range_weight'];
                }
            }

            $db->execute('DELETE FROM `' . $prefix . 'module_carrier`
                     WHERE `id_reference` = ' . $id_carrier);

            $db->execute('DELETE FROM `' . $prefix . 'carrier_zone`
                     WHERE `id_carrier` = ' . $id_carrier);

            $db->execute('DELETE FROM `' . $prefix . 'carrier_group`
                     WHERE `id_carrier` = ' . $id_carrier);

            $db->execute('DELETE FROM `' . $prefix . 'delivery`
                     WHERE `id_carrier` = ' . $id_carrier);

            $db->execute('DELETE FROM `' . $prefix . 'range_weight`
                     WHERE `id_carrier` = ' . $id_carrier);

            if (!empty($rangeWeightIds)) {
                foreach ($rangeWeightIds as $rangeWeightId) {
                    $stillUsed = $db->getValue(
                        'SELECT COUNT(*) FROM `' . $prefix . 'delivery`
                     WHERE `id_range_weight` = ' . $rangeWeightId
                    );

                    if (!$stillUsed) {
                        $db->execute('DELETE FROM `' . $prefix . 'range_price`
                             WHERE `id_range_price` IN (
                                 SELECT DISTINCT `id_range_price`
                                 FROM `' . $prefix . 'delivery`
                                 WHERE `id_range_weight` = ' . $rangeWeightId . '
                             )');
                    }
                }
            }

            $carrier = new Carrier($id_carrier);
            if (Validate::isLoadedObject($carrier)) {
                $carrier->delete();
            } else {
                $db->execute('DELETE FROM `' . $prefix . 'carrier`
                         WHERE `id_carrier` = ' . $id_carrier);
            }

            $logoPath = _PS_SHIP_IMG_DIR_ . '/' . $id_carrier . '.jpg';
            if (file_exists($logoPath)) {
                @unlink($logoPath);
            }

            return true;
        } catch (Exception $e) {
            error_log('[Alsendo] Error deleting carrier ' . $id_carrier . ': ' . $e->getMessage());

            return false;
        }
    }

    public static function getGroupsIDs()
    {
        try {
            $sql = new DbQuery();
            $sql->select('`id_group`')->from('group');
            $rows = Db::getInstance()->executeS($sql);
            $ids = [];
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $ids[] = (int) $row['id_group'];
                }
            }

            return $ids;
        } catch (Exception $e) {
            error_log('[Alsendo] Error getting group IDs: ' . $e->getMessage());

            return [];
        }
    }
}
