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
