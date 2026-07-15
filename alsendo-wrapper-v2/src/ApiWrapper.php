<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper;

use Alsendo\AlsendoWrapper\Exception\ValidationException;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * A wrapper class that provides a unified interface to an API client instance.
 * It manages the creation and access of an API client based on configuration.
 */
class ApiWrapper
{
    private ApiClientInterface $apiClient;

    /**
     * Initializes the object with a client name and configuration.
     *
     * @param string $name the name of the API client to create
     * @param array $config configuration array for the API client
     * @param string|null $platformName Name of the platform where the wrapper is used
     *
     * @return void
     *
     * @throws ValidationException
     * @throws \RuntimeException if the client creation fails due to configuration issues
     * @throws \InvalidArgumentException if the name is not provided or invalid
     */
    public function __construct(string $name, array $config, string $platformName = null)
    {
        $this->apiClient = (new ApiClientFactory())->create($name, $config);

        if ($platformName !== null) {
            $this->apiClient->setPlatformName($platformName);
        }
    }

    /**
     * Retrieves the configured API client instance.
     *
     * @return ApiClientInterface the API client instance
     *
     * @throws \RuntimeException if the API client is not initialized
     */
    public function getApiClient(): ApiClientInterface
    {
        return $this->apiClient;
    }
}
