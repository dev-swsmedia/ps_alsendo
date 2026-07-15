<?php
/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace Alsendo\AlsendoWrapper\Api\Apaczka;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alsendo\AlsendoWrapper\Api\Apaczka\Model\Order\ApaczkaOrderResponse;
use Alsendo\AlsendoWrapper\Api\Apaczka\Wrapper\OrderValuationResponseWrapper;
use Alsendo\AlsendoWrapper\Api\Apaczka\Wrapper\SendOrderResponseWrapper;
use Alsendo\AlsendoWrapper\Api\Apaczka\Wrapper\ServiceStructureWrapper;
use Alsendo\AlsendoWrapper\ApiClient;
use Alsendo\AlsendoWrapper\ApiClientInterface;
use Alsendo\AlsendoWrapper\Exception\ApiRequestException;
use Alsendo\AlsendoWrapper\Exception\ResponseException;
use Alsendo\AlsendoWrapper\Exception\UnresponsivenessException;
use Alsendo\AlsendoWrapper\Exception\UnsupportedEndpointException;
use Alsendo\AlsendoWrapper\Exception\ValidationException;
use Alsendo\AlsendoWrapper\Json;
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

class ApiApaczkaClient extends ApiClient implements ApiClientInterface
{
    public const API_URL = 'https://www.apaczka.pl/api/v2/';

    public const API_URL_TEST = 'https://panel-sandbox.apaczka.pl/api/v2/';

    public const CONFIG_REQUIRES = [
        'app_id',
        'app_secret',
    ];

    private const EXPIRES = '+20min';

    public function __construct(array $config, bool $test = false)
    {
        parent::__construct(($test) ? self::API_URL_TEST : self::API_URL, $config);
    }

    // NOT USED COMMENTED FOR PRESTASHOP VALIDATION
    /*
    private function authorization(): void
    {

    }
    */

    private function getSignature($string, $key): string
    {
        return hash_hmac('sha256', $string, $key);
    }

    private function stringToSign($appId, $route, $data, $expires): string
    {
        return sprintf('%s:%s:%s:%s', $appId, $route, $data, $expires);
    }

    /**
     * {@inheritDoc}
     */
    public function getServiceStructure(): ServiceStructure
    {
        $endpoint = 'service_structure/';

        try {
            $response = $this->makeRequest('POST', $endpoint, [
                'form_params' => $this->buildRequest([], $endpoint),
            ]);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);

        return ServiceStructureWrapper::wrap($response);
    }

    /**
     * Retrieves the valuation for an order based on the specified order request.
     *
     * @param OrderRequest $order the order request containing details for valuation calculation
     *
     * @throws UnresponsivenessException
     * @throws \ReflectionException
     * @throws GuzzleException
     * @throws ResponseException
     * @throws \JsonException
     * @throws ValidationException
     */
    public function getOrderValuation(OrderRequest $order): OrderValuationResponse
    {
        $endpoint = 'order_valuation/';

        $validator = new Validator(ValidationRules::ORDER_CREATE);
        $validator->validate($order);

        if ($validator->hasErrors()) {
            throw new ValidationException($validator->getErrors());
        }

        try {
            $response = $this->makeRequest('POST', $endpoint, [
                'form_params' => $this->buildRequest(['order' => $order->toArray()], $endpoint),
            ]);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);

        return OrderValuationResponseWrapper::wrap($response);
    }

    /**
     * Sends an order request to the API and returns the created order response.
     *
     * @param OrderRequest $order the order data to be sent, containing all required fields
     *
     * @return SendOrderResponse the parsed response object representing the created order
     *
     * @throws GuzzleException
     * @throws \JsonException
     * @throws ResponseException
     * @throws UnresponsivenessException
     * @throws ValidationException
     */
    public function sendOrder(OrderRequest $order): SendOrderResponse
    {
        $endpoint = 'order_send/';

        $validator = new Validator(ValidationRules::ORDER_CREATE);
        $validator->validate($order);

        if ($validator->hasErrors()) {
            throw new ValidationException($validator->getErrors());
        }

        try {
            $response = $this->makeRequest('POST', $endpoint, [
                'form_params' => $this->buildRequest(['order' => $order->toArray()], $endpoint),
            ]);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);

        return SendOrderResponseWrapper::wrap($response['order']);
    }

    /**
     * Retrieves an order by its unique identifier from the API and returns it as a response object.
     *
     * @param string $orderId the unique identifier of the order to retrieve
     *
     * @return OrderResponse the parsed response object representing the requested order
     *
     * @throws GuzzleException
     * @throws \JsonException
     * @throws ResponseException
     * @throws UnresponsivenessException
     */
    public function getOrder(string $orderId): OrderResponse
    {
        $endpoint = 'order/' . $orderId . '/';

        try {
            $response = $this->makeRequest('POST', $endpoint, [
                'form_params' => $this->buildRequest([], $endpoint),
            ]);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);

        return Json::mapArrayToObject($response['order'], ApaczkaOrderResponse::class);
    }

    /**
     * Retrieves order details for the given order IDs by making individual requests for each ID.
     *
     * @param string[] $orderIds a list of order IDs to fetch order details for
     *
     * @return OrderResponse[] an associative array where the key is the order ID and the value is the order data object
     *
     * @throws ResponseException
     * @throws UnresponsivenessException
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
     * Retrieves a way bill by order ID from the API and returns it as a WayBill object.
     *
     * @param string $orderId the unique identifier of the order for which to retrieve the way bill
     *
     * @return WayBill the parsed the way bill data as a WayBill object
     *
     * @throws GuzzleException
     * @throws \JsonException
     * @throws ResponseException
     * @throws UnresponsivenessException
     */
    public function getWayBill(string $orderId): WayBill
    {
        $endpoint = 'waybill/' . $orderId . '/';

        try {
            $response = $this->makeRequest('POST', $endpoint, [
                'form_params' => $this->buildRequest([], $endpoint),
            ]);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);

        return Json::mapArrayToObject($response, WayBill::class);
    }

    /**
     * Cancels an order by its ID using a POST request to the API endpoint.
     *
     * @param string $orderId the unique identifier of the order to be canceled
     *
     * @return bool true if the cancellation request was successfully sent, false otherwise
     *
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function cancelOrder(string $orderId): bool
    {
        $endpoint = 'cancel_order/' . $orderId . '/';

        try {
            $this->makeRequest('POST', $endpoint, [
                'form_params' => $this->buildRequest([], $endpoint),
            ]);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        return true;
    }

    /**
     * Retrieves a list of turn-in records for the given order IDs.
     *
     * @param string[] $orderIds a list of order IDs to fetch turn-in records for
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
        $endpoint = 'turn_in/';

        try {
            $response = $this->makeRequest('POST', $endpoint, [
                'form_params' => $this->buildRequest(['order_ids' => $orderIds], $endpoint),
            ]);
        } catch (ApiRequestException $e) {
            throw $this->handleRequestException($e);
        }

        $response = $this->parseResponse($response);

        return Json::mapArrayToObject($response, TurnIn::class);
    }

    /**
     * Retrieves a list of contacts from the API.
     *
     * This method is not supported and will throw an UnsupportedEndpointException.
     *
     * @throws UnsupportedEndpointException indicates that the contact_list endpoint is not supported
     */
    public function getContactList(): array
    {
        throw new UnsupportedEndpointException('contact_list');
    }

    /**
     * Converts an ApiRequestException into a ResponseException with structured error data.
     */
    private function handleRequestException(ApiRequestException $e): ResponseException
    {
        $apiError = new ApiErrorResponse($e->getMessage(), [], $e->getCode() ?: null);

        return new ResponseException($e->getMessage(), $e->getCode() ?: 400, $e, $apiError);
    }

    /**
     * Parses the raw API response into a structured array and validates its integrity.
     *
     * @param string $response the raw JSON response string from the API
     *
     * @return array the parsed response data, containing the actual response content
     *
     * @throws UnresponsivenessException if the response structure is invalid or missing required fields
     * @throws ResponseException if the response status code is not 200
     * @throws \JsonException if the JSON decoding fails
     */
    private function parseResponse(string $response): array
    {
        $decodedResponse = json_decode($response, true, 10, JSON_THROW_ON_ERROR);

        if (!is_array($decodedResponse) || !isset(
            $decodedResponse['status'],
            $decodedResponse['message'],
            $decodedResponse['response'])
        ) {
            throw new UnresponsivenessException();
        }

        $status = (int) $decodedResponse['status'];

        if ($status !== 200) {
            $apiError = new ApiErrorResponse($decodedResponse['message'], [], $status);
            throw new ResponseException($decodedResponse['message'], $status, null, $apiError);
        }

        return $decodedResponse['response'];
    }

    /**
     * Builds request array for Apaczka
     *
     * @param $data
     * @param $route
     *
     * @return array
     *
     * @throws \JsonException
     */
    private function buildRequest($data, $route): array
    {
        if (isset($this->platformName)) {
            $data['system'] = $this->platformName;
        }

        $data = json_encode($data, JSON_THROW_ON_ERROR);
        $expires = strtotime(self::EXPIRES);

        return [
            'app_id' => $this->config['app_id'],
            'request' => $data,
            'expires' => $expires,
            'signature' => $this->getSignature(
                $this->stringToSign(
                    $this->config['app_id'],
                    $route,
                    $data,
                    $expires
                ),
                $this->config['app_secret']
            ),
        ];
    }
}
