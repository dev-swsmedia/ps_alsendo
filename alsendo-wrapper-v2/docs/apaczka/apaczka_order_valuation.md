# Function Description

The `getOrderValuation` function is responsible for calculating the valuation of an order. It validates the input order request, sends it to an API endpoint, and then processes the response to extract relevant information about the order's valuation.

## Example Input

```php
$orderRequest = new OrderRequest();
$orderRequest->serviceId = 82;
$orderRequest->address = new Address(
    new Contact('John Doe', 'john.doe@example.com', '+1234567890'),
    '123 Main St',
    'City',
    'State',
    'Country'
);
$orderRequest->shipmentValue = 100;
$orderRequest->pickup = new Pickup('2025-07-10T08:00:00+02:00');
$orderRequest->shipment = ['item' => 'Product A', 'quantity' => 2];
```


## Example Output

```php
[
    "82" => {
        "serviceId": "82",
        "carrier": "Ecolet",
        "service": "Standard",
        "priceTable": [
            {"date": "2025-07-10", "price": "10.00"},
            {"date": "2025-07-11", "price": "12.00"}
        ],
        "pickupDate": "2025-07-10T08:00:00+02:00",
        "deliveryDate": "2025-07-12T12:00:00+02:00"
    }
]
```


## Function Description

The `getOrderValuation` function takes an `OrderRequest` object as input and returns an array of order valuations. It first validates the request using a `Validator`, which checks if all required fields are present and in the correct format. If there are any errors, it throws a `ValidationException`.

If the validation is successful, it sends the request to an API endpoint (`/api/v2/add-parcel/reload-form`) with the order data. The response from the API is then parsed and processed to extract information about the order's valuation, such as service ID, carrier, price table, pickup and delivery dates, etc.

The function returns an array of order valuations, where each key is a service ID and the value is an object containing details about that service.