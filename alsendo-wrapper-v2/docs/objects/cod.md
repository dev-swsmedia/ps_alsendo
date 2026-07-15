# Cod Class

## Overview

The `Cod` class represents a collection of properties related to a cod (cash on delivery) transaction.

## Properties

| Property Name | Type               | Description                                               |
|---------------|--------------------|-----------------------------------------------------------|
| amount        | int                | The amount of the cod transaction.                        |
| bankAccount   | ?string            | The bank account associated with the cod transaction.     |
| currency      | ?string            | The currency in which the cod transaction is denominated. |
| receivedAt    | ?DateTimeImmutable | The date and time when the cod was received.              |
| returnedAt    | ?DateTimeImmutable | The date and time when the cod was returned.              |

## Notes

- The `amount` property is an integer representing the value of the cod transaction.
- The `bankAccount`, `currency`, `receivedAt`, and `returnedAt` properties are optional and can be null if not provided.
- The `receivedAt` and `returnedAt` properties are instances of `DateTimeImmutable`, which means they are frozen objects that cannot be modified after creation.

## Related Classes

- `Service`: A class representing a service, which may be related to the cod transaction.
- `OrderCreatedResponse`: A class representing a response from an order creation request, which may include information about the cod transaction.