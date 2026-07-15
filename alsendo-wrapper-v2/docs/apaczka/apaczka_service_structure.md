## Function Description
The function is responsible for retrieving the service structure from an external API. It sends a POST request to the specified endpoint, processes the response, and maps the JSON response to an object of type . `getServiceStructure()``ServiceStructure`
## Example Input
``` php
// No direct input is required for this function as it internally handles the request.
```
## Example Output
``` php
{
    "services": [
        {
            "id": 1,
            "name": "Service A",
            "type": "shipping"
        },
        {
            "id": 2,
            "name": "Service B",
            "type": "pickup"
        }
    ],
    "options": [
        {
            "id": 1,
            "name": "Option A"
        },
        {
            "id": 2,
            "name": "Option B"
        }
    ],
    "package_type": [
        {
            "id": 1,
            "name": "Package Type A"
        },
        {
            "id": 2,
            "name": "Package Type B"
        }
    ]
}
```