## Overview

The `ApiApaczkaClient` class is a PHP implementation that provides an interface to interact with the Apaczka API. This class extends the `ApiClient` abstract class and implements the `ApiClientInterface`, allowing for communication with the Apaczka service through HTTP requests.

## Key Features

- **Authentication**: The client uses app_id and app_secret for authentication.
- **API Endpoints**: The class supports multiple endpoints related to order management, service structure, waybill retrieval, and order cancellation.
- **Error Handling**: It includes exception handling for unresponsive API calls, Guzzle exceptions, response exceptions, and JSON parsing errors.
- **Validation**: The client validates incoming orders before sending them to the API (In the future release).

## Usage

To use this class, you need to provide the configuration with `app_id` and `app_secret`. You can also specify whether to use the test environment or not.

```php
$config = [
    'app_id' => 'your_app_id',
    'app_secret' => 'your_app_secret'
];

$client = new ApiApaczkaClient($config, true); // true for test environment
```


## Methods

- `getServiceStructure()`: Retrieves the service structure from the API.
- `getOrderValuation(OrderRequest $order)`: Gets the valuation of an order.
- `sendOrder(OrderRequest $order)`: Sends an order to the Apaczka service.
- `getOrder($orderId)`: Retrieves an order by its ID.
- `getOrders(array $orderIds)`: Retrieves multiple orders by their IDs.
- `getWayBill($orderId)`: Gets the waybill information for an order.
- `cancelOrder($orderId)`: Cancels an order.
- `getTurnInList(array $orderIds)`: Retrieves turn-in information for orders.
