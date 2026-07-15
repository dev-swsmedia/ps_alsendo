## Overview

Unified pickup point search for all supported countries: PL (BliskaPaczka), CZ (Zaslat), RO (Ecolet).

`PickupPointService` is a facade that delegates to the appropriate region-specific service based on the country code.

## Architecture

```
PickupPointService (facade)
  ├── PL -> BliskaPaczkaPointService (searchByAddress + enrichment)
  ├── CZ -> ZaslatPointService
  └── RO -> EcoletPointService
```

All services implement `PickupPointServiceInterface` with a unified `search(string $address, ?string $carrier, int $limit): Point[]` method.

`BliskaPaczkaPointService` implements a different interface (`PointServiceInterface`) with `search(PointSearchRequest)`. `PickupPointService` wraps it for PL with a simple adapter (5 lines).

## Quick Start

```php
// PL - no config needed
$service = new PickupPointService('pl');
$points = $service->search('Kraków', 'INPOST', 10);

// CZ - requires Zaslat API key
$service = new PickupPointService('cz', ['api_key' => 'your-zaslat-key']);
$points = $service->search('', 'PPL', 20);

// RO - requires Ecolet bearer token
$service = new PickupPointService('ro', ['token' => 'your-ecolet-token']);
$points = $service->search('Bucuresti', 'sameday', 20);
```

## Config per Region

| Region | Config key | Required | Source |
|--------|-----------|----------|--------|
| PL | (none) | No | BliskaPaczka API is public |
| CZ | `api_key` | Yes | Zaslat dashboard |
| RO | `token` | Yes | Ecolet OAuth token |

## Detailed Docs

- [pickup_points_search.md](pickup_points_search.md) - search() method details
- [zaslat_point_service.md](zaslat_point_service.md) - CZ/Zaslat service internals
- [ecolet_point_service.md](ecolet_point_service.md) - RO/Ecolet service internals
- [point_model.md](point_model.md) - Point model field reference
- [points_readme.md](points_readme.md) - Low-level PL service (BliskaPaczkaPointService)
