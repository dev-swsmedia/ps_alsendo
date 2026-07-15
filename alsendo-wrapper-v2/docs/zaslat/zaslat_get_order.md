## Function Description
The `getOrder` function is used to retrieve an order by its ID from a shipping service. It sends a POST request to the endpoint `/api/v1/shipments/detail` with the given order ID and returns the response in the form of a `ZaslatOrderResponse` object. 
## Example Input
``` php
$orderId = 'IZ07BFA2EE08';
```
## Example Output
``` json
{
    "status": 200,
    "message": "Request successful",
    "data": {
        "IZ07BFA2EE08": {
            "id": "IZ07BFA2EE08",
            "orderNumber": "ORDER123456",
            "date": "2025-07-08T12:34:56Z",
            "status": "Shipped",
            "courier": {
                "name": "John Doe",
                "phone": "+447700900123"
            }
        }
    }
}
```
