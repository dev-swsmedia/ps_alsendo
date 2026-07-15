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

use Alsendo\AlsendoWrapper\Api\Ecolet\EcoletOAuthClient;
use Composer\Autoload\ClassLoader;

// Load wrapper autoloader. On PS 1.7.x the wrapper vendors newer versions of
// Guzzle, Symfony and PSR packages which conflict with PS's bundled ones.
// Clear every PSR-4/PSR-0 prefix except wrapper's own Alsendo\* namespace so
// PS keeps using its own versions of shared libraries.
$_alsendoAutoload = _PS_MODULE_DIR_ . 'alsendo/vendor/autoload.php';
if (file_exists($_alsendoAutoload)) {
    $_alsendoLoader = require_once $_alsendoAutoload;
    if ($_alsendoLoader instanceof ClassLoader) {
        foreach ($_alsendoLoader->getPrefixesPsr4() as $_alsendoPrefix => $_alsendoPaths) {
            if (strpos($_alsendoPrefix, 'Alsendo\\') !== 0) {
                $_alsendoLoader->setPsr4($_alsendoPrefix, []);
            }
        }
        foreach ($_alsendoLoader->getPrefixes() as $_alsendoPrefix => $_alsendoPaths) {
            if (strpos($_alsendoPrefix, 'Alsendo\\') !== 0 && strpos($_alsendoPrefix, 'Alsendo_') !== 0) {
                $_alsendoLoader->set($_alsendoPrefix, []);
            }
        }
    }
}

class AlsendoEcoletauthModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $code = Tools::getValue('code', '');
        $state = Tools::getValue('state', '');

        $baseUrl = (Tools::usingSecureMode() ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

        if (empty($code) || empty($state)) {
            Tools::redirect($baseUrl);

            return;
        }

        $clientSecret = Configuration::get('ALSENDO_RO_CLIENT_SECRET');
        if (empty($clientSecret)) {
            Tools::redirect($baseUrl);

            return;
        }

        $expectedState = md5(substr($clientSecret, 0, 10));
        if ($expectedState !== $state) {
            Tools::redirect($baseUrl);

            return;
        }

        $clientId = Configuration::get('ALSENDO_RO_CLIENT_ID');
        if (empty($clientId)) {
            Tools::redirect($baseUrl);

            return;
        }

        $redirectUri = $baseUrl . '/index.php?fc=module&module=alsendo&controller=ecoletauth';
        if (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
            $redirectUri = 'https://google.com';
        }

        $isTestMode = (bool) Configuration::get('ALSENDO_TEST_MODE', null, null, null, false);

        $oauthConfig = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
        ];

        Configuration::updateValue('ALSENDO_RO_OAUTH_CODE', $code);

        try {
            $client = new EcoletOAuthClient($oauthConfig, $isTestMode);
            $token = $client->getAccessToken($code);
        } catch (Throwable $e) {
            Tools::redirect($baseUrl);

            return;
        }

        if (!$token) {
            Tools::redirect($baseUrl);

            return;
        }

        Configuration::updateValue('ALSENDO_RO_OAUTH_ACCESS_TOKEN', $token->getAccessToken());
        Configuration::updateValue('ALSENDO_RO_OAUTH_REFRESH_TOKEN', $token->refreshToken);
        Configuration::updateValue('ALSENDO_RO_OAUTH_EXPIRES_AT', (string) $token->expiresAt);

        Tools::redirect($baseUrl);
    }
}
