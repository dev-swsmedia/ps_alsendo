## Function Description

The `search()` method on `PickupPointService` (and all `PickupPointServiceInterface` implementations) provides unified pickup point search across all supported countries.

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| address | string | Yes | - | Address or city to search near |
| carrier | string\|null | No | null | Filter by carrier slug (e.g. 'PPL', 'INPOST', 'sameday') |
| limit | int | No | 20 | Max number of results |

## How It Works Per Region

### PL (BliskaPaczka)
1. Delegates to `BliskaPaczkaPointService::searchByAddress()`
2. Uses Nominatim geocoding internally to convert address to coordinates
3. Searches BliskaPaczka API `/api/v1/pos/filter` with proximity
4. Enriches results: sets `country='PL'`, `carrier=operator`, detects lockers via `pointTypes`

### CZ (Zaslat)
1. Geocodes address via Nominatim
2. Fetches all CZ parcel points from Zaslat API (cached 15min)
3. Sorts by Haversine distance from geocoded coordinates
4. Returns top N results

### RO (Ecolet)
1. Tries Ecolet locality search first (built-in geocoding)
2. If locality found: fetches map-points for that locality
3. If no locality: falls back to Nominatim geocoding + all points
4. Sorts by distance, returns top N
5. Carrier slug (e.g. 'sameday') is converted to courier ID via /api/v1/services

## Cache

- CZ: Zaslat points cached 15 minutes (per carrier filter)
- RO: Ecolet courier map cached 15 minutes
- Cache is static (in-memory, per-request in PHP)
- `clearCache()` available on each service for testing

## Errors

| Exception | When |
|-----------|------|
| `InvalidArgumentException` | Empty address or unsupported country |
| `RuntimeException` | API request failure (Zaslat, Ecolet) |
| `GeocodingException` | Nominatim geocoding failure |

## Example

```php
$service = new PickupPointService('cz', ['api_key' => 'xxx']);

try {
    $points = $service->search('', 'PPL', 10);
    foreach ($points as $point) {
        echo "{$point->name} ({$point->code}) - {$point->distance} km\n";
    }
} catch (\RuntimeException $e) {
    echo "API error: " . $e->getMessage();
}
```
