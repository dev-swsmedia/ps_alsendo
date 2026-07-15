## Description of the Function
The `getOrders` function is a method used to retrieve order details from an API endpoint. It takes an array of order IDs as input and returns an associative array of order objects, each associated with its corresponding order ID.
### Parameters
- : An array of order IDs for which the orders need to be retrieved. `array $orderIds`

### Return Value
- : An associative array where the keys are order IDs and the values are objects representing the order details. `array`

## Example Input
``` php
['ORDER123', 'ORDER456']
```
## Example Output
``` php
[
    'ORDER123' => (object) [
        'id' => 'ORDER123',
        'supplier' => 'Supplier Name',
        'serviceId' => 'SERVICE001',
        'serviceName' => 'Standard Service',
        'waybillNumber' => 'WAYBILL123456',
        'pickup' => (object) ['date' => '2025-07-08', 'time' => '10:00'],
        'pickupNumber' => 'PICKUP789',
        'trackingUrl' => 'https://example.com/tracking/123456',
        'status' => 'SHIPPED',
        'shipmentsCount' => 2
    ],
    'ORDER456' => (object) [
        'id' => 'ORDER456',
        'supplier' => 'Supplier Name',
        'serviceId' => 'SERVICE002',
        'serviceName' => 'Express Service',
        'waybillNumber' => 'WAYBILL678901',
        'pickup' => (object) ['date' => '2025-07-08', 'time' => '11:00'],
        'pickupNumber' => 'PICKUP321',
        'trackingUrl' => 'https://example.com/tracking/678901',
        'status' => 'IN_TRANSIT',
        'shipmentsCount' => 1
    ]
]
```

