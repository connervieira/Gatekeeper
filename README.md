# Gatekeeper 

A simple tool for monitoring vehicles entering/exiting a property.


## Security

While Gatekeeper is designed to be secure and reliable, for ideal security, you should not expose it to the internet. Gatekeeper is designed to run on a local network, and should only receive information from local Predator Fabric instances.


## Description

Gatekeeper is a web utility that interfaces with one or more [Predator Fabric](https://v0lttech.com/predatorfabric.php) instances over a network to detect when vehicles enter/exit a property. The intended use case for Gatekeeper is for home owners who want to receive detailed notifications when someone enters their property, as well as the ability to quickly identify them. However, it is worth clarifying that Gatekeeper is not intended to be a security tool, and you should not rely on it for safety critical notifications.


## Features

### Private

Gatekeeper is completely self hosted, and doesn't collect or share any analytics with third-party services.

### Lightweight

Gatekeeper is lightweight, and can run comfortably on an inexpensive single-board computer.

### Customizable

Gatekeeper is designed with tinkering in mind. All source code is well documented, and easy to modify.

### Alerts

Gatekeeper can use both push notifications and voice audio samples to notify users when vehicles enter or exit their property.

### Identification

Gatekeeper allows administrators to add known vehicles to the configuration so they can be easily identified.

### Expandable

While Gatekeeper works great with just a single Predator Fabric instance, it is capable of handling many more to cover a large property.


## Documentation

To learn more about how to install, set up, and use Gatekeeper, see the [DOCUMENTATION.md](DOCUMENTATION.md) file.
