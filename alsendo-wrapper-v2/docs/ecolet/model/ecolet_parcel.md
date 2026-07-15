# Parcel Class

## Overview
The `Parcel` class represents a parcel in the Alsendo wrapper. It extends the `Request` class and contains properties related to the parcel's characteristics, such as type, weight, dimensions, and declared value.

## Properties

| Property       | Type      | Description                           |
|----------------|-----------|---------------------------------------|
| $type          | ?string   | The type of the parcel                |
| $weight        | string    | The weight of the parcel              |
| $dimensions    | Dimension | The dimensions of the parcel          |
| $shape         | ?string   | The shape of the parcel               |
| $declaredValue | string    | The declared value of the parcel      |
| $amount        | string    | The amount associated with the parcel |
| $content       | ?string   | The content of the parcel             |
| $observations  | ?string   | Observations related to the parcel    |

## Related Classes
- `Dimension`: Represents the dimensions of the parcel.

## Notes
- The `Dimension` class is used to represent the dimensions of the parcel and contains properties for length, width, and height.