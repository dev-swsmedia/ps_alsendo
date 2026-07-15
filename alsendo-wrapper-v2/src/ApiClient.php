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

use Alsendo\AlsendoWrapper\Exception\ApiRequestException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

abstract class ApiClient
{
    protected Client $httpClient;
    protected array $config;
    protected array $options = [];
    protected string $platformName;

    /**
     * Initializes a new instance of the class with the provided API URL, configuration, and optional settings.
     *
     * @param string $apiUrl the base URL of the API to interact with
     * @param array $config an associative array of configuration settings for the client
     * @param array $options optional configuration settings that may be used by the client for additional behavior
     *
     * @return void
     *
     * @throws \InvalidArgumentException if the provided apiUrl is not a valid string or is empty
     * @throws \InvalidArgumentException if the config array is not provided or is not an array
     */
    public function __construct(string $apiUrl, array $config, array $options = [])
    {
        $this->config = $config;
        $this->config['apiUrl'] = $apiUrl;
        // Guzzle 7 uses 'base_uri', Guzzle 5 uses 'base_url'
        $baseKey = method_exists(Client::class, 'request') ? 'base_uri' : 'base_url';
        $this->httpClient = new Client([$baseKey => $apiUrl]);
        $this->options = $options;
    }

    public function setPlatformName(string $platformName): void
    {
        $this->platformName = $platformName;
    }

    // NOT USED COMMENTED FOR PRESTASHOP VALIDATION
    /*
    private function authorization(): void
    {

    }
    */

    /**
     * Sends an HTTP request to the specified endpoint using the configured HTTP client.
     *
     * @param string $method The HTTP method to use (e.g., 'GET', 'POST', 'PUT', 'DELETE').
     * @param string $endpoint the endpoint path to request, relative to the base URL
     * @param array $options additional options to pass to the HTTP request, such as headers, query parameters, or body data
     *
     * @return string the response body as a string
     *
     * @throws \InvalidArgumentException if the method or endpoint is not provided or invalid
     * @throws ApiRequestException if an HTTP request fails, preserving the response body for structured error parsing
     */
    protected function makeRequest(string $method, string $endpoint, array $options = []): string
    {
        $options = array_merge($this->options, $options);
        // Guzzle 5: form_params not supported, convert to body
        if (!method_exists($this->httpClient, 'request') && isset($options['form_params'])) {
            $options['body'] = http_build_query($options['form_params']);
            $options['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
            unset($options['form_params']);
        }
        try {
            if (method_exists($this->httpClient, 'request')) {
                $response = $this->httpClient->request($method, $endpoint, $options);
            } else {
                // Guzzle 5: createRequest + send (only available on Guzzle 5 client)
                /** @phpstan-ignore-next-line */
                $request = $this->httpClient->createRequest($method, $endpoint, $options);
                $response = $this->httpClient->send($request);
            }

            return (string) $response->getBody();
        } catch (RequestException $e) {
            $responseBody = null;
            $statusCode = 0;
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $responseBody = $response->getBody()->getContents();
            }
            throw new ApiRequestException('Request failed: ' . $e->getMessage(), $statusCode, $e, $responseBody);
        }
    }
}
