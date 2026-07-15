# Readme.md for getOrderValuation Function

## Description of the Function

The `getOrderValuation` function is responsible for retrieving and processing order valuation information from an API endpoint. It takes an `OrderRequest` object as input, validates it using a set of rules, sends it to an external service (via a POST request), and then processes the returned response to extract and format relevant data into an array of `EcoletOrderValuation` objects.

## Example Input

```php
$orderRequest = new OrderRequest();
$orderRequest->serviceId = 82;
$orderRequest->address = new Address(
    new Contact(
        'PL',
        $this->faker->name(),
        $this->faker->streetAddress(),
        '',
        $this->faker->postcode(),
        '',
        $this->faker->city(),
        1
    ),
    new Address(
        'PL',
        $this->faker->name(),
        $this->faker->streetAddress(),
        '',
        $this->faker->postcode(),
        '',
        $this->faker->city(),
        2
    )
);
$orderRequest->shipmentValue = 100;
```


## Example Output

```php
[
    'service_id_1' => [
        'service_id' => 'service_id_1',
        'status' => true,
        'additional_services' => [],
        'pickup_dates' => ['2025-07-08', '2025-07-09'],
        'is_standard' => true,
        'price' => [
            'net' => '100,00',
            'gross' => '120,00'
        ],
        'fees' => ['fee_1', 'fee_2'],
        'vat' => 23,
        'info' => ['info_1', 'info_2'],
        'errors' => ['error_1']
    ],
    'service_id_2' => [
        'service_id' => 'service_id_2',
        'status' => false,
        'additional_services' => ['service_3'],
        'pickup_dates' => [],
        'is_standard' => false,
        'price' => [
            'net' => '50,00',
            'gross' => '60,00'
        ],
        'fees' => [],
        'vat' => 17,
        'info' => ['info_3'],
        'errors' => []
    ]
]
```