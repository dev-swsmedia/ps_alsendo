## Function Description

The `getPoint` method retrieves a single pickup point by its operator and code. It sends a GET request to `/api/v1/pos/{operator}/{code}` and returns a `Point` object.

If the point is not found, the API returns a 404 error which results in a `RuntimeException`.

## Parameters

| Parameter | Type     | Required | Description                        |
|-----------|----------|----------|------------------------------------|
| operator  | Operator | yes      | The operator enum (INPOST, DPD, etc.) |
| code      | string   | yes      | The unique point code              |

## Example Input

```php
$service = new BliskaPaczkaPointService();

$point = $service->getPoint(Operator::INPOST, 'KRA010');
```

## Example Output

```php
Point {
    operator => "INPOST",
    operatorPretty => "InPost",
    brand => "INPOST",
    brandPretty => "InPost",
    code => "KRA010",
    street => "ul. Krakowska 10",
    city => "Kraków",
    postalCode => "30-001",
    latitude => 50.0647,
    longitude => 19.9450,
    cod => true,
    description => "Paczkomat InPost przy Galerii Krakowskiej",
    available => true,
    postingPoint => true,
    deliveryPoint => true,
    district => "Stare Miasto",
    province => "małopolskie",
    pointTypes => ["parcel_locker"],
    openingHoursMap => [
        "monday" => "00:00-23:59",
        "tuesday" => "00:00-23:59",
        ...
    ],
}
```
