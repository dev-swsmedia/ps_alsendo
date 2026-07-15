## Function Description
The `getOrder` function is used to retrieve an order from the Ecolet API by its ID. It sends a GET request to the appropriate endpoint and returns an instance of the `OrderResponse`
## Example Input
``` php
'IZ07BFA2EE08'
```
## Example Output
``` php
EcoletOrderResponse Object
(
    [id] => 123456789
    [order_number] => ORDER123456789
    [status] => 1
    [created_at] => 2025-07-08T10:00:00+00:00
    [updated_at] => 2025-07-08T10:00:00+00:00
    [customer] => 
    [sender] => 
    [recipient] => 
    [pickup] => 
    [delivery] => 
    [payment] => 
    [courier] => 
    [services] => 
    [notifications] => 
)
```
