# Courier Class
## Overview
This document describes the structure of the class, which is part of the namespace. The class extends the `Request` class and represents a courier object in the context of an order. `Courier``Alsendo\AlsendoWrapper\Api\Ecolet\Model\Order`
## Properties Description

| Property      | Type            | Description                                                                                    |
|---------------|-----------------|------------------------------------------------------------------------------------------------|
| `$service`    | `string`        | Represents the service associated with the courier.                                            |
| `$pickup`     | `CourierPickup` | An instance of the class, which contains information about the pickup details. `CourierPickup` |
| `$contractId` | `?int`          | An optional integer representing the contract ID associated with the courier.                  |
## Related Classes
### `CourierPickup`
- **Class Name**: `CourierPickup`
- **Namespace**: `Alsendo\AlsendoWrapper\Api\Ecolet\Model\Order`
- **Description**: This class represents pickup details for a courier, including type, date, and time.

### `Service`
- **Class Name**: `Service`
- **Namespace**: `Alsendo\AlsendoWrapper\Api\Ecolet\Model\Order`
- **Description**: This class represents a service, with properties such as code and data.

### `PickupType`
- **Class Name**: `PickupType`
- **Namespace**: `Alsendo\AlsendoWrapper\Api\Ecolet\Model\Order`
- **Description**: This class represents the type of pickup, including type and description.

### `ServiceCourier`
- **Class Name**: `ServiceCourier`
- **Namespace**: `Alsendo\AlsendoWrapper\Api\Ecolet\Model\Order`
- **Description**: This class represents a service courier, with properties such as ID, slug, name, and status.

## Additional Information
The class is part of the broader namespace and is used in the context of sending orders to shipping services. It leverages the `Request` class for common functionality and extends it with specific properties related to couriers and their associated pickup details.
