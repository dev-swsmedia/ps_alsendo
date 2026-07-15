## Description of the function
The `sendOrder` function is responsible for sending an order request to a shipping service. It performs validation on the provided object and then sends it to the appropriate API endpoint. `OrderRequest`
## Function Parameters
- `OrderRequest $order`: An instance of the class `OrderRequest` that contains all the data needed to create an order. 

## Function Behavior
1. The function `OrderRequest` first validates the object using a validator. 
2. If validation fails, it throws a `ValidationException` with the errors found.
3. If the validation is successful, it wraps the `OrderRequest` into a `ZaslatOrderRequest` object that is suitable for sending to the API. 
4. It then sends a POST request to the endpoint `/api/v1/shipments/create` with the wrapped order request as JSON data. 
5. The response from the API is parsed and converted into a `ZaslatOrderCreatedResponse` object, which is returned. 

## Example Input
``` php
$orderRequest = new OrderRequest();
$orderRequest->serviceId = 1;
$orderRequest->address = new Address(
    new Contact('sender@example.com', 'Sender Name'),
    new Address('receiver@example.com', 'Receiver Name')
);
$orderRequest->pickup = new Pickup(new \DateTimeImmutable('2025-07-08 10:00:00'));
$orderRequest->shipmentValue = 100;
$orderRequest->shipment = [
    new Shipment(
        weight: 1.5,
        dimension1: 20,
        dimension2: 30,
        dimension3: 40
    )
];
```
## Example Output
``` json
{
    "id": "123456",
    "shipments": [
        {
            "id": "789012",
            "status": "created"
        }
    ]
}
```
