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
    global $gatekeeper_config;

    $alert_time = time(); // Record the current time.
    $gatekeeper_alerts = load_alerts_database(); // Load the alerts database.
    $gatekeeper_alerts[$alert_time] = array();
    $gatekeeper_alerts[$alert_time]["alert"] = $alert; // Add this alert to the database.
    $gatekeeper_alerts[$alert_time]["read"]["played"] = false; // This will be set to true after the alert has been heard by the user.
    save_alerts_database($gatekeeper_alerts); // Save the updated alerts database.

    if ($gatekeeper_config["alerts"]["notifications"]["enabled"] == true) { // Check to see if push notifications are enabled.
        if (sizeof($alert["vehicles"]) > 1) { // Check to see of multiple vehicles were detected.
            $notification_text = "Multiple vehicles"; 
        } else { // Otherwise, assume only one vehicle was detected.
            if (in_array($alert["vehicles"][0], array_keys($gatekeeper_config["vehicles"]))) { // Check to see if this license plate is in the configuration database.
                $notification_text = $gatekeeper_config["vehicles"][$alert[1][0]]["name"]; // Use this vehicles name in place of the the license plate.
            } else {
                $notification_text = $plates[0]; // Use this vehicles license plate to identify it.
            }
        }
        if ($alert["type"] == "enter") {
            $notification_text = $notification_text . " entered at"; 
        } else if ($alert["type"] == "exit") {
            $notification_text = $notification_text . " exited at"; 
        } else {
            $notification_text = $notification_text . " passed"; 
        }
        if (in_array($alert["device"], array_keys($gatekeeper_config["devices"]))) { // Check to make sure this device's identifier exists in the configuration database.
            $notification_text = $notification_text . " " . $gatekeeper_config["devices"][$alert[0]]["name"];
        } else { // This should never occur, since an event can't be ingested unless its device identifier exists in the configuration database.
            $notification_text = $notification_text . " an unknown location"; 
        }
        if ($gatekeeper_config["alerts"]["notifications"]["provider"] == "ntfy") {
            file_get_contents($gatekeeper_config["alerts"]["notifications"]["server"], false, stream_context_create([
                'http' => ['method' => 'POST', 'header' => 'Content-Type: text/plain', 'content' => $notification_text]
            ]));
        } else if ($gatekeeper_config["alerts"]["notifications"]["provider"] == "gotify") {
            shell_exec('curl "' . $gatekeeper_config["alerts"]["notifications"]["server"] .'" -F "title=Gatekeeper" -F "message=' . $notification_text . '" -F "priority=5"');
        } else {
            echo "<p class='red'>Error: " . $gatekeeper_config["alerts"]["notifications"]["provider"] . " is not a recognized notification provider.</p>";
        }
    }
}

function process_event_for_alerts($device, $plates) { // This function takes information from an event and determines if an alert is necessary.
    maintain_alert_database(); // Eliminate expired alerts.

    global $gatekeeper_config;
    $type = $gatekeeper_config["devices"][$device]["type"];
    $plates = array_keys($plates); // Only grab the main guess for each license plate.

    $gatekeeper_alerts = load_alerts_database(); // Load the alerts database.
    $time_since_last_identical_alert = 1000000000; // This will be replaced with the time since the last alert that is identical to the current alert being processed.
    foreach ($gatekeeper_alerts as $time => $alert) { // Iterate through each alert in the database.
        if ($alert["alert"]["device"] == $device and $alert["alert"]["vehicles"] == $plates and $alert["alert"]["type"] == $type) { // Check to see if this alert matches the alert currently being processed.
            $time_since_this_alert = time() - $time; // Calculate the time since this alert occurred.
            if ($time_since_this_alert < $time_since_last_identical_alert) { // Check to see if this alert happened more recently than the currently known most recent alert.
                $time_since_last_identical_alert = $time_since_this_alert; // Update the most recent alert.
            }
        }
    }
    if ($time_since_last_identical_alert > 10) { // Check to see if this last occurrance of this alert happened more than a certain number of seconds ago.
        issue_alert(array("device" => $device, "vehicles" => $plates, "type" => $type));
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
