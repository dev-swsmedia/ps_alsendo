**Note:** This is the low-level PL-only service. For unified multi-region search (PL/CZ/RO), see [pickup_points_readme.md](pickup_points_readme.md).

## Overview

The `BliskaPaczkaPointService` class provides an interface to search for pickup points (paczkomaty, pickup points) using the BliskaPaczka public Points API (`pos.bliskapaczka.pl`). This service does not require authentication.

The service implements `PointServiceInterface` with full control over search parameters (coordinates, distance, operators, point types).

## Key Features

- **No Authentication**: The BliskaPaczka Points API is public and does not require API keys.
- **Search**: Search for pickup points by text (city, street, point code).
- **Get Point**: Retrieve a single point by operator and code.
- **Filtering**: Filter by operators (INPOST, DPD, POCZTA, etc.), point types, and COD support.
- **Field Selection**: Choose which fields to return in the response.

## Usage

```php
// Production
$service = new BliskaPaczkaPointService();

// Sandbox / test
$service = new BliskaPaczkaPointService(true);
```

## Methods

- `search(PointSearchRequest $request): Point[]` - Search for pickup points by text query. See [points_search.md](points_search.md).
- `getPoint(Operator $operator, string $code): Point` - Get a single point by operator and code. See [points_get_point.md](points_get_point.md).

## Models

- `Point` - Pickup point model with fields: operator, code, city, street, postalCode, latitude, longitude, etc.
- `PointSearchRequest` - Search request DTO with searchText, size, operators, fields, etc.
- `Operator` - PHP 8.1 enum of supported operators (INPOST, RUCH, POCZTA, DPD, UPS, FEDEX).
- `PointField` - PHP 8.1 enum of available response fields.
