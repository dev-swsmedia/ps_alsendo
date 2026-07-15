<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Zaslat;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Api\Zaslat\Model\AddressBookListResponse;
use Alsendo\AlsendoWrapper\Api\Zaslat\Model\Order\ZaslatOrderResponse;
use Alsendo\AlsendoWrapper\Api\Zaslat\Wrapper\AddressBookListResponseWrapper;
use Alsendo\AlsendoWrapper\Api\Zaslat\Wrapper\ContactListResponseWrapper;
use Alsendo\AlsendoWrapper\Api\Zaslat\Wrapper\OrderRequestWrapper;
use Alsendo\AlsendoWrapper\Api\Zaslat\Wrapper\OrderValuationRequestWrapper;
use Alsendo\AlsendoWrapper\Api\Zaslat\Wrapper\OrderValuationResponseWrapper;
use Alsendo\AlsendoWrapper\Api\Zaslat\Wrapper\SendOrderResponseWrapper;
use Alsendo\AlsendoWrapper\Api\Zaslat\Wrapper\ServiceStructureWrapper;
use Alsendo\AlsendoWrapper\ApiClient;
use Alsendo\AlsendoWrapper\ApiClientInterface;
use Alsendo\AlsendoWrapper\Exception\ApiRequestException;
use Alsendo\AlsendoWrapper\Exception\ResponseException;
use Alsendo\AlsendoWrapper\Exception\UnresponsivenessException;
use Alsendo\AlsendoWrapper\Exception\ValidationException;
use Alsendo\AlsendoWrapper\Json;
use Alsendo\AlsendoWrapper\Model\Contact;
use Alsendo\AlsendoWrapper\Model\Error\ApiErrorResponse;
use Alsendo\AlsendoWrapper\Model\Order\OrderRequest;
use Alsendo\AlsendoWrapper\Model\Order\OrderResponse;
use Alsendo\AlsendoWrapper\Model\Order\SendOrderResponse;
use Alsendo\AlsendoWrapper\Model\Order\Valuation\OrderValuationResponse;
use Alsendo\AlsendoWrapper\Model\Service\ServiceStructure;
use Alsendo\AlsendoWrapper\Model\TurnIn\TurnIn;
use Alsendo\AlsendoWrapper\Model\WayBill\WayBill;
use Alsendo\AlsendoWrapper\Validator;
use GuzzleHttp\Exception\GuzzleException;

class ApiZaslatClient extends ApiClient implements ApiClientInterface
{
    public const API_URL = 'https://www.zaslat.cz/';
    public const API_URL_TEST = 'https://test.zaslat.cz/';

    public const CONFIG_REQUIRES = [
        'api_key',
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
                'X-Apikey' => $this->config['api_key'],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getServiceStructure(): ServiceStructure
    {
        return ServiceStructureWrapper::wrap();
    }

    /**
     * Retrieves the valuation for an order based on shipping rates.
     *
     * @param OrderRequest $order the order request containing shipment details for valuation
     *
     * @return OrderValuationResponse the valuation response with available shipping rates
     *
     * @throws GuzzleException
     * @throws \JsonException
     * @throws ResponseException
     * @throws UnresponsivenessException
     * @throws ValidationException
     * @throws \ReflectionException
     */
    public function getOrderValuation(OrderRequest $order): OrderValuationResponse
    {
        $endpoint = '/api/v1/rates/get';

        $validator = new Validator(ValidationRules::ORDER_CREATE);
        $validator->validate($order);

        if ($validator->hasErrors()) {
            throw new ValidationException($validator->getErrors());
        }

        $request = OrderValuationRequestWrapper::wrap($order);

        try {
            $response = $this->makeRequest('POST', $endpoint, [
                'json' => $request->toArray(),
            ]);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);

        return OrderValuationResponseWrapper::wrap($response);
    }

    /**
     * Parses the raw response from the API into a structured array.
     *
     * @param string $response the raw response string from the API
     *
     * @return array the parsed response array containing status, message, and other data
     *
     * @throws UnresponsivenessException if the response is not a valid array or missing required fields
     * @throws ResponseException if the response status is not 200
     * @throws \JsonException
     */
    private function parseResponse($response): array
    {
        $decodedResponse = json_decode($response, true, 10, JSON_THROW_ON_ERROR);

        if (!is_array($decodedResponse) || !isset(
            $decodedResponse['status'],
            $decodedResponse['message'],)
        ) {
            throw new UnresponsivenessException();
        }

        $status = (int) $decodedResponse['status'];

        if ($status !== 200) {
            if (!empty($decodedResponse['errors']) && is_array($decodedResponse['errors'])) {
                $validationErrors = [];
                foreach ($decodedResponse['errors'] as $error) {
                    $field = $error['field'] ?? 'general';
                    $validationErrors[$field][] = $error['message'] ?? 'Unknown error';
                }
                throw new ValidationException($validationErrors, $decodedResponse['message'], $status);
            }
            $apiError = new ApiErrorResponse($decodedResponse['message'], [], $status);
            throw new ResponseException($decodedResponse['message'], $status, null, $apiError);
        }

        return $decodedResponse;
    }

    /**
     * Converts an ApiRequestException into a ResponseException with structured error data.
     * When the API returns field-level validation errors, throws ValidationException instead
     * so the error format is consistent with pre-request validation (same as Apaczka).
     *
     * @throws ValidationException when the API response contains field-level validation errors
     */
    private function handleRequestException(ApiRequestException $e): ResponseException
    {
        $responseBody = $e->getResponseBody();
        if ($responseBody !== null) {
            $decoded = json_decode($responseBody, true);
            if (is_array($decoded)) {
                $message = $decoded['message'] ?? $e->getMessage();

                if (!empty($decoded['errors']) && is_array($decoded['errors'])) {
                    $validationErrors = [];
                    foreach ($decoded['errors'] as $error) {
                        $field = $error['field'] ?? 'general';
                        $validationErrors[$field][] = $error['message'] ?? 'Unknown error';
                    }
                    throw new ValidationException($validationErrors, $message, $e->getCode() ?: 400);
                }

                $apiError = new ApiErrorResponse($message, [], $e->getCode() ?: null);

                return new ResponseException($message, $e->getCode() ?: 400, $e, $apiError);
            }
        }
        $apiError = new ApiErrorResponse($e->getMessage(), [], $e->getCode() ?: null);

        return new ResponseException($e->getMessage(), $e->getCode() ?: 400, $e, $apiError);
    }

    /**
     * Sends an order request to the shipping service and returns the response.
     *
     * Makes two API calls: first creates the shipment, then fetches its detail
     * to populate the response with carrier, service, and status information.
     *
     * @param OrderRequest $order the order data to be sent to the shipping service
     *
     * @return SendOrderResponse the response object containing the created order details
     *
     * @throws GuzzleException
     * @throws \JsonException
     * @throws ResponseException
     * @throws UnresponsivenessException
     * @throws ValidationException
     */
    public function sendOrder(OrderRequest $order): SendOrderResponse
    {
        $endpoint = '/api/v1/shipments/create';

        $validator = new Validator(ValidationRules::ORDER_CREATE);
        $validator->validate($order);

        if ($validator->hasErrors()) {
            throw new ValidationException($validator->getErrors());
        }

        $zaslatOrderRequest = OrderRequestWrapper::wrap($order);

        try {
            $response = $this->makeRequest('POST', $endpoint, [
                'json' => $zaslatOrderRequest->toArray(),
            ]);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);
        $createData = $response['data'];

        $detailData = [];
        $shipmentId = $createData['shipments'][0] ?? null;

        if ($shipmentId) {
            try {
                $detailResponse = $this->makeRequest('POST', '/api/v1/shipments/detail', [
                    'json' => ['shipments' => [$shipmentId]],
                ]);
                $detailResponse = $this->parseResponse($detailResponse);
                $detailData = $detailResponse['data'][$shipmentId] ?? [];
            } catch (ApiRequestException $e) {
                // Order was created successfully — detail fetch failure is non-fatal
            }
        }

        return SendOrderResponseWrapper::wrap($createData, $detailData);
    }

    /**
     * Retrieves an order detail by its ID from the shipping service.
     *
     * @param string $orderId the unique identifier of the order to retrieve
     *
     * @return OrderResponse the order response object containing the order details
     *
     * @throws GuzzleException
     * @throws \JsonException
     * @throws ResponseException
     * @throws UnresponsivenessException
     */
    public function getOrder(string $orderId): OrderResponse
    {
        $endpoint = '/api/v1/shipments/detail';

        try {
            $response = $this->makeRequest('POST', $endpoint, [
                'json' => [
                    'shipments' => [$orderId],
                ],
            ]);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);

        $zaslatOrderResponse = Json::mapArrayToObject($response['data'][$orderId], ZaslatOrderResponse::class);
        $zaslatOrderResponse->id = $orderId;

        return $zaslatOrderResponse;
    }

    /**
     * Retrieves order details for the specified order IDs by sending a request to the shipping service.
     *
     * @param string[] $orderIds an array of order IDs to fetch details for
     *
     * @return OrderResponse[] an associative array where the key is the order ID and the value is the order details
     *
     * @throws GuzzleException
     * @throws \JsonException
     * @throws ResponseException
     * @throws UnresponsivenessException
     */
    public function getOrders(array $orderIds): array
    {
        $endpoint = '/api/v1/shipments/detail';

        try {
            $response = $this->makeRequest('POST', $endpoint, [
                'json' => [
                    'shipments' => $orderIds,
                ],
            ]);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);

        $zaslatOrdersResponse = [];

        foreach ($response['data'] as $orderId => $order) {
            $zaslatOrdersResponse[$orderId] = Json::mapArrayToObject($order, ZaslatOrderResponse::class);
            $zaslatOrdersResponse[$orderId]->id = $orderId;
        }

        return $zaslatOrdersResponse;
    }

    /**
     * Retrieves the waybill data for a given order ID by making a request to the shipping service.
     *
     * @param string $orderId the unique identifier of the shipment for which to retrieve the waybill
     *
     * @return WayBill the waybill data as a WayBill object
     *
     * @throws GuzzleException
     * @throws \JsonException
     * @throws ResponseException
     * @throws UnresponsivenessException
     */
    public function getWayBill(string $orderId): WayBill
    {
        $endpoint = '/api/v1/shipments/label';

        try {
            $response = $this->makeRequest('POST', $endpoint, [
                'json' => [
                    'shipments' => [$orderId],
                ],
            ]);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);

        $wayBill = new WayBill();
        $wayBill->waybill = $response['data'];
        $wayBill->type = 'pdf';

        return $wayBill;
    }

    /**
     * Cancels a shipment order by sending a request to the shipping service.
     *
     * @param string $orderId the unique identifier of the shipment to be canceled
     *
     * @return bool true if the cancellation request was successfully processed, false otherwise
     *
     * @throws GuzzleException
     * @throws \JsonException
     * @throws ResponseException
     * @throws UnresponsivenessException
     */
    public function cancelOrder(string $orderId): bool
    {
        $endpoint = '/api/v1/shipments/storno';

        try {
            $response = $this->makeRequest('POST', $endpoint, [
                'json' => [
                    'shipments' => [$orderId],
                ],
            ]);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);

        return true;
    }

    /**
     * Retrieves a list of turn-in records for the specified order IDs.
     *
     * @param string[] $orderIds a list of order IDs to query turn-in records for
     *
     * @return TurnIn the turn-in object containing the base64 PDF
     *
     * @throws GuzzleException
     * @throws \JsonException
     * @throws ResponseException
     * @throws UnresponsivenessException
     */
    public function getTurnInList(array $orderIds): TurnIn
    {
        $endpoint = '/api/v1/shipments/manifest';

        try {
            $response = $this->makeRequest('POST', $endpoint, [
                'json' => [
                    'shipments' => $orderIds,
                ],
            ]);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);

        $turnIn = new TurnIn();
        $turnIn->turnIn = $response['data'];

        return $turnIn;
    }

    /**
     * Retrieves a list of contacts from the contact management service.
     *
     * @return Contact[] an array of Contact objects representing the retrieved contact list
     *
     * @throws GuzzleException
     * @throws \JsonException
     * @throws ResponseException
     * @throws UnresponsivenessException
     */
    public function getContactList(): array
    {
        $endpoint = '/api/v1/contacts/list';

        try {
            $response = $this->makeRequest('GET', $endpoint);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);

        return ContactListResponseWrapper::wrap($response['data']);
    }

    public function getAddressBookList(): AddressBookListResponse
    {
        $endpoint = '/api/v1/contacts/list';

        try {
            $response = $this->makeRequest('GET', $endpoint);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);

        return AddressBookListResponseWrapper::wrap($response['data'] ?? []);
    }
}
