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
use Alsendo\AlsendoWrapper\Exception\UnsupportedEndpointException;
use GuzzleHttp\Exception\GuzzleException;

abstract class ApiKeyClient extends ApiClient
{
    private array $supportedEndpoints = [
    ];

    /**
     * Sends a GET request to the specified endpoint with optional query parameters and authentication headers.
     *
     * @param string $endpoint the API endpoint path to request
     * @param array $params optional query parameters to include in the request URL
     *
     * @return string the response data returned by the API as string
     *
     * @throws GuzzleException
     * @throws UnsupportedEndpointException if the API does not support the requested endpoint
     */
    public function get(string $endpoint, array $params = []): string
    {
        if (!in_array($endpoint, $this->supportedEndpoints, true)) {
            throw new UnsupportedEndpointException($endpoint);
        }

        $options = [
            'query' => $params,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config['api_key'],
            ],
        ];

        return $this->makeRequest('GET', $endpoint, $options);
    }
}
