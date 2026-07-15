## Overview

`GeoUtils` provides static utility methods for geographic distance calculations and sorting.

Used internally by `ZaslatPointService` and `EcoletPointService` to sort pickup points by proximity.

## Methods

### calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float

Calculates the distance between two geographic points using the Haversine formula.

Returns distance in kilometers.

```php
//  -> Brno ~ 185 km
$distance = GeoUtils::calculateDistance(50.0755, 14.4378, 49.1951, 16.6068);
```

### sortByDistance(Point[] $points, float $lat, float $lon): Point[]

Sorts an array of Point objects by distance from the given coordinates.

- Sets the `distance` field on each point (in km)
- Points with null coordinates get `PHP_FLOAT_MAX` distance (sorted last)
- Returns a new sorted array

```php
$sorted = GeoUtils::sortByDistance($points, 50.0755, 14.4378);
// $sorted[0] is the closest point
// $sorted[0]->distance contains the distance in km
```
