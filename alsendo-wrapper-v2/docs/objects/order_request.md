# OrderRequest Class

## Overview
The `OrderRequest` class represents a model of an order request based on the Alsendo API Model. It contains properties that define various aspects of an order, including service ID, address, shipment details, and other relevant information.

## Properties

### Common Properties

| Property Name         | Type             | Description |
|-----------------------|------------------|-------------|
| $serviceId            | int|string       | The service ID, which can be an integer for Apaczka and Zaslat, or a string (slug) for Ecolet. |
| $address              | Address          | The address associated with the order. |
| $option               | ?array           | Optional array of additional options. |
| $notification         | Notification     | Notification details for the order. |
| $shipmentValue        | int              | The value of the shipment. |
| $cod                  | ?Cod             | Cash on delivery details, if applicable. |
| $pickup               | Pickup           | Pickup details for the order. |
| $shipment             | array            | Details of the shipment. |
| $comment              | ?string          | Any additional comments for the order. |
| $content              | ?string          | Description of the content being shipped. |
| $isZebra              | int              | Indicator for Zebra-related functionality. |
| $currency             | ?string          | The currency used for the order. |

### Additional Properties

| Property Name         | Type             | Description |
|-----------------------|------------------|-------------|
| $additionalServices   | ?AdditionalService[] | Additional services applicable only in Ecolet. |
| $paymentType          | ?string          | Payment type for Zaslat, applicable only in Zaslat. |
| $carrier              | ?string          | Carrier information, applicable only in Zaslat. |
| $reference            | ?string          | Reference number, applicable only in Zaslat. |
| $promoCode            | ?string          | Promo code or voucher, applicable only in Zaslat. |

## Notes
- The `OrderRequest` class extends the `Request` class.
- Some properties are specific to certain services (e.g., Ecolet, Zaslat, Apaczka).
- The `$additionalServices`, `$paymentType`, `$carrier`, and `$reference` properties are only applicable in certain contexts (e.g., Zaslat).