## Overview

The `NominatimGeocodingService` class provides geocoding functionality using the Nominatim/OpenStreetMap API (`nominatim.openstreetmap.org`). It converts a text address into geographic coordinates (latitude/longitude). This service does not require authentication.

The service extends `ApiClient` and is used internally by `BliskaPaczkaPointService::searchByAddress()` to convert user-provided addresses into coordinates for proximity-based pickup point searches.

## Key Features

- **No Authentication**: The Nominatim API is public and does not require API keys.
- **Geocode Address**: Convert a text address (city, street, postal code) into geographic coordinates.
- **Returns Coordinates**: `geocode()` returns a `GeocodingResult` with lat, lon, and displayName; `geocodeToCoordinates()` returns a simple `array{lat, lon}`.

## Usage

```php
$service = new NominatimGeocodingService();
```

No constructor parameters are needed. The service automatically sets the base URL to `https://nominatim.openstreetmap.org` and configures the required `User-Agent` header.

## Methods

- `geocode(string $address): GeocodingResult` - Geocode an address, returns full result with display name. See [geocoding_geocode.md](geocoding_geocode.md).
- `geocodeToCoordinates(string $address): array{lat: float, lon: float}` - Geocode an address, returns only coordinates. See [geocoding_geocode_to_coordinates.md](geocoding_geocode_to_coordinates.md).

## Models

- `GeocodingResult` - Geocoding result model with properties: `lat` (float), `lon` (float), `displayName` (string).
- `GeocodingException` - Exception thrown when geocoding fails (empty address, no results found, invalid JSON response).

## GeoUtils

`GeoUtils` provides static utility methods for Haversine distance calculation and sorting Point arrays by proximity. Used by `ZaslatPointService` and `EcoletPointService`. See [geo_utils.md](geo_utils.md).
