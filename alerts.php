<?php
$gatekeeper_alert_database_filepath = "/dev/shm/alerts.json";

if (!file_exists($gatekeeper_alert_database_filepath)) { // Check to see if the alert database file needs to be created.
    $gatekeeper_alerts = array(); // Initialize the alert database to an empty array.
    save_alerts_database($gatekeeper_alerts);
}


function save_alerts_database($gatekeeper_alerts) { // This function saves the alerts database.
    global $gatekeeper_alert_database_filepath;
    if (is_writable(dirname($gatekeeper_alert_database_filepath))) {
        file_put_contents($gatekeeper_alert_database_filepath, json_encode($gatekeeper_alerts, (JSON_UNESCAPED_SLASHES)));
    } else {
        echo "<p class='red'>Error: The " . realpath(dirname($gatekeeper_alert_database_filepath)) . " directory is not writable.</p>";
    }
}

function load_alerts_database() { // This function loads the alerts database.
    $gatekeeper_alerts = json_decode(file_get_contents($gatekeeper_alert_database_filepath), true);
    return $gatekeeper_alerts;
}

function maintain_alert_database() { // This function reads through the alert database and removes any that are older than a certain age.
    $max_age = 60;
    $gatekeeper_alerts = load_alerts_database(); // Load the alerts database.
    foreach (array_keys($gatekeeper_alerts) as $alert_time) { // Iterate through each alert in the database.
        if (time() - $alert_time > $max_age) { // Check to see if this alert is older than the maximum age.
            unset($gatekeeper_alerts[$time]); // Remove this alert from the alert database.
        }
    }
}

function process_event_for_alerts($time, $device, $plates) { // This function takes information from an event and determines if an alert is necessary.
}




?>
