<?php
include "./utils.php";
include "./config.php";

$gatekeeper_alert_database_filepath = "/dev/shm/alerts.json";

function save_alerts_database($gatekeeper_alerts) { // This function saves the alerts database.
    global $gatekeeper_alert_database_filepath;
    if (is_writable(dirname($gatekeeper_alert_database_filepath))) {
        file_put_contents($gatekeeper_alert_database_filepath, json_encode($gatekeeper_alerts, (JSON_UNESCAPED_SLASHES)));
    } else {
        echo "<p class='red'>Error: The " . realpath(dirname($gatekeeper_alert_database_filepath)) . " directory is not writable.</p>";
    }
}

if (!file_exists($gatekeeper_alert_database_filepath)) { // Check to see if the alert database file needs to be created.
    $gatekeeper_alerts = array(); // Initialize the alert database to an empty array.
    save_alerts_database($gatekeeper_alerts);
}

function load_alerts_database() { // This function loads the alerts database.
    global $gatekeeper_alert_database_filepath;
    $gatekeeper_alerts = json_decode(file_get_contents($gatekeeper_alert_database_filepath), true);
    return $gatekeeper_alerts;
}

function maintain_alert_database() { // This function reads through the alert database and removes any that are older than a certain age.
    $max_age = 60;
    $gatekeeper_alerts = load_alerts_database(); // Load the alerts database.
    foreach (array_keys($gatekeeper_alerts) as $alert_time) { // Iterate through each alert in the database.
        if (time() - $alert_time > $max_age) { // Check to see if this alert is older than the maximum age.
            unset($gatekeeper_alerts[$alert_time]); // Remove this alert from the alert database.
        }
    }
}

function issue_alert($alert) { // This function takes an alert after it has been processed and issues it to the database and notifies the user.
    $alert_time = time(); // Record the current time.
    $gatekeeper_alerts = load_alerts_database(); // Load the alerts database.
    $gatekeeper_alerts[$alert_time] = array();
    $gatekeeper_alerts[$alert_time]["alert"] = $alert; // Add this alert to the database.
    $gatekeeper_alerts[$alert_time]["read"]["played"] = false; // This will be set to true after the alert has been heard by the user.
    save_alerts_database($gatekeeper_alerts); // Save the updated alerts database.
}

function process_event_for_alerts($device, $plates) { // This function takes information from an event and determines if an alert is necessary.
    maintain_alert_database(); // Eliminate expired alerts.

    global $gatekeeper_config;
    $type = $gatekeeper_config["devices"][$device]["type"];
    $plates = array_keys($plates); // Only grab the first guess for each license plate.

    $gatekeeper_alerts = load_alerts_database(); // Load the alerts database.
    $time_since_last_identical_alert = 1000000000; // This will be replaced with the time since the last alert that is identical to the current alert being processed.
    foreach ($gatekeeper_alerts as $time => $alert) { // Iterate through each alert in the database.
        if ($alert["alert"][0] == $device and $alert["alert"][1] == $plates and $alert["alert"][2] == $type) { // Check to see if this alert matches the alert currently being processed.
            $time_since_this_alert = time() - $time; // Calculate the time since this alert occurred.
            if ($time_since_this_alert < $time_since_last_identical_alert) { // Check to see if this alert happened more recently than the currently known most recent alert.
                $time_since_last_identical_alert = $time_since_this_alert; // Update the most recent alert.
            }
        }
    }
    if ($time_since_last_identical_alert > 10) { // Check to see if this last occurrance of this alert happened more than a certain number of seconds ago.
        issue_alert(array($device, $plates, $type));
    }
}


function check_for_alerts() { // This function returns a list of alerts that have not been played by the user.
    $gatekeeper_alerts = load_alerts_database(); // Load the alerts database.

    $unheard_alerts = array(); // This is a placeholder that will be populated with all of the alerts that have not been heard by the user.
    foreach ($gatekeeper_alerts as $time => $alert) { // Iterate through each alert in the database.
        if ($alert["read"]["played"] == false) { // Check to see if this alert has not yet been heard.
            $unheard_alerts[$time] = $alert; // Add this alert to the list of unheard alerts.
        }
    }
    save_alerts_database($gatekeeper_alerts); // Save the updated alerts database.
    return $unheard_alerts; // Return the list of unheared alerts.
}

function speak_alert($time, $vehicle, $device, $type) { // This function speaks an alert using the event speech function from `utils.php`.
    speak_event($vehicle, $device, $type);
    $gatekeeper_alerts = load_alerts_database(); // Load the alerts database.
    if (isset($gatekeeper_alerts[$time]["read"]["played"])) {
        $gatekeeper_alerts[$time]["read"]["played"] = true; // Mark this alert as heard.
        save_alerts_database($gatekeeper_alerts); // Save the updated alerts database.
    }
}

?>
