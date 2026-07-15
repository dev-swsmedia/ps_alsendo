# ApiEcoletClient Class

## Description of the Class
The `ApiEcoletClient` class is an implementation of the `ApiClientInterface` that provides methods to interact with the Ecolet API. It handles authentication, makes requests to the API endpoints, and processes the responses.

## Structure of Properties

| Property Name | Type    | Description                                                  |
|---------------|---------|--------------------------------------------------------------|
| `config`      | `array` | Configuration array containing API credentials and settings. |
| `options`     | `array` | Options for HTTP requests, such as headers and debug mode.   |

## Methods
- `__construct(array $config, bool $test = false)`: Initializes the client with configuration and test flag.
- `authorization(): void`: Sets up authorization headers for API requests.
- `getMe()`: Retrieves information about the current user.
- `getServiceStructure()`: Gets the structure of available services from the Ecolet API.
- `getOrderValuation(OrderRequest $order)`: Calculates order valuations based on provided order data.
- `sendOrder(OrderRequest $order)`: Sends an order to the Ecolet API for processing.
- `getOrder($orderId)`: Retrieves details of a specific order by its ID.
- `getWayBill($orderId)`: Downloads the waybill for a given order.
- `cancelOrder($orderId)`: Cancels an existing order.
- `getTurnInList(array $orderIds)`: Retrieves a list of orders to be turned in (not implemented).
- `getOrders(array $orderIds)`: Retrieves multiple orders by their IDs (not implemented).
- `queryLocation(LocationQueryRequest $locationQueryRequest)`: Retrieves a list of locations based on country code and city (returns an array).

## Key Features
- **Authentication**: Uses Bearer token for secure API access.
- **Error Handling**: Throws exceptions for validation errors and unsupported endpoints.
- **Modular Design**: Follows a consistent structure for making API requests and handling responses.

## Usage Example

```php
$config = [
    'token' => 'your_token_here',
];

$client = new ApiEcoletClient($config, true); // 'true' indicates test environment

try {
    $user = $client->getMe();
    echo "User: " . json_encode($user) . "\n";

    $serviceStructure = $client->getServiceStructure();
    echo "Service Structure: " . json_encode($serviceStructure) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```


This class provides a comprehensive interface to the Ecolet API, enabling developers to interact with the service efficiently and securely.

### queryLocation
Retrieves a list of locations for a given country code and city.

- Endpoint: `/locations/{country_code}/localities/{city}`
- Parameters: `LocationQueryRequest` with `countryCode` and `city`
- Return type: array (the JSON response is parsed into a PHP array)

Example response (parsed to PHP array; JSON shape shown below):

```
{
  "localities": [
    {
      "id": 13751,
      "name": "Bucuresti",
      "municipality": "Sectorul 1",
      "postal_code": "011318",
      "has_streets": true,
      "county": {
        "id": 10,
        "name": "Bucuresti",
        "code": "B"
      }
    }
  ]
}
```

Notes:
- The request is validated against `ValidationRules::LOCATION_QUERY` and may throw `ValidationException` if invalid.
- On HTTP or parsing issues, `GuzzleException` or `JsonException` may be thrown.
