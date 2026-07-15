## Function Description
The `sendOrder` function is responsible for sending an order request to the API endpoint. It performs validation on the provided object using a validator, and if there are any errors during validation, it throws a `ValidationException`. If the validation passes, it sends the order data via an HTTP POST request to the specified endpoint and returns the parsed response as an instance of . `OrderRequest``ApaczkaOrderCreatedResponse`
## Example Input
``` php
$orderRequest = new OrderRequest();
$orderRequest->serviceId = 1;
$orderRequest->address = new Address(
    new Contact('PL', 'Jan Kowalski', 'ul. XYZ 123', '', '123456789'),
    new Contact('PL', 'Jan Nowak', 'ul. ABC 456', '', '987654321')
);
$orderRequest->notification = new Notification(
    new NotificationDetail('email', 'new_order@example.com'),
    new NotificationDetail('sms', '+48123456789'),
    new NotificationDetail('email', 'exception@example.com'),
    new NotificationDetail('sms', '+48987654321')
);
$orderRequest->shipmentValue = 100;
$orderRequest->pickup = new Pickup(
    null,
    '2025-07-08',
    '09:00',
    '17:00'
);
$orderRequest->shipment = [
    ['weight' => 1.5, 'length' => 30, 'width' => 20, 'height' => 10]
];
```
## Example Output
``` php
ApaczkaOrderCreatedResponse {
    id => "order_123456789",
    status => "created",
    service_id => 1,
}
```
