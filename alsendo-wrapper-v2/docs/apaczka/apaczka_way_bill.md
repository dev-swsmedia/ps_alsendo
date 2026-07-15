## Function Description
The `getWayBill` function is used to retrieve the waybill information for a given order. It sends a POST request to the appropriate endpoint with the necessary data and returns the response as an object of type . `WayBill`
## Example Input
``` php
$orderId = '12345';
```
## Example Output
``` php
WayBill {
    public string $waybill = "WAYBILL123456";
    public string $type = "standard";
}
```
