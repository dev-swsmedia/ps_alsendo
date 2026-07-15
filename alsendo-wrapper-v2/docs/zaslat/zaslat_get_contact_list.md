### Function `getContactList()`
#### Overview
The function `getContactList()` is part of the API client implementation in the Alsendo wrapper. It retrieves a list of contacts from the Zaslat API by making a GET request to the `/api/v1/contacts/list` endpoint. 
#### Function Description
This function sends a GET request to the specified endpoint, parses the response, and returns an array of objects. Each contact is mapped from the JSON response data using the `Json::mapArrayToObject()` method. `ZaslatContact`
#### Example Input
The function does not require any specific input as it is a simple GET request to the endpoint. `/api/v1/contacts/list`
#### Example Output
``` php
[
    (object) [
        'id' => 1,
        'firstname' => 'John',
        'surname' => 'Doe',
        'company' => 'Example Inc.',
        'street' => 'Main Street',
        'city' => 'New York',
        'zip' => '10001',
        'country' => 'USA',
        'phone' => '+1234567890',
        'email' => 'john.doe@example.com'
    ],
    (object) [
        'id' => 2,
        'firstname' => 'Jane',
        'surname' => 'Smith',
        'company' => 'Example Corp.',
        'street' => 'Broadway',
        'city' => 'Los Angeles',
        'zip' => '90001',
        'country' => 'USA',
        'phone' => '+1234567891',
        'email' => 'jane.smith@example.com'
    ]
]
```
#### Notes
- The function relies on the `makeRequest()` method to send HTTP requests and the `parseResponse()` method to process the response.
- The response is expected to be in JSON format with a status code of 200 indicating success.
- If the response is not successful, an exception will be thrown.
