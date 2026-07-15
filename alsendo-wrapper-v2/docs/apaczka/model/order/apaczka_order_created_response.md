# ApaczkaOrderCreatedResponse
## Class Description
The class `ApaczkaOrderCreatedResponse` is an extension of the class `OrderCreatedResponse`. It represents a response from the Apaczka API when an order is created.  
## Properties Structure

| Property Name   | Type     | Description                                        |
|-----------------|----------|----------------------------------------------------|
| id              | int      | Unique identifier for the order                    |
| service_id      | int      | Identifier of the service used to create the order |
| service_name    | string   | Name of the service used to create the order       |
| waybill_number  | string   | Waybill number for the order                       |
| pickup_number   | string   | Pickup number for the order                        |
| tracking_url    | string   | URL to track the order                             |
| status          | string   | Status of the order                                |
| shipments_count | int      | Number of shipments in the order                   |
| receiver        | object   | Object representing the receiver of the order      |
| shipments       | object   | Object representing the shipments in the order     |
| created         | datetime | Date and time when the order was created           |
### Receiver Properties

| Property Name      | Type   | Description                            |
|--------------------|--------|----------------------------------------|
| name               | string | Name of the receiver                   |
| contact_person     | string | Contact person for the receiver        |
| email              | string | Email address of the receiver          |
| phone              | string | Phone number of the receiver           |
| line1              | string | First line of the receiver's address   |
| line2              | string | Second line of the receiver's address  |
| postal_code        | string | Postal code of the receiver's address  |
| city               | string | City of the receiver's address         |
| country_code       | string | Country code of the receiver's address |
| foreign_address_id | int    | ID of the foreign address              |
### Shipments Properties

| Property Name  | Type   | Description                      |
|----------------|--------|----------------------------------|
| weight         | int    | Weight of the shipment           |
| dimension1     | int    | First dimension of the shipment  |
| dimension2     | int    | Second dimension of the shipment |
| dimension3     | int    | Third dimension of the shipment  |
| declared_value | float  | Declared value of the shipment   |
| content        | string | Content of the shipment          |
## Notes
- The `created` property is converted to a datetime format using the conversion. 
- The `receiver` and `shipments` properties are objects that map to their respective classes (`Receiver` and `Shipment`). 
