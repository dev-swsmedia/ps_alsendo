# EcoletOrderSendResponse Class
## Description
The class represents the response from sending an order to Ecolet. It extends the class and includes properties related to the order, such as the order ID, status, service ID, receiver information, and shipment details. `EcoletOrderSendResponse`
## Structure of Properties

| Property                   | Type   | Description                                                                                                                                                                                                                                        |
|----------------------------|--------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| order_id                   | string | The unique identifier of the order. Mapped to `id` in the response.                                                                                                                                                                                |
| status                     | string | The current status of the order. Mapped to `status` in the response.                                                                                                                                                                               |
| order.service_id           | string | The ID of the service associated with the order. Mapped to in the response. `service_id`                                                                                                                                                           |
| order.data.receiver        | object | Contains information about the receiver. This property is mapped to `receiver`, and it includes fields such as name, contact person, phone, email, street address, city, and country code. The class is used for this property. `Receiver`         |
| order.data.courier.service | string | The name of the courier service associated with the order. Mapped to in the response. `service_name`                                                                                                                                               |
| order.data.parcels         | object | Contains information about the parcels being sent. This property is mapped to `shipments`, and it includes fields such as weight, dimensions (length, width, height), declared value, and content. The class is used for this property. `Shipment` |
## Service-Specific Properties
The following properties are specific to the Ecolet service:

| Property            | Type   | Description                                                                                                                                                                                                                                |
|---------------------|--------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| order.data.receiver | object | Contains information about the receiver. This property is mapped to `receiver`, and it includes fields such as name, contact person, phone, email, street address, city, and country code. The class is used for this property. `Receiver` |
## Notes
- The class `EcoletOrderSendResponse` uses a `getPropertyTypeMap()` method to define how properties are mapped from the JSON response.  
- The class `Receiver` is used to map the receiver information, and the class `Shipment` is used to map the shipment details. 
- This class is part of the Ecolet API integration for order management.
