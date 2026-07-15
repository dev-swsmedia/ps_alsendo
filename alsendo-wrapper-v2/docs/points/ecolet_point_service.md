## Overview

`EcoletPointService` handles pickup point search for RO (Romania) using the Ecolet API.

Implements `PickupPointServiceInterface`.

## Ecolet API

- **Auth**: `Authorization: Bearer {token}` header
- **Base URL**: `https://panel.ecolet.ro/` (production), `https://staging.ecolet.ro/` (test)

### Endpoints Used

1. **Localities**: `GET /api/v1/locations/ro/localities/{query}` - built-in geocoding
2. **Map Points**: `POST /api/v1/map-points/ro` - fetch pickup points
3. **Services**: `GET /api/v1/services` - courier slug -> ID mapping

## 2-Step Search

### Step 1: Locality Search (preferred)
1. Query Ecolet localities endpoint with address text
2. If locality found: use its ID to fetch points for that locality
3. If locality has lat/lng: sort points by distance from locality center

### Step 2: Nominatim Fallback
1. If no localities found (or no points for locality): fall back to Nominatim geocoding
2. Fetch all RO points (no locality filter)
3. Sort by Haversine distance from Nominatim coordinates

## Carrier Filtering

Ecolet uses courier IDs (integers) internally. The service maps carrier slugs (strings) to IDs:

1. Fetch `/api/v1/services` (cached 15min)
2. Build map: `{'sameday' => 3, 'dpd' => 2, 'fan' => 1}`
3. Convert user's carrier slug to ID
4. Pass as `couriers` array in map-points request body
5. If slug unknown: skip filter, return all points

Matching is case-insensitive ('Sameday' matches 'sameday').

## Map Points Request Body

```json
{
  "destination": "receiver",
  "localityId": 5001,
  "couriers": [3]
}
```

`localityId` and `couriers` are optional.

## Field Mapping (JSON -> Point)

| JSON field | Point field |
|------------|-------------|
| id | ecoletId, code (as string) |
| name | name |
| street (or address) | street |
| locality.name (or city) | city |
| postal_code | postalCode |
| country_code | country |
| lat | latitude |
| lng | longitude |
| courier_slug | carrier, operator |
| type == 'locker' | isLocker |
| (computed) | address (street, city, postal_code joined) |

## Cache

- Courier map key: `ecolet_courier_map`
- TTL: 15 minutes
- Scope: static (in-memory)
- Clear: `EcoletPointService::clearCache()`

## Constructor

```php
$service = new EcoletPointService(
    ['token' => 'your-bearer-token'],  // config
    false,                              // test mode
    null,                               // HTTP client (for testing)
    null                                // geocoding service (for testing)
);
```

## Usage

```php
$service = new EcoletPointService(['token' => 'xxx']);
$points = $service->search('Bucuresti', 'sameday', 20);
```
