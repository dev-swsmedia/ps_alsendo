# Readme.md

## Project Overview

This project is a PHP library that provides an API client factory for various logistics services. The main class, `ApiClientFactory`, is responsible for creating instances of different API clients based on the specified service and configuration.

## Features

- Factory pattern for creating API clients
- Configuration validation
- Support for multiple logistics providers (Apaczka, Ecolet, Zaslat)
- Test environment support

## Installation

You can install this package via Composer:

```shell script
composer require alsendo/alsendo-wrapper
```


## Usage

To use the API client factory, you need to create an instance of `ApiClientFactory` and call the `create()` method with the desired API name and configuration.

```php
$factory = new \Alsendo\AlsendoWrapper\ApiClientFactory();

$config = [
    'api_key' => 'your_api_key',
    'secret' => 'your_secret'
];

$client = $factory->create('apaczka', $config);
```


## Configuration

The configuration array should contain the necessary parameters for the specific API client. The required fields depend on the service being used.

For example, for Apaczka:

```php
$config = [
    'api_key' => 'your_api_key',
    'secret' => 'your_secret',
    'test_mode' => true
];
```
## Additional Documentation

Additional documentation can be found in the [docs](docs/) directory.


## Supported APIs

- Apaczka (production and test environments)
- Ecolet (production and test environments)
- Zaslat (production and test environments)

## Contributing

Contributions are welcome! Please follow the standard contribution guidelines for the project.

## License

This code is released under the MIT License. See the LICENSE file for details.

## Contact

For questions or feedback, please contact the maintainers of the project.