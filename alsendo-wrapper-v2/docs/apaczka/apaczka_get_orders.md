## Description of the Function
The `getOrders` function is designed to retrieve multiple order details from an API by providing an array of order IDs. It calls the `getOrder` method for each ID in the input array and returns an associative array where the keys are the order IDs and the values are the corresponding order objects.
## Example Input
``` php
array(
    'ORDER_ID_1',
    'ORDER_ID_2',
    'ORDER_ID_3'
)
```
## Example Output
``` php
array(
    'ORDER_ID_1' => OrderResponse object,
    'ORDER_ID_2' => OrderResponse object,
    'ORDER_ID_3' => OrderResponse object
)
```

