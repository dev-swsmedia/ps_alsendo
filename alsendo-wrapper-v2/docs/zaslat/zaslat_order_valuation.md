## Description of the Function
The `getOrderValuation` function is responsible for retrieving the valuation of an order by making a POST request to an external API endpoint. 
It validates the input object using a predefined set of validation rules, and if valid, it wraps the request data into a suitable format for sending to the API. 
This function is part of a larger system that handles shipping and logistics services, likely involving multiple carriers like Apaczka, Ecolet, and Zaslat. 
The function returns an array of objects `OrderValuation`, which represent the valuation results for different services or rates available for the given order. 
## Example Input
``` php
$orderRequest = new OrderRequest();
$orderRequest->serviceId = 82;
$orderRequest->address = new Address(
    new Contact()
);
$orderRequest->pickup = new Pickup(new DateTimeImmutable('2025-07-08'));
```
## Example Output
``` php
[
    (int) 0 => (
        [
            "serviceId" => null,
            "carrier" => "Zaslat",
            "service" => "Standard",
            "priceTable" => [
                "1" => [
                    "currency" => "PLN",
                    "amount" => 50.0
                ],
                "2" => [
                    "currency" => "PLN",
                    "amount" => 70.0
                ]
            ],
            "pickupDate" => DateTimeImmutable::__set_state([
                "date" => "2025-07-08"
            ])
        ]
    ),
    (int) 1 => (
        [
            "serviceId" => null,
            "carrier" => "Apaczka",
            "service" => "Express",
            "priceTable" => [
                "1" => [
                    "currency" => "PLN",
                    "amount" => 80.0
                ],
                "2" => [
                    "currency" => "PLN",
                    "amount" => 100.0
                ]
            ],
            "pickupDate" => DateTimeImmutable::__set_state([
                "date" => "2025-07-08"
            ])
        ]
    )
]
```

