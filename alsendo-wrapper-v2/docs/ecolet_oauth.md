## Overview
The EcoletOAuthClient is a PHP class that provides an interface to the Ecolet OAuth2 authentication system. It allows developers to interact with the Ecolet API using various grant types, such as authorization code, password, and refresh token. `EcoletOAuthClient`
This class is part of the namespace `Alsendo\AlsendoWrapper\Api\Ecolet` and is designed to be used in conjunction with other classes and services within the Alsendo wrapper library. 
## Features
- Supports multiple grant types: authorization code, password, and refresh token.
- Provides methods for generating OAuth2 authorization URLs.
- Allows exchanging authorization codes for access tokens.
- Includes a method for refreshing expired access tokens using a refresh token.

## Usage
To use the `EcoletOAuthClient`, you need to provide the following configuration: 
``` php
$config = [
    'client_id' => 'your_client_id',
    'client_secret' => 'your_client_secret',
    'redirect_uri' => 'your_redirect_uri',
];
```
Then, create an instance of the `EcoletOAuthClient` with the configuration and test flag (optional): 
``` php
$oauthClient = new EcoletOAuthClient($config, $test);
```
### Example Usage
Here is an example of how to use the `EcoletOAuthClient`
``` php
// Create a configuration array
$config = [
    'client_id' => 'your_client_id',
    'client_secret' => 'your_client_secret',
    'redirect_uri' => 'https://your-app.com/callback',
];

// Create an instance of the EcoletOAuthClient
$oauthClient = new EcoletOAuthClient($config);

// Generate the OAuth2 authorization URL
$authorizationUrl = $oauthClient->getAuthorizationUrl('state');

// Exchange the authorization code for an access token
$accessToken = $oauthClient->getAccessToken('your_authorization_code');
```
## Notes
- The uses Guzzle HTTP client to send requests to the Ecolet API. `EcoletOAuthClient`
- The class supports both production and test environments by allowing a test flag in the constructor.
- The `sendRequest` method is a private method that sends HTTP requests to the Ecolet API using the Guzzle client.
