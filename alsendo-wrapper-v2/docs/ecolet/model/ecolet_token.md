# Token Class
## Class Overview
The `Token` class is used to represent an access token and its associated information. It provides methods to construct the object from data, check if the token is expired, convert it to an array, and serialize it to JSON.
## Properties

| Property Name  | Type     | Description                            |
|----------------|----------|----------------------------------------|
| `tokenType`    | `string` | The type of the token (e.g., "Bearer") |
| `expiresAt`    | `int`    | The timestamp when the token expires   |
| `accessToken`  | `string` | The actual access token                |
| `refreshToken` | `string` | The refresh token to renew the access  |
## Services
None of the properties are specific to a particular service.
## Methods
- `__construct(array $data)` : Initializes the `Token` object from an array of data. 
- `getAccessToken(): string` : Returns the access token. 
- `isExpired(): bool` : Checks if the token is expired. 
- `toArray(): array` : Converts the object into an associative array. 
- `toJson(): string` : Serializes the object to a JSON string. 
- `fromJson(string $json): ?self` : Creates a `Token` object from a JSON string. 

## Usage
The `Token` class is used in conjunction with other classes such as `EcoletOAuthClient`, which retrieves tokens from an API, and `EcoletTokenStorage`, which saves the token to a file. The `fromJson` method allows for easy parsing of JSON data into a `Token` object. 
This class provides a simple and effective way to handle authentication tokens in a PHP application.
