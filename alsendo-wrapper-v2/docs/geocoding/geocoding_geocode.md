## Function Description

The `geocode` method converts a text address into geographic coordinates using the Nominatim API. It sends a GET request to `/search` with query parameters `q` (address), `format=json`, and `limit=1`, and returns a `GeocodingResult` object containing latitude, longitude, and the display name returned by Nominatim.

The method validates that the address is not empty before making the API call. If validation fails or no results are found, a `GeocodingException` is thrown.

## Parameters

| Parameter | Type   | Required | Description                                      |
|-----------|--------|----------|--------------------------------------------------|
| address   | string | yes      | Text address to geocode (city, street, postal code) |

## Example Input

```php
$service = new NominatimGeocodingService();

$result = $service->geocode('Kraków, Rynek Główny 1');
```

## Example Output

```php
GeocodingResult {
    lat => 50.0616862,
    lon => 19.9372472,
    displayName => "1, Rynek Główny, Stare Miasto, Kraków, województwo małopolskie, 31-042, Polska",
}
```

## Notes

- **Empty address**: Throws `GeocodingException('Address cannot be empty')` if the trimmed address is an empty string.
- **No results**: Throws `GeocodingException('No results found for address: ...')` if Nominatim returns an empty array.
- **Invalid JSON**: Throws `GeocodingException('Invalid JSON response from Nominatim: ...')` if the response cannot be decoded.
- The API is called with `limit=1`, so only the first (most relevant) result is used.
- Nominatim requires a `User-Agent` header, which is set automatically to `Alsendo-Wrapper/2.0`.
