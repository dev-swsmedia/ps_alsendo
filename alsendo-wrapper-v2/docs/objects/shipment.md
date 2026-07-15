# Shipment Class
## Overview
The class is a PHP implementation that represents a shipment object. It extends the `Request` class and contains various properties related to shipping details.
## Structure of Properties

| Property Name     | Type   | Description                               |
|-------------------|--------|-------------------------------------------|
| $dimension1       | int    | First dimension of the shipment           |
| $dimension2       | int    | Second dimension of the shipment          |
| $dimension3       | int    | Third dimension of the shipment           |
| $weight           | int    | Weight of the shipment                    |
| $weightBillable   | int    | Billable weight of the shipment           |
| $content          | string | Content of the shipment                   |
| $comment          | string | Comment about the shipment                |
| $wayBillNumber    | string | Waybill number of the shipment            |
| $isNstd           | int    | Indicates if the shipment is non-standard |
| $shipmentTypeCode | string | Code representing the type of shipment    |
| $customsData      | array  | Data related to customs                   |
| $price            | string | Price of the shipment                     |
| $priceVat         | string | VAT price of the shipment                 |
| $priceGross       | string | Gross price of the shipment               |
### Additional Fields (Only in Ecolet)

| Property Name  | Type   | Description                     |
|----------------|--------|---------------------------------|
| $amount        | int    | Amount related to the shipment  |
| $observations  | string | Observations about the shipment |
| $shape         | string | Shape of the shipment           |
| $declaredValue | int    | Declared value of the shipment  |
| $status        | string | Status of the shipment          |
