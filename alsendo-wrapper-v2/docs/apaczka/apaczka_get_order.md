## Function Description
The `getOrder` function is responsible for retrieving an order from the API endpoint. It sends a POST request to the specified endpoint with the provided order ID and returns the parsed response as an instance of `OrderResponse`
## Example Input
``` php
$orderId = 'IZ07BFA2EE08';
```
## Example Output
``` php
// Assuming the response from the API is successfully parsed
$orderResponse = $this->apiClient->getOrder($orderId);
$this->assertInstanceOf(ZaslatOrderResponse::class, $orderResponse);
```
