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

// Do NOT register wrapper autoloader globally at top level.
// PS 1.7.x has Guzzle 5, wrapper vendor has Guzzle 7 -- loading wrapper autoloader
// here would make Guzzle 7 override PS's Guzzle 5, breaking PS internal services.
// Wrapper autoloader is loaded on-demand in WrapperService when API calls are needed.

require_once __DIR__ . '/alsendoCarrier.php';
require_once __DIR__ . '/src/Services/WrapperService.php';
require_once __DIR__ . '/src/Services/OrderDetailsService.php';
require_once __DIR__ . '/src/Services/OrderValidator.php';
require_once __DIR__ . '/src/Services/BankAccountValidator.php';
require_once __DIR__ . '/src/Services/BulkSendService.php';
require_once __DIR__ . '/src/Services/MapBridge.php';
require_once __DIR__ . '/src/DTO/OrderShipmentSubmitDTO.php';
require_once __DIR__ . '/src/Hook/CheckoutHook.php';
require_once __DIR__ . '/src/Hook/AdminOrderHook.php';

use Alsendo\Hook\AdminOrderHook;
use Alsendo\Hook\CheckoutHook;
use Alsendo\Services\WrapperService;

// PHP 7.4 polyfills for PHP 8.0 string functions
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}

class Alsendo extends Module
{
    private $carrierHandler;
    private $checkoutHook;
    private $adminOrderHook;

    public function __construct()
    {
        $this->name = 'alsendo';
        $this->tab = 'shipping_logistics';
        $this->version = '1.7.19';
        $this->author = 'Innovation Software';
        $this->bootstrap = true;
        $this->ps_versions_compliancy = ['min' => '1.7.8', 'max' => '9.99'];
        parent::__construct();

        self::bootstrapAutoloader();

        $this->displayName = $this->l('Alsendo');
        $this->description = $this->l('Shipping module for PrestaShop 1.7.8+ / 8.x.');

        try {
            $this->createTmpDirectory();
            $this->runMigrations();
            $this->clearCacheIfVersionChanged();
            $this->ensureHooksRegistered();
        } catch (Throwable $e) {
            error_log('Alsendo: constructor init error: ' . $e->getMessage());
        }
    }

    /**
     * Load the module's bundled vendor autoloader and isolate it from PrestaShop's global
     * Composer ClassLoader. Without isolation, dev dependencies shipped under vendor/
     * (nikic/php-parser v5, phpunit, fakerphp, etc.) shadow versions bundled with
     * PrestaShop 8.x — most visibly nikic/php-parser, where our v5 lacks the
     * legacy ParserFactory::create() method that PS admin still calls.
     */
    public static function bootstrapAutoloader(): void
    {
        static $done = false;
        if ($done) {
            return;
        }

        $path = __DIR__ . '/vendor/autoload.php';
        if (!file_exists($path)) {
            return;
        }

        $loader = require_once $path;

        // require_once returns 1 (not the ClassLoader) when vendor/autoload.php
        // was already required earlier in this request. In that case the
        // ClassLoader instance is still registered globally — fetch it back
        // from Composer's static registry so the blacklist below still runs.
        if (!($loader instanceof \Composer\Autoload\ClassLoader)
            && method_exists(\Composer\Autoload\ClassLoader::class, 'getRegisteredLoaders')) {
            $vendorRealPath = realpath(__DIR__ . '/vendor');
            foreach (\Composer\Autoload\ClassLoader::getRegisteredLoaders() as $registeredVendorDir => $registeredLoader) {
                if (realpath($registeredVendorDir) === $vendorRealPath) {
                    $loader = $registeredLoader;
                    break;
                }
            }
        }

        if (!($loader instanceof \Composer\Autoload\ClassLoader)) {
            return;
        }

        // Dev / transitive namespaces that must NOT leak into PrestaShop's global
        // autoloader. PhpParser is the primary offender (v5 vs PS-admin v4 API
        // mismatch). The rest are dev-only deps pulled in by phpunit/fakerphp/dotenv.
        $blacklist = [
            'PhpParser\\',
            'PHPUnit\\',
            'PharIo\\',
            'SebastianBergmann\\',
            'TheSeer\\',
            'Faker\\',
            'Dotenv\\',
            'PhpOption\\',
            'Doctrine\\Instantiator\\',
            'DeepCopy\\',
        ];
        foreach ($blacklist as $prefix) {
            $loader->setPsr4($prefix, []);
        }

        // setPsr4() only clears PSR-4 paths; the Composer-generated classmap still
        // maps PHPUnit / SebastianBergmann / TheSeer / PharIo classes to our vendor
        // and would shadow PrestaShop's bundled versions. ClassLoader has no public
        // setter for $classMap, so reach in via reflection — bounded to the blacklist,
        // safe because we know the property exists in every Composer release.
        try {
            $ref = new \ReflectionProperty(\Composer\Autoload\ClassLoader::class, 'classMap');
            $ref->setAccessible(true);
            $classMap = $ref->getValue($loader);
            if (is_array($classMap)) {
                foreach (array_keys($classMap) as $class) {
                    foreach ($blacklist as $prefix) {
                        if (strpos($class, $prefix) === 0) {
                            unset($classMap[$class]);
                            break;
                        }
                    }
                }
                $ref->setValue($loader, $classMap);
            }
        } catch (\ReflectionException $e) {
            // Classmap clean-up is best-effort; PSR-4 blacklisting above already
            // resolves the primary PhpParser conflict.
            error_log('Alsendo: classmap isolation skipped (' . $e->getMessage() . ')');
        }

        // PS 1.7.x ships Guzzle 5 in core; our wrapper requires Guzzle 7. Loading our
        // Guzzle 7 globally would break PS's CsaGuzzleBundle. Wrapper's ApiClient
        // handles both Guzzle 5 and 7 transparently via runtime detection.
        if (version_compare(_PS_VERSION_, '8.0.0', '<')) {
            $loader->setPsr4('GuzzleHttp\\', []);
            $loader->setPsr4('GuzzleHttp\\Psr7\\', []);
            $loader->setPsr4('GuzzleHttp\\Promise\\', []);
        }

        $done = true;
    }

    private function getCarrierHandler(): AlsendoCarrier
    {
        if (!$this->carrierHandler) {
            $this->carrierHandler = new AlsendoCarrier();
        }

        return $this->carrierHandler;
    }

    private function getCheckoutHook(): CheckoutHook
    {
        if (!$this->checkoutHook) {
            $this->checkoutHook = new CheckoutHook($this);
        }

        return $this->checkoutHook;
    }

    private function getAdminOrderHook(): AdminOrderHook
    {
        if (!$this->adminOrderHook) {
            $this->adminOrderHook = new AdminOrderHook($this);
        }

        return $this->adminOrderHook;
    }

    public function install()
    {
        $this->cleanupOrphanedData();

        if (!parent::install()
            || !$this->registerHook('displayAfterCarrier')
            || !$this->registerHook('actionValidateOrder')
            || !$this->registerHook('displayPaymentTop')
            || !$this->registerHook('displayAdminOrderSide')
            || !$this->registerHook('displayAdminOrderMain')
            || !$this->registerHook('displayAdminOrder')
            || !$this->registerHook('actionCarrierProcess')
            || !$this->registerHook('actionAdminControllerSetMedia')
            || !$this->registerHook('actionCarrierUpdate')
            || !$this->registerHook('actionObjectCarrierDeleteAfter')
            || !$this->registerHook('actionObjectCarrierUpdateAfter')
            || !$this->registerHook('displayAdminOrdersListTop')
            || !$this->installTabs()
            || !$this->installDb()
            || !$this->installOrderStatus()
        ) {
            return false;
        }

        $this->createTmpDirectory();
        $this->runMigrations();

        return true;
    }

    public function enable($force_all = false)
    {
        return parent::enable($force_all);
    }

    public function installOrderStatus()
    {
        $existingStatusId = Configuration::get('ALSENDO_OS_PREPARING');
        if ($existingStatusId) {
            $status = new OrderState((int) $existingStatusId);
            if (Validate::isLoadedObject($status)) {
                return true;
            }
        }

        $statusNames = [
            'pl' => 'Przygotowanie do wysyłki',
            'en' => 'Preparing for shipment',
            'cs' => 'Příprava k odeslání',
            'ro' => 'Pregătire pentru expediere',
            'sk' => 'Príprava na odoslanie',
            'de' => 'Vorbereitung zum Versand',
            'fr' => 'Préparation pour expédition',
            'hu' => 'Felkészülés a szállításra',
            'uk' => 'Підготовка до відправлення',
            'lt' => 'Ruošiama siuntimui',
            'lv' => 'Gatavošana nosūtīšanai',
        ];

        $orderState = new OrderState();
        $orderState->module_name = $this->name;
        $orderState->color = '#3498db';
        $orderState->send_email = false;
        $orderState->invoice = false;
        $orderState->delivery = false;
        $orderState->logable = true;
        $orderState->shipped = false;
        $orderState->paid = true;
        $orderState->pdf_delivery = false;
        $orderState->pdf_invoice = false;
        $orderState->deleted = false;
        $orderState->hidden = false;

        foreach (Language::getLanguages(true) as $lang) {
            $isoCode = strtolower($lang['iso_code']);
            $statusName = isset($statusNames[$isoCode]) ? $statusNames[$isoCode] : $statusNames['en'];
            $orderState->name[$lang['id_lang']] = $statusName;
            $orderState->template[$lang['id_lang']] = '';
        }

        if ($orderState->add()) {
            Configuration::updateValue('ALSENDO_OS_PREPARING', $orderState->id);

            return true;
        }

        return false;
    }

    private function uninstallOrderStatus()
    {
        $statusId = Configuration::get('ALSENDO_OS_PREPARING');
        if ($statusId) {
            $orderState = new OrderState((int) $statusId);
            if (Validate::isLoadedObject($orderState)) {
                $orderState->delete();
            }
        }

        return true;
    }

    private function createTmpDirectory()
    {
        $tmpDir = dirname(__FILE__) . '/tmp';
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0755, true);
        }
    }

    private function runMigrations()
    {
        $tableName = _DB_PREFIX_ . 'alsendo_bulk_send_item';

        try {
            $tableExists = Db::getInstance()->executeS(
                "SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                 AND TABLE_NAME = '" . pSQL($tableName) . "'"
            );

            if (empty($tableExists)) {
                return;
            }

            $result = Db::getInstance()->executeS(
                "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                 AND TABLE_NAME = '" . pSQL($tableName) . "'
                 AND COLUMN_NAME = 'status'"
            );

            if ($result && isset($result[0]['COLUMN_TYPE'])) {
                $columnType = $result[0]['COLUMN_TYPE'];
                if (strpos($columnType, "'cancelled'") === false) {
                    Db::getInstance()->execute(
                        'ALTER TABLE `' . pSQL($tableName) . "`
                         MODIFY `status` ENUM('pending', 'processing', 'success', 'failed', 'cancelled') DEFAULT 'pending'"
                    );
                }
            }

            if (!Configuration::get('ALSENDO_OS_PREPARING')) {
                $this->installOrderStatus();
            }

            $shipmentTable = _DB_PREFIX_ . 'alsendo_order_shipment';
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

            $smTable = _DB_PREFIX_ . 'alsendo_shipping_methods';
            $smTableExists = Db::getInstance()->executeS(
                "SELECT 1 FROM INFORMATION_SCHEMA.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                 AND TABLE_NAME = '" . pSQL($smTable) . "'"
            );
            if (!empty($smTableExists)) {
                $colCheck1 = Db::getInstance()->executeS(
                    "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE()
                     AND TABLE_NAME = '" . pSQL($smTable) . "'
                     AND COLUMN_NAME = 'ship_via_pickup_point'"
                );
                if (empty($colCheck1)) {
                    Db::getInstance()->execute(
                        'ALTER TABLE `' . pSQL($smTable) . '`
                         ADD COLUMN `ship_via_pickup_point` TINYINT(1) DEFAULT 0,
                         ADD COLUMN `pickup_request` TINYINT(1) DEFAULT 0'
                    );
                }
            }

            if (!$this->isRegisteredInHook('actionCarrierUpdate')) {
                $this->registerHook('actionCarrierUpdate');
            }
            if (!$this->isRegisteredInHook('actionObjectCarrierDeleteAfter')) {
                $this->registerHook('actionObjectCarrierDeleteAfter');
            }

            $requiredHooks = [
                'displayAdminOrderSide',
                'displayAdminOrderMain',
                'displayAdminOrder',
                'displayAdminOrdersListTop',
                'displayAfterCarrier',
                'actionValidateOrder',
                'displayPaymentTop',
                'actionCarrierProcess',
                'actionAdminControllerSetMedia',
                'actionObjectCarrierUpdateAfter',
            ];
            foreach ($requiredHooks as $hook) {
                if (!$this->isRegisteredInHook($hook)) {
                    $this->registerHook($hook);
                }
            }
        } catch (Exception $e) {
        }
    }

    private function installDb()
    {
        $sql_file = dirname(__FILE__) . '/install.sql';
        if (!file_exists($sql_file)) {
            return false;
        }

        $sql = file_get_contents($sql_file);
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = preg_split("/;\s*[\r\n]+/", $sql);

        foreach ($sql as $query) {
            $query = trim($query);
            if (!empty($query)) {
                if (!Db::getInstance()->execute($query)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function installTabs()
    {
        $id_parent = (int) Db::getInstance()->getValue(
            'SELECT `id_tab` FROM `' . _DB_PREFIX_ . 'tab` WHERE `class_name` = "AdminAlsendo"'
        );
        if (!$id_parent) {
            $parent = new Tab();
            $parent->active = true;
            $parent->enabled = true;
            $parent->class_name = 'AdminAlsendo';
            $parent->module = $this->name;
            $parent->id_parent = 0;
            foreach (Language::getLanguages(true) as $lang) {
                $parent->name[$lang['id_lang']] = 'Alsendo';
            }
            $parent->add();
            $id_parent = $parent->id;
        }

        $tabTranslations = [
            'AdminAlsendoModuleConfiguration' => [
                'en' => 'Configuration',
                'pl' => 'Konfiguracja',
                'cs' => 'Konfigurace',
                'ro' => 'Configurare',
            ],
            'AdminAlsendoShippingMethods' => [
                'en' => 'Shipping Methods',
                'pl' => 'Metody wysyłki',
                'cs' => 'Způsoby dopravy',
                'ro' => 'Metode de livrare',
            ],
            'AdminAlsendoOrder' => [
                'en' => 'Order Shipment',
                'pl' => 'Przesyłka',
                'cs' => 'Objednávka',
                'ro' => 'Expediere',
            ],
            'AdminAlsendoBulkSend' => [
                'en' => 'Bulk Send',
                'pl' => 'Wysyłka masowa',
                'cs' => 'Hromadné odeslání',
                'ro' => 'Trimitere în masă',
            ],
            'AdminAlsendoShippingConfiguration' => [
                'en' => 'Shipping Config',
                'pl' => 'Konfiguracja wysyłki',
                'cs' => 'Nastavení dopravy',
                'ro' => 'Configurare livrare',
            ],
            'AdminAlsendoPointTest' => [
                'en' => 'Point Test',
                'pl' => 'Test punktów',
                'cs' => 'Test bodů',
                'ro' => 'Test puncte',
            ],
        ];

        $hiddenTabs = [
            'AdminAlsendoOrder',
            'AdminAlsendoBulkSend',
            'AdminAlsendoShippingConfiguration',
            'AdminAlsendoPointTest',
        ];

        $tabs = array_keys($tabTranslations);

        foreach ($tabs as $class) {
            $isHidden = in_array($class, $hiddenTabs);

            $existing_id = (int) Db::getInstance()->getValue(
                'SELECT `id_tab` FROM `' . _DB_PREFIX_ . 'tab` WHERE `class_name` = "' . pSQL($class) . '"'
            );
            if ($existing_id) {
                $tab = new Tab($existing_id);
                $tab->enabled = true;
                if ($isHidden) {
                    $tab->active = false;
                }
                foreach (Language::getLanguages(true) as $lang) {
                    $isoCode = $lang['iso_code'];
                    $tab->name[$lang['id_lang']] = $tabTranslations[$class][$isoCode] ?? $tabTranslations[$class]['en'];
                }
                $tab->update();
                continue;
            }

            $tab = new Tab();
            $tab->active = !$isHidden;
            $tab->enabled = true;
            $tab->class_name = $class;
            $tab->id_parent = $id_parent;
            $tab->module = $this->name;
            foreach (Language::getLanguages(true) as $lang) {
                $isoCode = $lang['iso_code'];
                $tab->name[$lang['id_lang']] = $tabTranslations[$class][$isoCode] ?? $tabTranslations[$class]['en'];
            }
            $tab->add();
        }

        return true;
    }

    public function getContent()
    {
        $this->installTabs();
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminAlsendoModuleConfiguration'));
    }

    public function uninstall()
    {
        $this->uninstallCarriers();
        $this->uninstallDb();
        $this->uninstallConfig();
        $this->uninstallOrderStatus();
        $this->uninstallTabs();
        $this->uninstallHooks();
        $this->cleanupAuthorizationRoles();
        $result = parent::uninstall();
        $this->clearAllCaches();

        return $result;
    }

    private function cleanupOrphanedData()
    {
        try {
            $this->cleanupAuthorizationRoles();
            Db::getInstance()->execute(
                'DELETE FROM `' . _DB_PREFIX_ . "tab` WHERE module = 'alsendo'"
            );
        } catch (Exception $e) {
        }
    }

    private function cleanupAuthorizationRoles()
    {
        try {
            $roles = Db::getInstance()->executeS(
                'SELECT id_authorization_role FROM `' . _DB_PREFIX_ . "authorization_role`
                 WHERE slug LIKE 'ROLE_MOD_MODULE_ALSENDO_%'
                    OR slug LIKE 'ROLE_MOD_TAB_ADMINALSENDO%'"
            );
            if ($roles) {
                $ids = array_column($roles, 'id_authorization_role');
                $idList = implode(',', array_map('intval', $ids));
                Db::getInstance()->execute(
                    'DELETE FROM `' . _DB_PREFIX_ . 'access` WHERE id_authorization_role IN (' . $idList . ')'
                );
                Db::getInstance()->execute(
                    'DELETE FROM `' . _DB_PREFIX_ . 'authorization_role` WHERE id_authorization_role IN (' . $idList . ')'
                );
            }
        } catch (Exception $e) {
        }
    }

    private function uninstallCarriers()
    {
        try {
            $result = Db::getInstance()->executeS(
                'SELECT DISTINCT id_carrier FROM `' . _DB_PREFIX_ . 'alsendo_shipping_methods`'
            );

            if ($result) {
                foreach ($result as $row) {
                    $carrierId = (int) $row['id_carrier'];
                    if ($carrierId > 0) {
                        $carrier = new Carrier($carrierId);
                        if (Validate::isLoadedObject($carrier)) {
                            $carrier->delete();
                        }
                    }
                }
            }
        } catch (Exception $e) {
        }

        return true;
    }

    private function uninstallDb()
    {
        $tables = [
            'alsendo_bulk_send_item',
            'alsendo_bulk_send_batch',
            'alsendo_shipping_methods',
            'alsendo_order_details',
            'alsendo_order_pickup',
            'alsendo_order_shipment',
            'alsendo_order_package',
            'alsendo_order_shipping_address',
            'alsendo_order_sender_address',
        ];

        foreach ($tables as $table) {
            $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $table . '`';
            Db::getInstance()->execute($sql);
        }

        return true;
    }

    private function uninstallConfig()
    {
        $config_keys = [
            'ALSENDO_REGION',
            'ALSENDO_APP_ID',
            'ALSENDO_SECRET',
            'ALSENDO_TOKEN',
            'ALSENDO_API_KEY',
            'ALSENDO_SENDER_LIST',
            'ALSENDO_SHIPPING_SETTINGS_LIST',
            'ALSENDO_AVAILABLE_SERVICES',
            'ALSENDO_SHIPPING_METHODS_LIST',
            'ALSENDO_PICKUP_INPOST',
            'ALSENDO_PICKUP_INPOST_DISPLAY',
            'ALSENDO_PICKUP_DHL',
            'ALSENDO_PICKUP_DHL_DISPLAY',
            'ALSENDO_PICKUP_POCZTA',
            'ALSENDO_PICKUP_POCZTA_DISPLAY',
            'ALSENDO_PICKUP_DPD',
            'ALSENDO_PICKUP_DPD_DISPLAY',
            'ALSENDO_PICKUP_UPS',
            'ALSENDO_PICKUP_UPS_DISPLAY',
            'ALSENDO_PICKUP_PPL',
            'ALSENDO_PICKUP_PPL_DISPLAY',
            'ALSENDO_PICKUP_BALIKOVNA',
            'ALSENDO_PICKUP_BALIKOVNA_DISPLAY',
            'ALSENDO_PICKUP_PACKETA',
            'ALSENDO_PICKUP_PACKETA_DISPLAY',
            'ALSENDO_PICKUP_ONE_DELIVERY',
            'ALSENDO_PICKUP_ONE_DELIVERY_DISPLAY',
            'ALSENDO_PICKUP_SAMEDAY',
            'ALSENDO_PICKUP_SAMEDAY_DISPLAY',
            'ALSENDO_PICKUP_FAN_COURIER',
            'ALSENDO_PICKUP_FAN_COURIER_DISPLAY',
            'ALSENDO_OS_PREPARING',
            'ALSENDO_LAST_VERSION',
            'ALSENDO_RO_CLIENT_ID',
            'ALSENDO_RO_CLIENT_SECRET',
            'ALSENDO_RO_OAUTH_CODE',
            'ALSENDO_RO_OAUTH_ACCESS_TOKEN',
            'ALSENDO_RO_OAUTH_REFRESH_TOKEN',
            'ALSENDO_RO_OAUTH_EXPIRES_AT',
            'ALSENDO_AUTO_DECLARED_VALUE',
        ];

        foreach ($config_keys as $key) {
            Configuration::deleteByName($key);
        }

        return true;
    }

    private function uninstallTabs()
    {
        $tabs = [
            'AdminAlsendoModuleConfiguration',
            'AdminAlsendoShippingMethods',
            'AdminAlsendoOrder',
            'AdminAlsendoBulkSend',
            'AdminAlsendoShippingConfiguration',
            'AdminAlsendoPointTest',
            'AdminAlsendo',
        ];
        foreach ($tabs as $class) {
            $id = (int) Db::getInstance()->getValue(
                'SELECT `id_tab` FROM `' . _DB_PREFIX_ . 'tab` WHERE `class_name` = "' . pSQL($class) . '"'
            );
            if ($id) {
                $tab = new Tab($id);
                $tab->delete();
            }
        }

        return true;
    }

    private function uninstallHooks()
    {
        $hooks = [
            'displayAfterCarrier',
            'actionValidateOrder',
            'displayPaymentTop',
            'displayAdminOrderSide',
            'displayAdminOrderMain',
            'displayAdminOrder',
            'actionCarrierProcess',
            'actionAdminControllerSetMedia',
            'actionCarrierUpdate',
            'actionObjectCarrierDeleteAfter',
            'actionObjectCarrierUpdateAfter',
            'displayAdminOrdersListTop',
        ];

        foreach ($hooks as $hook) {
            $this->unregisterHook($hook);
        }

        return true;
    }

    public function getOrderShippingCost($cart, $shipping_cost)
    {
        return $this->getCarrierHandler()->getOrderShippingCost($cart, $shipping_cost);
    }

    public function getOrderShippingCostExternal($cart)
    {
        return $this->getCarrierHandler()->getOrderShippingCostExternal($cart);
    }

    public function hookDisplayAfterCarrier($params)
    {
        return $this->getCheckoutHook()->hookDisplayAfterCarrier($params);
    }

    public function hookActionValidateOrder($params)
    {
        return $this->getCheckoutHook()->hookActionValidateOrder($params);
    }

    public function hookActionCarrierProcess($params)
    {
        return $this->getCheckoutHook()->hookActionCarrierProcess($params);
    }

    public function hookDisplayPaymentTop($params)
    {
        $cart = $this->context->cart;
        if (!$cart) {
            return '';
        }

        $sql = 'SELECT pickup_point_display FROM `' . _DB_PREFIX_ . 'alsendo_order_pickup`
                WHERE id_cart = ' . (int) $cart->id;
        $pickupPointDisplay = Db::getInstance()->getValue($sql);

        if (empty($pickupPointDisplay)) {
            return '';
        }

        $this->context->smarty->assign([
            'alsendo_pickup_point_display' => $pickupPointDisplay,
        ]);

        return $this->display(__FILE__, 'views/templates/front/pickup_point_info.tpl');
    }

    public function hookDisplayAdminOrdersListTop($params)
    {
        $this->context->controller->addJS($this->_path . 'views/js/admin/inject-bulk-button.js');

        return '';
    }

    public function hookDisplayAdminOrderSide($params)
    {
        try {
            $result = $this->getAdminOrderHook()->hookDisplayAdminOrderSide($params);

            return $result;
        } catch (Throwable $e) {
            error_log('Alsendo: hookDisplayAdminOrderSide ERROR: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            $this->context->smarty->assign('alsendo_hook_error', $e->getMessage());

            return $this->display(__FILE__, 'views/templates/admin/_partials/hook_error.tpl');
        }
    }

    public function hookDisplayAdminOrderMain($params)
    {
        try {
            return $this->getAdminOrderHook()->hookDisplayAdminOrderMain($params);
        } catch (Throwable $e) {
            error_log('Alsendo: hookDisplayAdminOrderMain ERROR: ' . $e->getMessage());

            return '';
        }
    }

    public function hookDisplayAdminOrder($params)
    {
        return $this->hookDisplayAdminOrderSide($params);
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        static $tabsUpdated = false;
        if (!$tabsUpdated) {
            $this->updateTabTranslations();
            $tabsUpdated = true;
        }

        if ($this->context->controller->controller_name === 'AdminOrders') {
            Media::addJsDef([
                'ALSENDO_MSG_TOKEN_ERROR' => $this->l('Security token not found. Please refresh the page and try again.'),
                'ALSENDO_MSG_SEND_WITH_ALSENDO' => $this->l('Send with Alsendo'),
                'ALSENDO_MSG_ORDERS_SELECTED' => $this->l('order(s) selected'),
                'ALSENDO_MSG_SELECT_ORDERS' => $this->l('Please select at least one order'),
            ]);
            $this->context->controller->addJS($this->_path . 'views/js/admin/alsendo-modal.js');
            $this->context->controller->addJS($this->_path . 'views/js/admin/bulk-send-list.js');
        }
    }

    public function hookActionCarrierUpdate($params)
    {
        $oldId = (int) ($params['id_carrier'] ?? 0);
        $newCarrier = $params['carrier'] ?? null;

        if (!$oldId || !$newCarrier || !Validate::isLoadedObject($newCarrier)) {
            return;
        }

        $newId = (int) $newCarrier->id;

        if ($oldId === $newId) {
            return;
        }

        Db::getInstance()->update(
            'alsendo_shipping_methods',
            ['id_carrier' => $newId],
            'id_carrier = ' . $oldId
        );
    }

    public function hookActionObjectCarrierDeleteAfter($params)
    {
        $carrier = $params['object'] ?? null;

        if (!$carrier || !($carrier instanceof Carrier)) {
            return;
        }

        Db::getInstance()->delete(
            'alsendo_shipping_methods',
            'id_carrier = ' . (int) $carrier->id
        );
    }

    public function hookActionObjectCarrierUpdateAfter($params)
    {
        $carrier = $params['object'] ?? null;

        if (!$carrier || !($carrier instanceof Carrier)) {
            return;
        }

        if ((int) $carrier->deleted === 1) {
            $oldId = (int) $carrier->id;
            $count = (int) Db::getInstance()->getValue(
                'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'alsendo_shipping_methods` WHERE id_carrier = ' . $oldId
            );
            if ($count > 0) {
                $newId = (int) Db::getInstance()->getValue(
                    'SELECT id_carrier FROM `' . _DB_PREFIX_ . 'carrier`
                     WHERE id_reference = ' . (int) $carrier->id_reference . '
                     AND deleted = 0 AND id_carrier != ' . $oldId . '
                     ORDER BY id_carrier DESC'
                );
                if ($newId > 0) {
                    Db::getInstance()->update(
                        'alsendo_shipping_methods',
                        ['id_carrier' => $newId],
                        'id_carrier = ' . $oldId
                    );
                }
            }
        }
    }

    private function clearAllCaches()
    {
        try {
            Tools::clearSmartyCache();
            Tools::clearXMLCache();

            $cacheDir = _PS_ROOT_DIR_ . '/var/cache/';
            foreach (['prod', 'dev'] as $env) {
                $classIndex = $cacheDir . $env . '/class_index.php';
                if (file_exists($classIndex)) {
                    @unlink($classIndex);
                }
            }

            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }
        } catch (Exception $e) {
        }
    }

    private function clearCacheIfVersionChanged()
    {
        $lastVersion = Configuration::get('ALSENDO_LAST_VERSION');
        if ($lastVersion !== $this->version) {
            $this->clearAllCaches();
            Configuration::updateValue('ALSENDO_LAST_VERSION', $this->version);
        }
    }

    private function ensureHooksRegistered()
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        $requiredHooks = [
            'displayAdminOrderSide',
            'displayAdminOrderMain',
            'displayAdminOrder',
            'displayAdminOrdersListTop',
            'displayAfterCarrier',
            'actionValidateOrder',
            'displayPaymentTop',
            'actionCarrierProcess',
            'actionAdminControllerSetMedia',
            'actionCarrierUpdate',
            'actionObjectCarrierDeleteAfter',
            'actionObjectCarrierUpdateAfter',
        ];

        $registered = false;
        foreach ($requiredHooks as $hookName) {
            if (!$this->isRegisteredInHook($hookName)) {
                $this->registerHook($hookName);
                $registered = true;
            }
        }

        if ($registered) {
            $this->clearAllCaches();
        }
    }

    private function updateTabTranslations()
    {
        $tabTranslations = [
            'AdminAlsendoModuleConfiguration' => [
                'en' => 'Configuration',
                'pl' => 'Konfiguracja',
                'cs' => 'Konfigurace',
                'ro' => 'Configurare',
            ],
            'AdminAlsendoShippingMethods' => [
                'en' => 'Shipping Methods',
                'pl' => 'Metody wysyłki',
                'cs' => 'Způsoby dopravy',
                'ro' => 'Metode de livrare',
            ],
        ];

        foreach ($tabTranslations as $class => $translations) {
            $tabId = (int) Db::getInstance()->getValue(
                'SELECT `id_tab` FROM `' . _DB_PREFIX_ . 'tab` WHERE `class_name` = "' . pSQL($class) . '"'
            );
            if ($tabId) {
                $tab = new Tab($tabId);
                foreach (Language::getLanguages(true) as $lang) {
                    $isoCode = $lang['iso_code'];
                    $tab->name[$lang['id_lang']] = $translations[$isoCode] ?? $translations['en'];
                }
                $tab->update();
            }
        }
    }
}

// Apply vendor isolation as soon as PrestaShop loads this file, NOT only when
// Alsendo is instantiated. PS 8.x ModuleRepository->getList() iterates modules
// and runs ModuleDataProvider::isModuleMainClassValid() (which calls
// PhpParser\ParserFactory::create() — v4 API) without ever constructing the
// Module class. Running the blacklist here makes sure our vendored
// nikic/php-parser v5 is removed from the autoloader's PSR-4 + classmap before
// PS reaches that call, regardless of whether __construct() fires this request.
Alsendo::bootstrapAutoloader();
