# README.md for getServiceStructure Function

## Function Description

The `getServiceStructure` function is used to retrieve the service structure from an API endpoint. It makes a GET request to the `/api/v1/services` endpoint, parses the response, and maps the JSON data to an object of type `EcoletServiceStructure`.

## Example Input

```php
// No direct input is required for this function as it makes a request to an external API.
```


## Example Output

The output is an instance of the `EcoletServiceStructure` class, which contains arrays of services, options, package types, and points type.

```php
{
    "services": [
        {
            "id": 1,
            "slug": "service-1",
            "name": "Service 1",
            "status": true
        }
    ],
    "options": [],
    "package_type": [],
    "points_type": []
}
```
