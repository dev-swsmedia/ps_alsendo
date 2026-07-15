<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Ecolet;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Api\Ecolet\Model\Token;
use Alsendo\AlsendoWrapper\Exception\ResponseException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class EcoletOAuthClient
{
    public const OAUTH_URL = 'https://panel.ecolet.ro/oauth/authorize';
    public const OAUTH_TEST_URL = 'https://staging.ecolet.ro/oauth/authorize';
    public const OAUTH_TOKEN_URL = 'https://panel.ecolet.ro/api/v1/oauth/token';
    public const OAUTH_TEST_TOKEN_URL = 'https://staging.ecolet.ro/api/v1/oauth/token';
    private Client $httpClient;
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    private string $authUrl;
    private string $tokenUrl;

    /**
     * Initializes the OAuth client with configuration and test mode settings.
     *
     * @param array $config configuration array containing 'client_id', 'client_secret', and 'redirect_uri'
     * @param bool $test whether to use test environment URLs; defaults to false
     *
     * @return void
     */
    public function __construct(array $config, bool $test = false)
    {
        $baseUrl = $test ? self::OAUTH_TEST_URL : self::OAUTH_URL;
        $baseKey = method_exists(Client::class, 'request') ? 'base_uri' : 'base_url';
        $this->httpClient = new Client([
            $baseKey => $baseUrl,
            'timeout' => 10.0,
        ]);

        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->redirectUri = $config['redirect_uri'];
        $this->authUrl = $test ? self::OAUTH_TEST_URL : self::OAUTH_URL;
        $this->tokenUrl = $test ? self::OAUTH_TEST_TOKEN_URL : self::OAUTH_TOKEN_URL;
    }

    /**
     * Generates the authorization URL for redirecting the user to the OAuth2 authorization endpoint.
     *
     * @param string $state a randomly generated state parameter to prevent CSRF attacks and maintain the session state
     *
     * @return string the constructed authorization URL with query parameters
     */
    public function getAuthorizationUrl(string $state = ''): string
    {
        return sprintf(
            '%s?client_id=%s&redirect_uri=%s&response_type=code&state=%s',
            $this->authUrl,
            urlencode($this->clientId),
            urlencode($this->redirectUri),
            urlencode($state)
        );
    }

    /**
     * Retrieves an access token from the authorization server using an authorization code.
     *
     * @param string $code the authorization code obtained from the user during the authorization flow
     *
     * @return ?Token the access token response, or null if the request failed or no response was received
     *
     * @throws \JsonException
     * @throws ResponseException
     */
    public function getAccessToken(string $code): ?Token
    {
        return $this->sendRequest([
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'code' => $code,
        ]);
    }

    /**
     * Retrieves an access token using the password grant type.
     *
     * @param string $username the username for authentication
     * @param string $password the password for authentication
     *
     * @return ?Token the access token response, or null if the request failed
     *
     * @throws \JsonException
     * @throws ResponseException
     */
    public function getAccessTokenWithPassword(string $username, string $password): ?Token
    {
        return $this->sendRequest([
            'grant_type' => 'password',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'username' => $username,
            'password' => $password,
            'scope' => '',
        ]);
    }

    /**
     * Refreshes an access token using a refresh token.
     *
     * @param string $refreshToken the refresh token used to obtain a new access token
     *
     * @return ?Token the newly obtained token or null if the request failed
     *
     * @throws \JsonException
     * @throws ResponseException
     */
    public function refreshAccessToken(string $refreshToken): ?Token
    {
        return $this->sendRequest([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => '',
        ]);
    }

    /**
     * Sends a POST request to the token endpoint with the provided data and returns a Token object if successful.
     *
     * @param array $data the form data to include in the request, such as client_id, client_secret, and grant_type
     *
     * @return Token the decoded Token object from the response
     *
     * @throws \JsonException
     * @throws ResponseException if a Guzzle HTTP exception occurs or the response body is invalid JSON
     */
    private function sendRequest(array $data): Token
    {
        try {
            $postOptions = [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ];
            // Guzzle 7 uses form_params, Guzzle 5 uses body
            if (method_exists($this->httpClient, 'request')) {
                $postOptions['form_params'] = $data;
            } else {
                $postOptions['body'] = http_build_query($data);
            }
            $response = $this->httpClient->post($this->tokenUrl, $postOptions);

            $responseData = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

            return new Token($responseData);
        } catch (GuzzleException $e) {
            throw new ResponseException($e->getMessage());
        }
    }
}
