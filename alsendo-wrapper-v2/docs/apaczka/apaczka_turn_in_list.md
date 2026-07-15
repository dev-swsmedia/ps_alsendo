## Function Description
The `getTurnInList` function is used to retrieve a list of turn-in orders based on the provided order IDs. It sends a POST request to the specified endpoint with the order IDs and returns the response parsed into an object.
## Example Input
``` php
[
    'order_ids' => [101, 102, 103]
]
```
## Example Output
``` json
{
    "status": 200,
    "message": "Success",
    "response": [
        {
            "id": 101,
            "order_id": "ORDER101",
            "created_at": "2025-07-08T12:34:56Z"
        },
        {
            "id": 102,
            "order_id": "ORDER102",
            "created_at": "2025-07-08T12:35:01Z"
        }
    ]
}
```
