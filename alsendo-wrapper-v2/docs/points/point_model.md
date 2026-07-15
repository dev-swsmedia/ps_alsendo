## Point Model Reference

`Alsendo\AlsendoWrapper\Api\Points\Model\Point`

All fields are nullable (`?type`), defaulting to `null`.

## Common Fields (all regions)

| Field | Type | Description |
|-------|------|-------------|
| code | string | Unique point identifier |
| name | string | Display name |
| street | string | Street address |
| city | string | City name |
| postalCode | string | Postal/ZIP code |
| latitude | float | GPS latitude |
| longitude | float | GPS longitude |
| country | string | Country code (PL, CZ, RO) |
| carrier | string | Carrier/courier name or slug |
| operator | string | Operator name (same as carrier for CZ/RO) |
| isLocker | bool | Whether this is a parcel locker |
| distance | float | Distance from search point in km (set by GeoUtils::sortByDistance) |
| address | string | Formatted full address |

## PL-only Fields (BliskaPaczka)

| Field | Type | Description |
|-------|------|-------------|
| operatorPretty | string | Human-readable operator name |
| brand | string | Brand code |
| brandPretty | string | Human-readable brand name |
| postingPoint | bool | Can send parcels from this point |
| deliveryPoint | bool | Can receive parcels at this point |
| cod | bool | Cash on delivery supported |
| description | string | Point description |
| available | bool | Whether point is active |
| pointTypes | array | Point type tags (e.g. 'parcel_locker') |
| district | string | District name |
| province | string | Province name |
| openingHoursMap | array | Opening hours per weekday |

## RO-only Fields (Ecolet)

| Field | Type | Description |
|-------|------|-------------|
| ecoletId | int | Ecolet internal point ID |

## Field Availability per Region

| Field | PL | CZ | RO |
|-------|----|----|------|
| code | x | x | x |
| name | - | x | x |
| street | x | x | x |
| city | x | x | x |
| postalCode | x | x | x |
| latitude | x | x | x |
| longitude | x | x | x |
| country | x | x | x |
| carrier | x | x | x |
| operator | x | x | x |
| isLocker | x | x | x |
| distance | x* | x | x |
| address | - | x | x |
| operatorPretty | x | - | - |
| brand/brandPretty | x | - | - |
| postingPoint | x | - | - |
| deliveryPoint | x | - | - |
| cod | x | - | - |
| description | x | - | - |
| available | x | - | - |
| pointTypes | x | - | - |
| district | x | - | - |
| province | x | - | - |
| openingHoursMap | x | - | - |
| ecoletId | - | - | x |

\* PL distance is set by BliskaPaczka API (not Haversine).
