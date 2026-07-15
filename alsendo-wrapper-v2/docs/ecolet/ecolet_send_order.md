## Function Description
The `sendOrder` function is responsible for sending an order to the Ecolet API. It validates the provided object, wraps it into an , makes a POST request to the endpoint, and then retrieves the response data to return the final order information. `OrderRequest``EcoletOrderRequest``/api/v2/add-parcel/send-order`
## Example Input
``` php
$orderRequest = new OrderRequest();
$orderRequest->serviceId = 1;
$orderRequest->address = new Address(
    new Contact('PL', 'John Doe', 'Main Street 123', '', '12345', ''),
    new Address('PL', 'Jane Smith', 'Broadway 456', '', '67890', '')
);
$orderRequest->pickup = new Pickup();
$orderRequest->pickup->type = 'home';
$orderRequest->pickup->date = '2025-07-10';
$orderRequest->pickup->hoursFrom = '09:00';
$orderRequest->shipment = [
    (new Shipment())
        ->setShipmentTypeCode('Parcel')
        ->setWeight(1.5)
        ->setAmount(50)
        ->setDimensions(20, 15, 10)
        ->setObservations('Sample observation')
        ->setShape('Rectangular')
];
```
## Example Output
``` php
{
    "order_id": "123456789",
    "status": "created",
    "order.service_id": 1,
    "order.data.receiver": {
        "name": "Jane Smith",
        "address": "Broadway 456",
        "postcode": "67890"
    }
}
```
