# Configuration

This document contains explanations of all configuration values available for Gatekeeper.


## Authentication

- **Administrator**: This value defines the name of the user who is allowed to configure and manage Gatekeeper.
- **Authorized Users**: This value is a comma saparated list of users who are permitted to use Gatekeeper, but not configure it.


## Interface

- **Theme**: This value sets the style of the Gatekeeper web interface.


## Alerts

### Voice

- **Enabled**: This value determines whether or not Gatekeeper will use pre-recorded voice audio samples to inform users when a vehicle has been detected.

### Notifications

- **Enabled**: This value determines whether or not Gatekeeper will send push notifications when a vehicle is detected.
- **Provider**: This determines the notification back-end that Gatekeeper will use to send push notifications.
- **Server**: This determines the push notification server that Gatekeeper will send notifications to.
    - This value should include the token, key, or topic used by the notification provider.


## Devices

This section allows you to register Predator Fabric devices with your Gatekeeper instance. To remove a device, simply clear its "Identifier" field. The vehicle will be removed when the configuration is updated.

- **Identifier**: This is the identification string used by the Predator Fabric instance you want to link with Gatekeeper. Keep in mind that any client who has this identifier can submit information to your Gatekeeper instance, so you should keep it private.
- **Name**: This is a recognizable name to associate with this device.


## Vehicles

This section allows you to add recognized vehicles to your Gatekeeper instance. To remove a vehicle, simply clear its "Plate" field. The vehicle will be removed when the configuration is updated.

- **Plate**: This is the license plate associated with this vehicle.
- **Name**: This is a recognizable name for the vehicle.
- **Make**: This is an optional field used to indicate the manufacturer of the vehicle.
- **Model**: This is an optional field used to indicate the model of the vehicle.
- **Year**: This is an optional field used to indicate the year the vehicle was made.
- **Trust**: This is an optional value used to indicate how much you trust the vehicle, where -5 is completely untrusted, and 5 is completely trusted.
