# Pickup Class

## Overview
The `Pickup` class represents a pickup request in the Alsendo wrapper. It extends the `Request` class and contains properties related to the pickup date and time.

## Properties

| Property       | Type     | Description                        |
|----------------|----------|------------------------------------|
| $type          | ?string  | The type of pickup                 |
| $date          | string   | The date of the pickup             |
| $hoursFrom     | ?string  | The start time of the pickup       |
| $hoursTo       | ?string  | The end time of the pickup         |

## Related Classes
- `PickupType`: Represents the type of pickup.