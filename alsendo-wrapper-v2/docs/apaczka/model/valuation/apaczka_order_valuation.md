# ApaczkaOrderValuation Class
## Overview
The class extends the class and is used to represent order valuation data specific to the Apaczka service. This class defines the structure of properties that are used in this context. `ApaczkaOrderValuation``OrderValuation`
## Property Structure

| Property Name | Description                                                  | Type               | Notes                                                                 |
|---------------|--------------------------------------------------------------|--------------------|-----------------------------------------------------------------------|
| `price_table` | Represents a table of prices related to the order valuation. | `PriceTableItem[]` | Each item in this array is an instance of the class. `PriceTableItem` |
## Detailed Property Information
### `price_table`
- **Mapped To**: `priceTable`
- **Type**: `PriceTableItem[]`
- **Map Fields**:
    - `price`: `price`
    - : `priceGross` `price_gross`

This property is used to store a list of price items related to the order valuation. Each item in this array is an instance of the class. `PriceTableItem`
## Related Classes
### `PriceTableItem`
- **Class Name**: `PriceTableItem`
- **Namespace**: `Alsendo\AlsendoWrapper\Model\Order\Valuation`
- **Properties**:
    - `price`: `?string`
    - `priceGross`: `?string`
    - `currency`: `?string`

### `OrderValuation`
- **Class Name**: `OrderValuation`
- **Namespace**: `Alsendo\AlsendoWrapper\Model\Order\Valuation`
- **Properties**:
    - `serviceId`: `?string`
    - `carrier`: `?string`
    - `service`: `?string`
    - : `priceTable``array`
    - `pickupDate`: `?DateTimeImmutable`
    - : `deliveryDate``?DateTimeImmutable`

## Notes
- The class is part of the Apaczka API integration and is used to map data from the Apaczka service to a common structure. `ApaczkaOrderValuation` `OrderValuation`
- The property is specifically designed for the Apaczka service and contains price-related information. `price_table`
