# Contact Class

## Overview
The `Contact` class represents a contact information object that extends from the `Request` class. It contains various properties to store contact details for a person or organization, including address and communication information.

## Properties

| Property Name          | Type     | Description                                                                 |
|------------------------|----------|-----------------------------------------------------------------------------|
| countryCode            | string   | The country code of the contact (e.g., "PL" for Poland).                   |
| name                   | string   | The full name of the contact person or organization.                       |
| line1                  | string   | The first line of the address.                                             |
| line2                  | string   | The second line of the address.                                            |
| postalCode             | string   | The postal code (ZIP) of the address.                                      |
| stateCode              | string   | The state code of the contact.                                             |
| city                   | string   | The city of the contact.                                                   |
| isResidential          | int      | Indicates whether the contact is residential (1 for yes, 0 for no).         |
| contactPerson          | string   | The name of the person contacting.                                         |
| email                  | string   | The email address of the contact.                                          |
| phone                  | string   | The phone number of the contact.                                           |
| foreignAddressId       | string   | The ID of a foreign address.                                               |
| id                     | int      | The unique identifier for the contact (used in Ecolet and Zaslat).         |
| country                | string   | The country name (mapped from $countryCode).                              |
| localityId             | int      | The ID of the locality (only in Ecolet).                                   |
| streetName             | string   | The name of the street (only in Ecolet).                                   |
| streetNumber           | string   | The number on the street (only in Ecolet).                                 |
| block                  | string   | The block number (only in Ecolet).                                         |
| entrance               | string   | The entrance number (only in Ecolet).                                      |
| floor                  | string   | The floor number (only in Ecolet).                                         |
| flat                   | string   | The flat number (only in Ecolet).                                          |
| hasMapPoint            | bool     | Indicates whether a map point is available (only in Ecolet).                |
| mapPointId             | int      | The ID of the map point (used in Ecolet and Zaslat).                       |
| mapPointName           | string   | The name of the map point (used in Ecolet and Zaslat).                     |
| county                 | string   | The county name (mapped from $stateCode).                                  |
| locality               | string   | The locality name (mapped from $city).                                     |
| company                | string   | The name of the company associated with the contact.                       |

## Service-Specific Properties

| Service Name | Property Name         | Type     | Description                                                                 |
|--------------|-----------------------|----------|-----------------------------------------------------------------------------|
| Ecolet       | id                    | int      | The unique identifier for the contact (used in Ecolet and Zaslat).         |
| Ecolet       | localityId            | int      | The ID of the locality (only in Ecolet).                                   |
| Ecolet       | streetName            | string   | The name of the street (only in Ecolet).                                   |
| Ecolet       | streetNumber          | string   | The number on the street (only in Ecolet).                                 |
| Ecolet       | block                 | string   | The block number (only in Ecolet).                                         |
| Ecolet       | entrance              | string   | The entrance number (only in Ecolet).                                      |
| Ecolet       | floor                 | string   | The floor number (only in Ecolet).                                         |
| Ecolet       | flat                  | string   | The flat number (only in Ecolet).                                          |
| Ecolet       | hasMapPoint           | bool     | Indicates whether a map point is available (only in Ecolet).                |
| Ecolet       | mapPointId            | int      | The ID of the map point (used in Ecolet and Zaslat).                       |
| Ecolet       | mapPointName          | string   | The name of the map point (used in Ecolet and Zaslat).                     |
| Zaslat       | id                    | int      | The unique identifier for the contact (used in Ecolet and Zaslat).         |

## Notes
- Some properties are specific to certain services (Ecolet or Zaslat).
- Properties like `country`, `county`, and `locality` are derived from other fields and are not directly set.
- The class is part of a larger system that includes related classes such as `Address` and `OrderRequest`.