# EcoletOrderValuation Class Structure

## Overview
The `EcoletOrderValuation` class extends the `OrderValuation` class and includes properties specific to the Ecolet service.

## Properties

### General Properties
| Property Name | Type              | Description                           |
|---------------|-------------------|---------------------------------------|
| $serviceId    | string            | The ID of the service                 |
| $carrier      | string            | The carrier associated with the order |
| $service      | string            | The service type                      |
| $priceTable   | array             | A list of price table items           |
| $pickupDate   | DateTimeImmutable | The pickup date for the order         |
| $deliveryDate | DateTimeImmutable | The delivery date for the order       |

### Ecolet-Specific Properties
| Property Name       | Type  | Description                              |
|---------------------|-------|------------------------------------------|
| $additionalServices | array | A list of additional services            |
| $pickupDates        | array | A list of pickup dates                   |
| $isStandard         | bool  | Whether the service is standard          |
| $fees               | array | A list of fees associated with the order |
| $vat                | int   | The value added tax                      |
| $info               | array | Additional information about the order   |
| $errors             | array | Errors encountered during processing     |

## Property Type Mapping
The `getPropertyTypeMap` method defines how properties are mapped and converted when serializing or deserializing data.

| Property Name         | Mapped To            | Conversion Notes                 |
|-----------------------|----------------------|----------------------------------|
| 'service_id'          | 'serviceId'          | No conversion needed             |
| 'pickup_dates'        | 'pickupDates'        | No conversion needed             |
| 'is_standard'         | 'isStandard'         | Converted to boolean             |
| 'additional_services' | 'additionalServices' | No conversion needed             |
| 'price'               | 'priceTable'         | Mapped to `PriceTableItem` class |
| 'vat'                 | 'vat'                | Converted to integer             |
| 'info'                | 'info'               | No conversion needed             |
| 'errors'              | 'errors'             | No conversion needed             |

## Notes
- The `PriceTableItem` class is used to map the `price` property, which includes both net and gross prices.
- The `$additionalServices`, `$pickupDates`, and `$fees` properties are specific to the Ecolet service and may not be present in other services.