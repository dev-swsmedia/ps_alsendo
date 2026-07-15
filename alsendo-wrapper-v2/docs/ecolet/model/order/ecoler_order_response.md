# Class `EcoletOrderResponse`
## Description of the Class
This class represents the response from an order created via the Ecolet API. It extends the class and includes additional properties related to shipment, services, fees, and statuses. `OrderResponse`
## Structure of Properties

| Property                   | Type                | Description                                                   |
|----------------------------|---------------------|---------------------------------------------------------------|
| `id`                       | `int`               | The unique identifier of the order.                           |
| `service`                  | `Service`           | An instance of the service associated with the order.         |
| `shipmentType`             | `string`            | The type of shipment for the order.                           |
| `primaryOrderAwb`          | `?string`           | The primary order AWB (Air Waybill) number, if available.     |
| `awb`                      | `string`            | The AWB (Air Waybill) number for the order.                   |
| `waybillExtension`         | `string`            | The extension of the waybill.                                 |
| `waybillHasBeenDownloaded` | `bool`              | A boolean indicating whether the waybill has been downloaded. |
| `vat`                      | `int`               | The value-added tax (VAT) amount for the order.               |
| `fees`                     | `array`             | An array of fee items associated with the order.              |
| `updatedAt`                | `DateTimeImmutable` | The date and time when the order was last updated.            |
| `createdAt`                | `DateTimeImmutable` | The date and time when the order was created.                 |
| `statuses`                 | `array`             | An array of status items related to the order.                |
## Properties for Services
The `service` property is an instance of the `Service` class, which includes the following properties:

| Property      | Type     | Description                                          |
|---------------|----------|------------------------------------------------------|
| `slug`        | `string` | The slug (short name) of the service.                |
| `fullName`    | `string` | The full name of the service.                        |
| `courierSlug` | `string` | The slug of the courier associated with the service. |
| `courierName` | `string` | The name of the courier associated with the service. |
## Properties for Fees
The `fees` property is an array of fee items, each of which includes the following properties:

| Property | Type     | Description           |
|----------|----------|-----------------------|
| `type`   | `string` | The type of the fee.  |
| `value`  | `mixed`  | The value of the fee. |
## Properties for Statuses
The `statuses` property is an array of status items, each of which includes the following properties:

| Property | Type     | Description              |
|----------|----------|--------------------------|
| `type`   | `string` | The type of the status.  |
| `value`  | `mixed`  | The value of the status. |
## Additional Information
- This class is part of the Ecolet API integration and is used to represent the response from an order creation request.
- It includes properties that are mapped to specific fields in the JSON response from the Ecolet API.
- The method `getPropertyTypeMap()` defines how the properties are mapped when deserializing JSON data into this class. 
