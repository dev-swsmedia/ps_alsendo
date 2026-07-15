# EcoletOrderRequest Class

## Overview
The `EcoletOrderRequest` class represents an order request in the Ecolet system. It extends the `Request` class and contains properties that define the details of an order.

## Properties

### Main Properties
| Property              | Type      | Description                                            |
|-----------------------|-----------|--------------------------------------------------------|
| `$sender`             | `Contact` | The sender's contact information.                      |
| `$receiver`           | `Contact` | The receiver's contact information.                    |
| `$parcel`             | `Parcel`  | The parcel details associated with the order.          |
| `$additionalServices` | `array`   | A list of additional services requested for the order. |
| `$courier`            | `Courier` | The courier responsible for delivering the order.      |

### Optional Properties
| Property         | Type       | Description                                                  |
|------------------|------------|--------------------------------------------------------------|
| `$parcels`       | `array`    | A list of parcels associated with the order.                 |

## Related Classes

### Contact
- **Class Name**: `Contact`
- **Namespace**: `Alsendo\AlsendoWrapper\Model`
- **Description**: Represents contact information for a person or company.

### Parcel
- **Class Name**: `Parcel`
- **Namespace**: `Alsendo\AlsendoWrapper\Api\Ecolet\Model`
- **Description**: Represents parcel details associated with an order.

### Courier
- **Class Name**: `Courier`
- **Namespace**: `Alsendo\AlsendoWrapper\Api\Ecolet\Model`
- **Description**: Represents the courier responsible for delivering an order.

## Notes
- The `$parcels` property is optional and can be used to handle multiple parcels in a single order.
- The `$additionalServices` property can be used to specify additional services requested for the order.