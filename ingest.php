<?php
include "./config.php";
include "./events.php";
include "./alerts.php";

$received_data = strval($_POST["results"]); // Get the raw submitted results.
$processed_data = json_decode($received_data, true); // Decode the JSON data received.

$identifier = strval($processed_data["info"]["identifier"]); // Get the identifier from the data submission as a string.
$identifier = filter_var($identifier, FILTER_SANITIZE_STRING); // Sanitize the identifier string.

if (strlen($identifier) > 100) { echo "Invalid identifier"; exit(); } // Verify that the identifier is an expected length. Otherwise, terminate the script.

if (in_array($identifier, array_keys($gatekeeper_config["devices"]))) { // Check to see if this submission's device identifier is in the configuration.
    $gatekeeper_events = load_events_database();
    $event_key = $processed_data["info"]["processing"]["captured_timestamp"];
    $gatekeeper_events[$event_key] = array();
    $gatekeeper_events[$event_key]["device"] = $identifier;
    foreach ($processed_data["results"] as $key => $plates) { // Iterate through each plate in the received results.
        $key_plate = $processed_data["results"][$key][0]["plate"]; // Use the first plate guess as the key.
        foreach ($plates as $guess) { // Iterate through each guess for this plate.
            if (in_array($guess["plate"], array_keys($gatekeeper_config["vehicles"]))) { // Check to see if this guess matches a vehicle in the configuration databases.
                $key_plate = $guess["plate"]; // Make this guess the key.
            }
        }
        if (isset($gatekeeper_events[$event_key]["plates"][$key_plate])) { // Check to see if this plate has already been seen in this submission.
            if (sizeof($gatekeeper_events[$event_key]["plates"][$key_plate]) < sizeof($processed_data["results"][$key])) { // Check to see if the newly encountered instance of this plate has more guesses than the existing instance.
                $gatekeeper_events[$event_key]["plates"][$key_plate] = $processed_data["results"][$key]; // Replace the existing instance with this newly encountered instance.
            }
        } else {
            $gatekeeper_events[$event_key]["plates"][$key_plate] = $processed_data["results"][$key];
        }
    }

    process_event_for_alerts($gatekeeper_events[$event_key]["device"], $gatekeeper_events[$event_key]["plates"]); // Send this event to alert processing.
    save_events_database($gatekeeper_events);
    echo "Ingested event";
} else {
    echo "Unknown identifier";
    exit();
}
?>
