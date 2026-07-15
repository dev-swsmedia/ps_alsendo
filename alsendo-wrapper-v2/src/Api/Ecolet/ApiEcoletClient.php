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

use Alsendo\AlsendoWrapper\Api\Ecolet\Model\Order\EcoletOrderResponse;
use Alsendo\AlsendoWrapper\Api\Ecolet\Wrapper\OrderRequestWrapper;
use Alsendo\AlsendoWrapper\Api\Ecolet\Wrapper\OrderValuationResponseWrapper;
use Alsendo\AlsendoWrapper\Api\Ecolet\Wrapper\SendOrderResponseWrapper;
use Alsendo\AlsendoWrapper\Api\Ecolet\Wrapper\ServiceStructureWrapper;
use Alsendo\AlsendoWrapper\ApiClient;
use Alsendo\AlsendoWrapper\ApiClientInterface;
use Alsendo\AlsendoWrapper\Exception\ApiRequestException;
use Alsendo\AlsendoWrapper\Exception\ResponseException;
use Alsendo\AlsendoWrapper\Exception\UnsupportedEndpointException;
use Alsendo\AlsendoWrapper\Exception\ValidationException;
use Alsendo\AlsendoWrapper\Json;
use Alsendo\AlsendoWrapper\Model\Error\ApiErrorResponse;
use Alsendo\AlsendoWrapper\Model\Error\ErrorMessage;
use Alsendo\AlsendoWrapper\Model\Location\LocationQueryRequest;
use Alsendo\AlsendoWrapper\Model\Order\OrderRequest;
use Alsendo\AlsendoWrapper\Model\Order\OrderResponse;
use Alsendo\AlsendoWrapper\Model\Order\SendOrderResponse;
use Alsendo\AlsendoWrapper\Model\Order\Valuation\OrderValuationResponse;
use Alsendo\AlsendoWrapper\Model\Service\ServiceStructure;
use Alsendo\AlsendoWrapper\Model\TurnIn\TurnIn;
use Alsendo\AlsendoWrapper\Model\WayBill\WayBill;
use Alsendo\AlsendoWrapper\Validator;
use GuzzleHttp\Exception\GuzzleException;

class ApiEcoletClient extends ApiClient implements ApiClientInterface
{
    public const API_URL = 'https://panel.ecolet.ro/';
    public const API_URL_TEST = 'https://staging.ecolet.ro/';

    public const CONFIG_REQUIRES = [
        'token',
    ];

    public function __construct(array $config, bool $test = false)
    {
        parent::__construct(($test) ? self::API_URL_TEST : self::API_URL, $config);
        $this->authorization();
    }

    private function authorization(): void
    {
        $this->options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config['token'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];
    }

    /**
     * Retrieves the current authenticated user's information.
     *
     * Makes a GET request to the '/api/v1/me' endpoint to fetch the user details.
     *
     * @return array The user data as an associative array
     *
     * @throws GuzzleException If the HTTP request fails
     * @throws \JsonException If the response JSON cannot be parsed
     */
    public function getMe()
    {
        $endpoint = '/api/v1/me';

        try {
            $response = $this->makeRequest('GET', $endpoint);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        return $this->parseResponse($response);
    }

    /**
     * {@inheritDoc}
     */
    public function getServiceStructure(): ServiceStructure
    {
        $endpoint = '/api/v1/services';

        try {
            $response = $this->makeRequest('GET', $endpoint);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);

        return ServiceStructureWrapper::wrap($response);
    }

    /**
     * Retrieves the valuation details for an order based on available service slugs and their associated pricing
     * and status information.
     *
     * @param OrderRequest $order the order request containing the necessary data to determine valuation
     *
     * @throws ValidationException if the order request fails validation
     * @throws \ReflectionException if there is an issue with reflection operations during processing
     * @throws GuzzleException if the HTTP request to the valuation endpoint fails
     * @throws \JsonException if there is an error during JSON parsing or mapping
     */
    public function getOrderValuation(OrderRequest $order): OrderValuationResponse
    {
        $endpoint = '/api/v2/add-parcel/reload-form';

        $validator = new Validator(ValidationRules::ORDER_CREATE);
        $validator->validate($order);

        if ($validator->hasErrors()) {
            throw new ValidationException($validator->getErrors());
        }

        $ecoletOrderRequest = OrderRequestWrapper::wrap($order, $this);

        try {
            $response = $this->makeRequest('POST', $endpoint, [
                'json' => $ecoletOrderRequest->toArray(),
            ]);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);

        return OrderValuationResponseWrapper::wrap($response);
    }

    /**
     * Sends an order to the Ecolet system and returns the created order response.
     *
     * This method validates the provided order request, sends it to the Ecolet API,
     * waits for the order to be processed, and returns the resulting order response.
     *
     * @param OrderRequest $order the order requests to be sent to the Ecolet system
     *
     * @return SendOrderResponse the response object representing the created order
     *
     * @throws GuzzleException
     * @throws \JsonException
     * @throws ValidationException
     */
    public function sendOrder(OrderRequest $order): SendOrderResponse
    {
        $endpoint = '/api/v2/add-parcel/send-order';

        $validator = new Validator(ValidationRules::ORDER_CREATE);
        $validator->validate($order);

        if ($validator->hasErrors()) {
            throw new ValidationException($validator->getErrors());
        }

        $ecoletOrderRequest = OrderRequestWrapper::wrap($order, $this);

        try {
            $response = $this->makeRequest('POST', $endpoint, [
                'json' => $ecoletOrderRequest->toArray(),
            ]);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);

        $orderToSendId = $response['order_to_send_id'];

        $endpoint = '/api/v1/order-to-send/' . $orderToSendId;
        $decodedJson = null;

        // Poll: sameday/locker potrzebuje więcej czasu niż 6s
        $delays = [6, 5, 5, 5];
        foreach ($delays as $delay) {
            sleep($delay);

            try {
                $response = $this->makeRequest('GET', $endpoint);
            } catch (ApiRequestException $e) {
                throw $this->handleRequestException($e);
            }

            $response = $this->parseResponse($response);
            $decodedJson = $response['order_to_send'];

            // Jeśli status nie jest "new" — zamówienie przetworzone
            if (($decodedJson['status'] ?? 'new') !== 'new') {
                break;
            }
        }

        if (!empty($decodedJson['error'])) {
            $apiError = new ApiErrorResponse($decodedJson['error']);
            throw new ResponseException($decodedJson['error'], 400, null, $apiError);
        }

        return SendOrderResponseWrapper::wrap($decodedJson);
    }

    /**
     * Retrieves an order by its unique identifier from the system.
     *
     * This method constructs a GET request to fetch an order using the provided order ID,
     * parses the response, and returns an order response object.
     *
     * @param string $orderId the unique identifier of the order to retrieve
     *
     * @return OrderResponse the order response object representing the retrieved order
     *
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function getOrder(string $orderId): OrderResponse
    {
        $endpoint = '/api/v1/order/' . $orderId;

        try {
            $response = $this->makeRequest('GET', $endpoint);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);

        return Json::mapArrayToObject($response['data'], EcoletOrderResponse::class);
    }

    /**
     * Retrieves the waybill for a given order ID.
     *
     * This method constructs the endpoint for downloading a waybill based on the provided order ID
     * and makes a GET request to fetch the waybill data.
     *
     * @param string $orderId the unique identifier of the order for which to retrieve the waybill
     *
     * @return WayBill the waybill data as a WayBill object
     *
     * @throws GuzzleException if the HTTP request fails or the response is invalid
     */
    public function getWayBill(string $orderId): WayBill
    {
        $endpoint = '/api/v1/order/' . $orderId . '/download-waybill';

        try {
            $wayBillFile = $this->makeRequest('GET', $endpoint);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $wayBill = new WayBill();
        $wayBill->waybill = base64_encode($wayBillFile);
        $wayBill->type = 'pdf';

        return $wayBill;
    }

    /**
     * Cancels an order by its ID using the Ecolet API.
     *
     * This method sends a DELETE request to the specified order endpoint to cancel the order.
     *
     * @param string $orderId the unique identifier of the order to be canceled
     *
     * @return bool true if the cancellation request was successfully sent, false otherwise
     *
     * @throws GuzzleException if the HTTP request fails or the response is invalid
     */
    public function cancelOrder(string $orderId): bool
    {
        $endpoint = '/api/v1/order/' . $orderId;

        try {
            $this->makeRequest('DELETE', $endpoint . '/');
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        return true;
    }

    /**
     * Retrieves a list of turn-in records for the provided order IDs.
     *
     * This method constructs a request to fetch turn-in data for the specified order IDs.
     * It currently throws an exception as the endpoint is not supported.
     *
     * @param string[] $orderIds a list of order IDs for which turn-in records are requested
     *
     * @throws UnsupportedEndpointException when attempting to access the turn_in_list endpoint
     */
    public function getTurnInList(array $orderIds): TurnIn
    {
        throw new UnsupportedEndpointException('turn_in_list');
    }

    /**
     * Retrieves a list of contacts from the system.
     *
     * This method is not supported and will throw an UnsupportedEndpointException.
     *
     * @throws UnsupportedEndpointException when attempting to access the contact list endpoint
     */
    public function getContactList(): array
    {
        throw new UnsupportedEndpointException('contact_list');
    }

    /**
     * Parses an ApiRequestException into a ResponseException with structured Ecolet error data.
     * Ecolet error formats:
     *   {"errors": {"field.path": ["msg1", "msg2"]}} — field-level validation errors
     *   {"general_error": "..."} — general error message
     */
    private function handleRequestException(ApiRequestException $e): ResponseException
    {
        $responseBody = $e->getResponseBody();
        $statusCode = $e->getCode() ?: null;

        if ($responseBody !== null) {
            $decoded = json_decode($responseBody, true);
            if (is_array($decoded)) {
                $errors = [];
                $message = $e->getMessage();

                if (!empty($decoded['errors']) && is_array($decoded['errors'])) {
                    foreach ($decoded['errors'] as $field => $messages) {
                        if (is_array($messages)) {
                            foreach ($messages as $msg) {
                                $errors[] = new ErrorMessage($msg, is_string($field) ? $field : null);
                            }
                        } else {
                            $errors[] = new ErrorMessage((string) $messages, is_string($field) ? $field : null);
                        }
                    }
                    $message = $decoded['message'] ?? 'Validation error';
                }

                if (!empty($decoded['general_error'])) {
                    $message = $decoded['general_error'];
                }

                $apiError = new ApiErrorResponse($message, $errors, $statusCode);

                return new ResponseException($message, $statusCode ?? 400, $e, $apiError);
            }
        }

        $apiError = new ApiErrorResponse($e->getMessage(), [], $statusCode);

        return new ResponseException($e->getMessage(), $statusCode ?? 400, $e, $apiError);
    }

    /**
     * Parses a JSON response string into a PHP array.
     *
     * Converts a JSON-formatted response string into a associative array using JSON decoding.
     * The decoding process throws an exception if the JSON is invalid or malformed.
     *
     * @param string $response the JSON-formatted response string to parse
     *
     * @return array the parsed response as an associative array
     *
     * @throws \JsonException if the JSON string is invalid or cannot be decoded
     */
    private function parseResponse(string $response): array
    {
        return json_decode($response, true, 10, JSON_THROW_ON_ERROR);
    }

    /**
     * Retrieves order details for the specified order IDs.
     *
     * This method constructs a request to fetch order information from the system
     * using the provided order IDs. It returns an array of order response objects
     * corresponding to the requested orders.
     *
     * @param string[] $orderIds a list of order IDs to retrieve details for
     *
     * @return OrderResponse[] an array of order response objects representing the fetched orders
     */
    public function getOrders(array $orderIds): array
    {
        $orders = [];
        foreach ($orderIds as $orderId) {
            try {
                $orders[$orderId] = $this->getOrder($orderId);
            } catch (GuzzleException|\JsonException $e) {
                $orders[$orderId] = null;
            }
        }

        return $orders;
    }

    /**
     * Retrieves a list of locations based on a country code and city.
     *
     * This method constructs a GET request to the '/locations/{country_code}/localities/{city}' endpoint
     * to fetch a list of locations based on the provided country code and city.
     *
     * @param LocationQueryRequest $locationQueryRequest the request object containing the country code and city
     *
     * @return array a list of locations as an associative array
     *
     * @throws GuzzleException
     * @throws \JsonException
     * @throws \ReflectionException
     * @throws ValidationException if the request object fails validation
     */
    public function queryLocation(LocationQueryRequest $locationQueryRequest): array
    {
        $validator = new Validator(ValidationRules::LOCATION_QUERY);
        $validator->validate($locationQueryRequest);

        if ($validator->hasErrors()) {
            throw new ValidationException($validator->getErrors());
        }

        $endpoint = 'api/v1/locations/' . rawurlencode($locationQueryRequest->countryCode) . '/localities/' . rawurlencode($locationQueryRequest->city);

        try {
            $response = $this->makeRequest('GET', $endpoint);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        return $this->parseResponse($response);
    }

    /**
     * @return array{id: int, county: string|null}
     */
    public function getLocalityIdByCity(string $countryCode, string $city): array
    {
        $endpoint = 'api/v1/locations/' . rawurlencode($countryCode) . '/localities/' . rawurlencode($city);

        try {
            $response = $this->makeRequest('GET', $endpoint);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $data = $this->parseResponse($response);

        if (empty($data['localities']) || !is_array($data['localities'])) {
            throw new ValidationException(['locality' => ['Unknown locality: ' . $city]], 'Unknown locality: ' . $city);
        }

        $first = $data['localities'][0];
        if (!isset($first['id'])) {
            throw new ValidationException(['locality' => ['Unknown locality: ' . $city]], 'Unknown locality: ' . $city);
        }

        return [
            'id' => (int) $first['id'],
            'county' => $first['county']['name'] ?? null,
        ];
    }
}
