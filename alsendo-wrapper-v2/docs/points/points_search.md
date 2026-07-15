**Note:** This documents the low-level `BliskaPaczkaPointService::search()`. For the unified multi-region `search()`, see [pickup_points_search.md](pickup_points_search.md).

## Function Description

The `search` method searches for pickup points using the BliskaPaczka Points API. It sends a GET request to `/api/v1/pos/filter` with query parameters built from the `PointSearchRequest` object.

The method validates that `searchText` is not empty before making the API call. If validation fails, a `ValidationException` is thrown.

## Parameters (PointSearchRequest)

At least one of `searchText` or (`lat` + `lon`) must be provided.

| Parameter    | Type          | Required | Description                                      |
|--------------|---------------|----------|--------------------------------------------------|
| searchText   | string        | no*      | Search query (city, street, point code)          |
| lat          | float         | no*      | Latitude for proximity search                    |
| lon          | float         | no*      | Longitude for proximity search                   |
| distance     | float         | no       | Search radius (default: API default)             |
| distanceUnit | string        | no       | Distance unit: 'KM' or 'M' (default: KM)        |
| size         | int           | no       | Limit number of results                          |
| operators    | Operator[]    | no       | Filter by operators (INPOST, DPD, POCZTA, etc.)  |
| posType      | string        | no       | Point type: 'all', 'delivery', 'posting'          |
| cod          | bool          | no       | Filter by COD (cash on delivery) support         |
| fields       | PointField[]  | no       | Which fields to include in the response          |
| pointTypes   | string[]      | no       | Filter by point types (e.g. 'parcel_locker')     |

\* `searchText` or (`lat` + `lon`) — at least one search criterion is required.

## Example Input

```php
$service = new BliskaPaczkaPointService();

$request = new PointSearchRequest();
$request->searchText = 'Kraków';
$request->size = 5;
$request->operators = [Operator::INPOST];
$request->fields = [PointField::CODE, PointField::CITY, PointField::STREET, PointField::LATITUDE, PointField::LONGITUDE];

$points = $service->search($request);
```

## Example Input (Proximity Search)

```php
$service = new BliskaPaczkaPointService();

$request = new PointSearchRequest();
$request->lat = 52.2297;
$request->lon = 21.0122;
$request->distance = 5;
$request->distanceUnit = 'KM';
$request->operators = [Operator::INPOST];
$request->size = 10;

$points = $service->search($request);
```

## Example Output

```php
// $points is an array of Point objects:
Point {
    operator => "INPOST",
    code => "KRA010",
    city => "Kraków",
    street => "ul. Krakowska 10",
    postalCode => "30-001",
    latitude => 50.0647,
    longitude => 19.9450,
    available => true,
    ...
}
```
