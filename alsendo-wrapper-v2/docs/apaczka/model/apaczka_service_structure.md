# ApaczkaServiceStructure README

## Structure of Properties

The `ApaczkaServiceStructure` class defines the structure of properties used in the API. The following table describes the properties and their corresponding classes:

| Property Name | Mapped To    | Type            |
|---------------|--------------|-----------------|
| services      | services     | `Service[]`     |
| options       | options      | `Option[]`      |
| package_type  | package_type | `PackageType[]` |
| pickup_type   | pickup_type  | `PickupType[]`  |
| unit_type     | unit_type    | `UnitType[]`    |

## Notes

- The `ApaczkaServiceStructure` class extends the `ServiceStructure` class, which is used to define the structure of services in the API.
- Each property is mapped to a specific class that represents the type of data it contains. For example, the `services` property is mapped to the `Service[]` class, which represents an array of service objects.

This structure allows for easy access and manipulation of the data related to services in the Apaczka API.