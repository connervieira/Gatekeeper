# Documentation

This document explains how to install, set up, and use Gatekeeper.


## Installation

1. Install Apache, or another web-server host.
    - Example: `sudo apt-get install apache2`
2. Install and enable PHP for your web-server.
    - Example: `sudo apt-get install php; sudo a2enmod php*`
3. Restart your web-server host.
    - Example: `sudo apache2ctl restart`
4. Install audio packages.
    - Example `sudo apt install sox libsox-fmt-mp3`
5. Install DropAuth.
    - Gatekeeper uses DropAuth to manage authentication.
    - You can learn more about DropAuth at <https://v0lttech.com/dropauth.php>
6. Install Gatekeeper.
    - After downloading Gatekeeper, move the main directory to the root of your web-server directory.
    - Example: `mv ~/Downloads/gatekeeper /var/www/html/gatekeeper/`


## Setup

### Permissions

1. Make the Gatekeeper directory writable.
    - Example: `chmod 777 /var/www/html/gatekeeper/`

### Basic Configuration

1. Navigate to DropAuth in your web browser.
    - Example: `http://localhost/dropauth/`
2. If you don't already have an account on your DropAuth instance, create one.
3. Log into your DropAuth account.
    - The account you log in with should be the one you plan to use as your Gatekeeper administration account.
4. Navigate to Gatekeeper in your web browser.
    - Example: `http://localhost/gatekeeper/`
5. Press the "Configure" button on the top left of the main Gatekeeper webpage.
6. Under the "Authentication" section, set the "Administrator" field to the username of your DropAuth account.
    - This user will become the administrator user, and the configuration interface will be restricted to all other users.
    - Note that after submitting the updated administrator user, the configuration interface will lock out all other users, and only the specified administrator will be able to configure Gatekeeper.
        - If you enter the wrong username and accidentally lock yourself out of the configuration interface, you can manually modify the the `config.json` file in the Gatekeeper directory to adjust the configuration.
7. Make other configuration changes as desired.
    - All configuration options are explained in the [CONFIGURATION.md](CONFIGURATION.md) document.


### Audio

Gatekeeper uses pre-recorded audio samples to inform the user when a vehicle is detected. A voice pack containing basic text-to-speech samples is included for example, but these can be replaced with higher quality custom samples by the administrator. This section describes how to set-up audio samples for Gatekeeper.

For audio notifications to work, the Gatekeeper web interface must be opened on a client with autoplay enabled.

All voice samples used by Gatekeeper are found in the `assets/audio/voice/` directory. Voice samples are stitched together when an alert is triggered in the following order: vehicle audio, event type audio, device audio. For example, 3 audio samples might be stitched together for form the following phrase: "An unknown vehicle" + "has entered at", "north driveway"

#### Vehicles

The `vehicles` directory contains audio samples for the names of vehicles. If a vehicle exists in the configuration, it should have a subdirectory in this directory named after its associated license plate. This subdirectory should contain a `name.mp3` file, which speaks the name of the vehicle.

The `vehicles` directory itself contains the following files, in addition to the subdirectories described previously.
- `multiple.mp3` is played when multiple vehicles are detected at the same time from the same device, and should say the phrase "more than one vehicle...".
- `previouslyseen.mp3` is a placeholder that is not currently used, but should say the phrase "a previously seen vehicle..."
- `unknown.mp3` is played when a vehicle that is not found in the configuration is detected, and should say the phrase "an unknown vehicle..."

#### Actions

The `actions` directory contains the following audio samples:
- `entered.mp3` is played when a vehicle passes an entrance and should say the phrase "...has entered at...".
- `exited.mp3` is played when a vehicle passes an exit and should say the phrase "...has exited at...".
- `passed.mp3` is played when a vehicle passes any other device type, and should say the phrase "...has passed...".

#### Devices

The `devices` directory contains audio samples for devices, which are considered as locations on the property. The `devices` directory contains subdirectories that correspond to device identifiers in the configuration. Each of these subdirectories should contain a `name.mp3` file, which should speak the name of the location the device is associated with.

#### System

The `system` directory contains audio files used by the system, and contains the following files:
- `missingsamples.mp3` is played when a voice alert is triggered, but one or more required voice samples is missing.


## Integration

Gatekeeper works by receiving ALPR events from one or more instances of Predator Fabric. To integrate a Predator Fabric instance with Gatekeeper, follow these steps:
1. Come up with an identifier to use with Predator Fabric.
    - This identifier should be unique and random, since anyone with access to this identifier can submit information to your Gatekeeper instance. 
2. Configure your Predator Fabric instance to use this identifier.
3. Register the identifier with Gatekeeper on the configuration webpage.
    - Under the "Devices" section, enter the identifier as a "New Device", and give it a recognizable name.
4. In the Predator Fabric configuration, set the `network>results_submission>target` to a URL pointing to the `ingest.php` script of your Gatekeeper instance.
    - For example: `http://example.com/gatekeeper/ingest.php`
    - Additionally, you should make sure that the `network>results_submission>mode` configuration value is set to either "auto" or "on".
5. Provided Predator Fabric can access your Gatekeeper instance over the network, Predator Fabric should now be able to submit ALPR results to Gatekeeper.
    - If you run into trouble, the `tools/test_result_submission.py` bundled with Predator Fabric allows you to manually submit sample data to a specified target, which mmay help you diagnose problems.
