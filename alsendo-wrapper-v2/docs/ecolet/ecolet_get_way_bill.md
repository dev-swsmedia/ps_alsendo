## Description
The `getWayBill` function is used to retrieve a waybill for an order. It sends a GET request to the `/api/v1/order/{orderId}/download-waybill` endpoint.
## Example Input
``` php
$orderId = '12345';
```
## Example Output
``` json
{
    "waybill": "ABC123",
    "type": "PDF"
}
```
