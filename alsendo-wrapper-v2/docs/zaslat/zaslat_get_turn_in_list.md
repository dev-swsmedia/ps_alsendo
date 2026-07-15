## Description of the Function
The `getTurnInList` function is part of the class `ApiZaslatClient`. It is used to retrieve a list of turn-in (return) orders from an external API.
This function sends a POST request to the endpoint `/api/v1/shipments/manifest`, passing an array of order IDs as part of the JSON payload. The response is then parsed and returned as an array, containing data related to the requested shipments. 
## Example Input for Function
``` php
[
    'orderIds' => [12345, 67890]
]
```
## Example Output for Function
``` json
{
    "data": [
        {
            "orderId": 12345,
            "status": "returned",
            "returnDate": "2025-07-08"
        },
        {
            "orderId": 67890,
            "status": "processing",
            "returnDate": "null"
        }
    ]
}
```

