# OrderResponse Class
## Overview
The class `OrderResponse` is representing a response from an order API, containing information about an order and its related shipments.  
## Properties Structure

| Property Name       | Type                | Description                                           |
|---------------------|---------------------|-------------------------------------------------------|
| `$id`               | `string`            | Unique identifier for the order.                      |
| `$supplier`         | `string`            | Name of the supplier.                                 |
| `$serviceId`        | `string`            | Identifier for the service associated with the order. |
| `$serviceName`      | `string`            | Name of the service.                                  |
| `$waybillNumber`    | `string`            | Waybill number for the shipment.                      |
| `$pickup`           | `Pickup`            | Object representing pickup information.               |
| `$pickupNumber`     | `string`            | Number associated with the pickup.                    |
| `$trackingUrl`      | `string`            | URL for tracking the order.                           |
| `$status`           | `string`            | Current status of the order.                          |
| `$shipmentsCount`   | `int`               | Total number of shipments in the order.               |
| `$shipments`        | `array`             | Array of shipment objects.                            |
| `$content`          | `string`            | Additional content related to the order.              |
| `$comment`          | `string`            | Comment associated with the order.                    |
| `$sender`           | `Contact`           | Object representing the sender of the order.          |
| `$receiver`         | `Contact`           | Object representing the receiver of the order.        |
| `$created`          | `DateTimeImmutable` | Date and time when the order was created.             |
| `$delivered`        | `DateTimeImmutable` | Date and time when the order was delivered.           |
| `$price`            | `float`             | Price of the order.                                   |
| `$priceVar`         | `float`             | Variable price component of the order.                |
| `$priceGross`       | `float`             | Gross price of the order.                             |
| `$cod`              | `float`             | Cash on delivery amount for the order.                |
| `$declarationValue` | `float`             | Declaration value for customs purposes.               |
## Related Classes
- **`Pickup`**: Represents pickup information.
- **`Contact`**: Represents contact information for a party involved in the order.

## Notes
- The class is initialized with default instances of and . `OrderResponse` `Pickup` `Contact`
- The property is an array that can hold multiple shipment objects. `$shipments`
