# AdditionalService Class Structure

## Overview

The `AdditionalService` class is an extension of the `Request` class. It represents additional service information for a request, including status and amount.

## Properties

| Property  | Type   | Description                             |
|-----------|--------|-----------------------------------------|
| `$status` | `bool` | Indicates the status of the service.    |
| `$amount` | `int`  | The amount associated with the service. |

## Services Information

### For `AdditionalService` class:

- **Class Name**: `AdditionalService`
- **Namespace**: `Alsendo\AlsendoWrapper\Api\Ecolet\Model`

## Related Classes

- `Request`: The base class that `AdditionalService` extends from.
- `Service`: A base class for representing services, though it's not directly used in this class.
- `ServiceCourier`: Represents a courier service, but it's not directly related to `AdditionalService`.
- `Cod`: Represents cash on delivery information, which is a different type of service.

## Notes

- The `AdditionalService` class does not use any specific service-related properties or methods beyond those defined in the `Request` class.
- It is designed to provide additional information about a request, such as status and amount, which can be used for various purposes like tracking or processing.