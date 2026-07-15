## Function Description

The `geocodeToCoordinates` method is a convenience wrapper around `geocode()`. It converts a text address into geographic coordinates and returns only the latitude and longitude as a simple associative array, discarding the display name.

Internally it calls `$this->geocode($address)` and extracts `lat` and `lon` from the resulting `GeocodingResult`.

## Parameters

| Parameter | Type   | Required | Description                                      |
|-----------|--------|----------|--------------------------------------------------|
| address   | string | yes      | Text address to geocode (city, street, postal code) |

## Example Input

```php
$service = new NominatimGeocodingService();

$coordinates = $service->geocodeToCoordinates(', Wenceslas Square');
```

## Example Output

```php
[
    'lat' => 50.0810676,
    'lon' => 14.4274585,
]
```

## Notes

- This method delegates entirely to `geocode()` — all exceptions (`GeocodingException`) documented in [geocoding_geocode.md](geocoding_geocode.md) apply here as well.
- Use this method when you only need coordinates and don't care about the display name.
