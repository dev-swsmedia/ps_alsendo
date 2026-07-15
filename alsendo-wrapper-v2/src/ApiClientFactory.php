<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Api\Apaczka\ApiApaczkaClient;
use Alsendo\AlsendoWrapper\Api\Ecolet\ApiEcoletClient;
use Alsendo\AlsendoWrapper\Api\Zaslat\ApiZaslatClient;
use Alsendo\AlsendoWrapper\Exception\ValidationException;

class ApiClientFactory
{
    public const APACZKA = 'apaczka';
    public const APACZKA_TEST = 'apaczka_test';

    public const ECOLET = 'ecolet';
    public const ECOLET_TEST = 'ecolet_test';

    public const ZASLAT = 'zaslat';
    public const ZASLAT_TEST = 'zaslat_test';

    /**
     * Creates and returns an instance of the appropriate API client based on the provided API name and configuration.
     *
     * @param string $apiName The name of the API to create a client for. Must be one of the defined constants
     *                        (e.g., APACZKA, APACZKA_TEST, ECOLET, ECOLET_TEST, ZASLAT, ZASLAT_TEST).
     * @param array $config configuration array required by the selected API client
     *
     * @return ApiClientInterface an instance of the appropriate API client implementation
     *
     * @throws \InvalidArgumentException if the $apiName is not a valid constant or if the configuration is invalid
     * @throws ValidationException if the configuration is missing, required fields for the specified API
     */
    public function create(string $apiName, array $config): ApiClientInterface
    {
        $this->validateConfig($apiName, $config);

        switch ($apiName) {
            case self::APACZKA:
                return new ApiApaczkaClient($config);
            case self::APACZKA_TEST:
                return new ApiApaczkaClient($config, true);
            case self::ECOLET:
                return new ApiEcoletClient($config);
            case self::ECOLET_TEST:
                return new ApiEcoletClient($config, true);
            case self::ZASLAT:
                return new ApiZaslatClient($config);
            case self::ZASLAT_TEST:
                return new ApiZaslatClient($config, true);
            default:
                throw new \InvalidArgumentException("Invalid API name: $apiName");
        }
    }

    /**
     * Validates the configuration for a given API name against required fields.
     *
     * @param string $apiName the name of the API to validate configuration for
     * @param array $config the configuration array to validate
     *
     * @return void
     *
     * @throws \InvalidArgumentException if the API name is unknown
     * @throws ValidationException if required, configuration fields are missing
     */
    private function validateConfig(string $apiName, array $config): void
    {
        switch ($apiName) {
            case self::APACZKA:
            case self::APACZKA_TEST:
                $requiredFields = ApiApaczkaClient::CONFIG_REQUIRES;
                break;
            case self::ECOLET:
            case self::ECOLET_TEST:
                $requiredFields = ApiEcoletClient::CONFIG_REQUIRES;
                break;
            case self::ZASLAT:
            case self::ZASLAT_TEST:
                $requiredFields = ApiZaslatClient::CONFIG_REQUIRES;
                break;
            default:
                throw new \InvalidArgumentException("Unknown API name: $apiName");
        }

        $missingFields = array_diff($requiredFields, array_keys($config));
        if (!empty($missingFields)) {
            throw new ValidationException(['missing_fields' => $missingFields], "Configuration validation failed for $apiName.");
        }
    }
}
