# Notification Class
## Overview
The class is used to represent a notification object in the Alsendo wrapper. It contains four properties, each of which is an instance of the class `NotificationDetail`
## Properties

| Property     | Type                 | Description                                   |
|--------------|----------------------|-----------------------------------------------|
| `$new`       | `NotificationDetail` | Represents the new notification detail.       |
| `$sent`      | `NotificationDetail` | Represents the sent notification detail.      |
| `$exception` | `NotificationDetail` | Represents the exception notification detail. |
| `$delivered` | `NotificationDetail` | Represents the delivered notification detail. |
## Service-Specific Properties
The properties of the class are not specific to any particular service, as they are part of the core notification system. 
## Related Classes
- `NotificationDetail` : A class that represents the details of a notification. It contains four integer properties that indicate whether a notification is for a receiver email, receiver SMS, sender email, or sender SMS. 
