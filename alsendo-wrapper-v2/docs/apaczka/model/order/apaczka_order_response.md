# ApaczkaOrderResponse Class Documentation

## Overview

The `ApaczkaOrderResponse` class is an extension of the `OrderResponse` class, representing a response from the Apaczka API related to an order. This class defines the structure of the data that can be returned from the API, including properties and their corresponding mappings.

## Properties Structure

| Property          | Type    | Description                                                                                             |
|-------------------|---------|---------------------------------------------------------------------------------------------------------|
| id                | string  | Unique identifier for the order                                                                         |
| supplier          | object  | Information about the supplier, with class name `Supplier` and namespace `Alsendo\AlsendoWrapper\Model` |
| service_id        | string  | Identifier for the service used in the order                                                            |
| waybill_number    | string  | Waybill number for tracking the shipment                                                                |
| pickup            | object  | Pickup information, with class name `Pickup` and namespace `Alsendo\AlsendoWrapper\Model`               |
| pickup_number     | string  | Number associated with the pickup                                                                       |
| tracking_url      | string  | URL to track the order                                                                                  |
| status            | string  | Status of the order                                                                                     |
| shipments_count   | integer | Number of shipments in the order                                                                        |
| shipments         | object  | Array of shipment details, with class name `Shipment` and namespace `Alsendo\AlsendoWrapper\Model`      |
| content           | string  | Content associated with the order                                                                       |
| comment           | string  | Comment related to the order                                                                            |
| sender            | object  | Sender information, with class name `Contact` and namespace `Alsendo\AlsendoWrapper\Model`              |
| receiver          | object  | Receiver information, with class name `Contact` and namespace `Alsendo\AlsendoWrapper\Model`            |
| created           | string  | Date and time when the order was created                                                                |
| delivered         | string  | Date and time when the order was delivered                                                              |
| price             | string  | Price of the order                                                                                      |
| price_var         | string  | Variable price of the order                                                                             |
| price_gross       | string  | Gross price of the order                                                                                |
| cod               | boolean | Whether the order is paid in cash on delivery                                                           |
| declaration_value | string  | Declaration value for customs purposes                                                                  |

## Service-Specific Properties

| Service | Property     | Type   | Description                           |
|---------|--------------|--------|---------------------------------------|
| Apaczka | service_name | string | Name of the service used in the order |

## Notes

- The `pickup` property contains detailed information about the pickup time and location, including date and hours.
- The `shipments` property is an array of shipment details, each containing information such as weight, dimensions, and price.
- The `sender` and `receiver` properties are instances of the `Contact` class, which includes contact details and address information.

This documentation provides a comprehensive overview of the `ApaczkaOrderResponse` class, including its properties and their corresponding data types and structures.