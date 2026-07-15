# Conditions Class
## Overview
The class represents a set of boolean flags that indicate various conditions or features related to an order or shipment. Each property is a boolean flag that can be set to `true` or `false`. `Conditions`
## Properties Structure

| Property Name       | Type | Description                                            |
|---------------------|------|--------------------------------------------------------|
| hasPickupOnlyToday  | bool | Indicates whether the pickup is only allowed on today. |
| hasMultipacks       | bool | Indicates whether multiple packs are allowed.          |
| hasCod              | bool | Indicates whether cash on delivery is available.       |
| hasOpenPackage      | bool | Indicates whether open package delivery is available.  |
| hasRod              | bool | Indicates whether Rod service is available.            |
| hasRop              | bool | Indicates whether ROP service is available.            |
| hasSaturdayDelivery | bool | Indicates whether Saturday delivery is available.      |
| hasSmsNotify        | bool | Indicates whether SMS notification is enabled.         |
| hasSwap             | bool | Indicates whether swap service is available.           |
## Service-Specific Properties
The class does not contain any properties that are specific to a particular service. `Conditions`
## Related Classes and Models
- **`ServiceStructure`**: A class that represents the structure of services, options, package types, etc.
- **`Pickup`**: A class representing pickup information for an order.
- **`Cod`**: A class representing cash on delivery information.
- **`Shipment`**: A class representing shipment details for an order.
