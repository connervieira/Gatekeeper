<?php
include "./config.php";
include "./events.php";

$received_data = strval($_POST["results"]); // Get the raw submitted results.
$processed_data = json_decode($received_data, true); // Decode the JSON data received.

$identifier = strval($processed_data["info"]["identifier"]); // Get the identifier from the data submission as a string.
$identifier = filter_var($identifier, FILTER_SANITIZE_STRING); // Sanitize the identifier string.

if (strlen($identifier) > 100) { exit(); } // Verify that the identifier is an expected length. Otherwise, terminate the script.

if (in_array($identifier, array_keys($gatekeeper_config["devices"]))) { // Check to see if this submission's device identifier is in the configuration.
    $gatekeeper_events = load_events_database();
    $gatekeeper_events[$processed_data["info"]["processing"]["captured_timestamp"]] = array();
    $gatekeeper_events[$processed_data["info"]["processing"]["captured_timestamp"]]["device"] = $identifier;
    $gatekeeper_events[$processed_data["info"]["processing"]["captured_timestamp"]]["plates"] = $processed_data["results"];
    save_events_database($gatekeeper_events);
}
?>
