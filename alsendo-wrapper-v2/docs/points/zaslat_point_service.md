## Overview

`ZaslatPointService` handles pickup point search for CZ (Czech Republic) using the Zaslat API.

Implements `PickupPointServiceInterface`.

## Zaslat API

- **Endpoint**: `GET /api/v1/parcelpoints/list`
- **Auth**: `X-Apikey` header
- **Base URL**: `https://www.zaslat.cz/` (production), `https://test.zaslat.cz/` (test)

### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| country | string | Country code, always 'CZ' |
| carrier | string | Optional carrier filter (e.g. 'PPL', 'ZASILKOVNA') |

### Response Format

```json
{
  "status": 200,
  "data": {
    "101": {
      "code": "PPL--1",
      "name": "PPL Depo ",
      "street": "Vodickova 12",
      "city": "Praha",
      "zip": "11000",
      "country": "CZ",
      "carrier": "PPL",
      "is_locker": 0,
      "location": {"lat": "50.0833", "lng": "14.4167"}
    }
  }
}
```

## How Search Works

1. Geocode address via Nominatim -> get lat/lon
2. Fetch all CZ points from Zaslat (with optional carrier filter)
3. Calculate Haversine distance for each point
4. Sort by distance ascending
5. Return top N results

## Field Mapping (JSON -> Point)

| JSON field | Point field |
|------------|-------------|
| code | code |
| name | name |
| street | street |
| city | city |
| zip | postalCode |
| country | country |
| location.lat | latitude |
| location.lng | longitude |
| carrier | carrier, operator |
| is_locker | isLocker (cast to bool) |
| (computed) | address (street, city, zip joined) |

## Cache

- Key: `zaslat_CZ_{carrier}` or `zaslat_CZ_all`
- TTL: 15 minutes
- Scope: static (in-memory)
- Clear: `ZaslatPointService::clearCache()`

## Constructor

```php
$service = new ZaslatPointService(
    ['api_key' => 'your-key'],  // config
    false,                       // test mode
    null,                        // HTTP client (for testing)
    null                         // geocoding service (for testing)
);
```

## Usage

```php
$service = new ZaslatPointService(['api_key' => 'xxx']);
$points = $service->search('Praha', 'PPL', 20);
```
