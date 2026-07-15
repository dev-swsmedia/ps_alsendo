## Description
The `cancelOrder` function is used to cancel an order by its ID. It sends a DELETE request to the Ecolet API's order endpoint and returns the parsed response.
## Example Input
``` php
$orderId = 'ORDER123456';
```
## Example Output
``` json
{
  "status": "cancelled",
  "message": "Order cancelled successfully"
}
```
