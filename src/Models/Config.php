<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\Models;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Config
{
    private string $region;
    private array $apiConfig;

    public function __construct(string $region = '', array $apiConfig = [])
    {
        $this->region = $region;
        $this->apiConfig = $apiConfig;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function setRegion(string $region): void
    {
        $this->region = $region;
    }

    public function getApiConfig(): array
    {
        return $this->apiConfig;
    }

    public function setApiConfig(array $apiConfig): void
    {
        $this->apiConfig = $apiConfig;
    }

    public function getAppId(): string
    {
        return $this->apiConfig['app_id'] ?? '';
    }

    public function getAppSecret(): string
    {
        return $this->apiConfig['app_secret'] ?? '';
    }

    public function getToken(): string
    {
        return $this->apiConfig['token'] ?? '';
    }

    public function getApiKey(): string
    {
        return $this->apiConfig['api_key'] ?? '';
    }

    public function getRoClientId(): string
    {
        return $this->apiConfig['ro_client_id'] ?? '';
    }

    public function getRoClientSecret(): string
    {
        return $this->apiConfig['ro_client_secret'] ?? '';
    }

    public function isComplete(): bool
    {
        if (empty($this->region)) {
            return false;
        }

        if ($this->region === 'ro') {
            $hasOAuth = !empty($this->apiConfig['ro_client_id'])
                && !empty($this->apiConfig['ro_client_secret'])
                && !empty($this->apiConfig['ro_oauth_access_token']);
            $hasLegacyToken = !empty($this->apiConfig['token']);

            return $hasOAuth || $hasLegacyToken;
        }

        $requiredFields = $this->getRequiredFields();
        foreach ($requiredFields as $field) {
            if (empty($this->apiConfig[$field])) {
                return false;
            }
        }

        return true;
    }

    public function getRequiredFields(): array
    {
        $map = [
            'pl' => ['app_id', 'app_secret'],
            'ro' => ['ro_client_id', 'ro_client_secret'],
            'cz' => ['api_key'],
        ];

        return $map[$this->region] ?? [];
    }

    public function getWrapperApiName(): string
    {
        $isTestMode = (bool) \Configuration::get('ALSENDO_TEST_MODE', null, null, null, true);

        if ($isTestMode) {
            $regionMap = [
                'pl' => 'apaczka_test',
                'cz' => 'zaslat_test',
                'ro' => 'ecolet_test',
            ];
        } else {
            $regionMap = [
                'pl' => 'apaczka',
                'cz' => 'zaslat',
                'ro' => 'ecolet',
            ];
        }

        return $regionMap[$this->region] ?? '';
    }

    public function getWrapperConfig(): array
    {
        $config = $this->apiConfig;

        if ($this->region === 'ro' && !empty($config['ro_oauth_access_token'])) {
            $config['token'] = $config['ro_oauth_access_token'];
        }

        if (!empty($config['token'])) {
            $config['token'] = preg_replace('/\s+/', '', $config['token']);
        }

        return $config;
    }
}
