<?php
include "./config.php";
include "./events.php";
include "./alerts.php";

$received_data = strval($_POST["results"]); // Get the raw submitted results.
$processed_data = json_decode($received_data, true); // Decode the JSON data received.

$identifier = strval($processed_data["info"]["identifier"]); // Get the identifier from the data submission as a string.
$identifier = filter_var($identifier, FILTER_SANITIZE_STRING); // Sanitize the identifier string.

if (strlen($identifier) > 100) { exit(); } // Verify that the identifier is an expected length. Otherwise, terminate the script.

if (in_array($identifier, array_keys($gatekeeper_config["devices"]))) { // Check to see if this submission's device identifier is in the configuration.
    $gatekeeper_events = load_events_database();
    $gatekeeper_events[$processed_data["info"]["processing"]["captured_timestamp"]] = array();
    $gatekeeper_events[$processed_data["info"]["processing"]["captured_timestamp"]]["device"] = $identifier;
    foreach ($processed_data["results"] as $key => $plate) { // Iterate through each plate in the received results.
        if (isset($gatekeeper_events[$processed_data["info"]["processing"]["captured_timestamp"]]["plates"][$plate[0]["plate"]])) { // Check to see if this plate has already been seen in this submission.
            if (sizeof($gatekeeper_events[$processed_data["info"]["processing"]["captured_timestamp"]]["plates"][$plate[0]["plate"]]) < sizeof($processed_data["results"][$key])) { // Check to see if the newly encountered instance of this plate has more guesses than the existing instance.
                $gatekeeper_events[$processed_data["info"]["processing"]["captured_timestamp"]]["plates"][$plate[0]["plate"]] = $processed_data["results"][$key]; // Replace the existing instance with this newly encountered instance.
            }
        } else {
            $gatekeeper_events[$processed_data["info"]["processing"]["captured_timestamp"]]["plates"][$plate[0]["plate"]] = $processed_data["results"][$key];
        }
    }

    process_event_for_alerts($gatekeeper_events[$processed_data["info"]["processing"]["captured_timestamp"]]["device"], $gatekeeper_events[$processed_data["info"]["processing"]["captured_timestamp"]]["plates"]); // Send this event to alert processing.
    save_events_database($gatekeeper_events);
    echo "Ingested event";
} else {
    echo "Invalid identifier";
    exit();
}
?>
