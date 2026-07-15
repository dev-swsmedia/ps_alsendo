# Class `ApiZaslatClient`

## Description of the Class
The `ApiZaslatClient` class is an implementation of the `ApiClientInterface` that interacts with the Zaslat API to manage orders and other related operations. It extends the `ApiClient` abstract class and provides specific functionality for connecting to the Zaslat service.

## Structure of Properties

| Property  | Type     | Description                                                         |
|-----------|----------|---------------------------------------------------------------------|
| `apiUrl`  | `string` | The base URL of the API endpoint.                                   |
| `config`  | `array`  | Configuration parameters for the API client, including the API key. |
| `options` | `array`  | Additional options and headers to be used in HTTP requests.         |

## Usage of the Object

The `ApiZaslatClient` is typically instantiated with a configuration array that includes the API key and a boolean flag indicating whether to use the test or live endpoint.
```php
$client = new ApiZaslatClient([
'api_key' => 'your_api_key_here',
], true); // if true, Use test endpoint
```
## Methods

- `__construct(array $config, bool $test = false)`: Initializes the client with configuration and determines which API endpoint to use.
- `authorization(): void`: Sets up HTTP headers for API requests, including the API key.
- `getServiceStructure()`: Throws an exception indicating that this endpoint is not supported.
- `getOrderValuation(OrderRequest $order): array`: Retrieves order valuation data from the API.
- `sendOrder(OrderRequest $order): ZaslatOrderCreatedResponse`: Sends an order to the API and returns a response object.
- `getOrder($orderId): OrderResponse`: Retrieves detailed information about a specific order by ID.

## Key Features

- **API Key Authentication**: Use an API key for authentication with the Zaslat service.
- **Endpoint Flexibility**: Supports both test and live endpoints.
- **Validation**: Ensures that input data meets required criteria before sending requests.
- **Response Parsing**: Parses API responses into appropriate objects for easy access to data.

## Additional Information

The `ApiZaslatClient` works in conjunction with other classes such as `OrderRequest`, `ZaslatOrderValuation`, and `ZaslatOrderCreatedResponse` to handle order-related operations. It is designed to be part of a larger system that interacts with the Zaslat API for shipping and logistics management.

