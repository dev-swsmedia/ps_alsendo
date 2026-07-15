# EcoletServiceStructure Class Readme

## Overview

The `EcoletServiceStructure` class is a subclass of `ServiceStructure`, which represents the structure of data for services in an API. This class defines how properties are mapped and what types they have.

## Properties Structure

The `EcoletServiceStructure` class has a property called `services` that maps to a list of `Service` objects. Each `Service` object has several properties, some of which map to other classes like `ServiceCourier` and `Conditions`.

### Main Properties

| Property   | Type        | Description                   |
|------------|-------------|-------------------------------|
| `services` | `Service[]` | An array of `Service` objects |

### Service Properties

The `Service` class has the following properties:

| Property     | Type             | Description                                              |
|--------------|------------------|----------------------------------------------------------|
| `id`         | `int`            | The ID of the service                                    |
| `slug`       | `string`         | The slug of the service                                  |
| `full_name`  | `string`         | The full name of the service                             |
| `status`     | `bool`           | The status of the service (mapped to `status`)           |
| `is_new`     | `bool`           | Whether the service is new (mapped to `isNew`)           |
| `is_promo`   | `bool`           | Whether the service is a promotion (mapped to `isPromo`) |
| `courier`    | `ServiceCourier` | The courier object for the service                       |
| `conditions` | `Conditions`     | The conditions object for the service                    |

### ServiceCourier Properties

The `ServiceCourier` class has the following properties:

| Property | Type     | Description                                    |
|----------|----------|------------------------------------------------|
| `id`     | `int`    | The ID of the courier                          |
| `slug`   | `string` | The slug of the courier                        |
| `name`   | `string` | The name of the courier                        |
| `status` | `bool`   | The status of the courier (mapped to `status`) |

### Conditions Properties

The `Conditions` class has the following properties:

| Property                | Type   | Description                                                            |
|-------------------------|--------|------------------------------------------------------------------------|
| `has_pickup_only_today` | `bool` | Whether pickup is only today (mapped to `hasPickupOnlyToday`)          |
| `has_multipacks`        | `bool` | Whether multiple packs are allowed (mapped to `hasMultipacks`)         |
| `has_cod`               | `bool` | Whether cash on delivery is allowed (mapped to `hasCod`)               |
| `has_open_package`      | `bool` | Whether open package is allowed (mapped to `hasOpenPackage`)           |
| `has_rod`               | `bool` | Whether rod is allowed (mapped to `hasRod`)                            |
| `has_rop`               | `bool` | Whether rop is allowed (mapped to `hasRop`)                            |
| `has_saturday_delivery` | `bool` | Whether Saturday delivery is allowed (mapped to `hasSaturdayDelivery`) |
| `has_sms_notify`        | `bool` | Whether SMS notification is allowed (mapped to `hasSmsNotify`)         |
| `has_swap`              | `bool` | Whether swap is allowed (mapped to `hasSwap`)                          |

## Notes

- The `EcoletServiceStructure` class is used to map data from an API response to a structured format.
- Some properties are mapped to other classes, such as `ServiceCourier` and `Conditions`, which have their own properties and types.
- The `Json` class is used for converting between JSON and objects.