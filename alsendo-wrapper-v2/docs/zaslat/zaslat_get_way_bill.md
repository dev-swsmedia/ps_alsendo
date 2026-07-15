## Description of the Function
The `getWayBill` function is used to retrieve the waybill information for a specific order. It sends a POST request to the endpoint `/api/v1/shipments/label` with the provided order ID and returns the response data. 
## Example Input
``` php
$orderId = '123456'; // Replace with an actual order ID
```
## Example Output
``` json
{
    "waybill": "ABC123456",
    "type": "standard"
}
```
