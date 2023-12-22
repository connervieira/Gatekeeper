<?php
$gatekeeper_events_database_filepath = "./events.json"; // This defines the file path of the events JSON file.

// Initialize the events database.
if (!file_exists($gatekeeper_events_database_filepath)) { // Check to see if the event database file needs to be created.
    $gatekeeper_events = array();
    if (realpath(dirname(is_writable($gatekeeper_events_database_filepath)))) {
        file_put_contents($gatekeeper_events_database_filepath, json_encode($gatekeeper_events, (JSON_UNESCAPED_SLASHES)));
    } else {
        echo "<p class='red'>Error: The " . realpath(dirname($gatekeeper_events_database_filepath)) . " directory is not writable.</p>";
    }
}

function save_events_database($data) {
    global $gatekeeper_events_database_filepath;
    if (is_writable($gatekeeper_events_database_filepath)) {
        file_put_contents($gatekeeper_events_database_filepath, json_encode($data, (JSON_UNESCAPED_SLASHES)));
    } else {
        echo "<p class='red'>Error: The " . $gatekeeper_events_database_filepath . " file is not writable.</p>";
    }
}

function load_events_database() {
    global $gatekeeper_events_database_filepath;
    $gatekeeper_events = json_decode(file_get_contents($gatekeeper_events_database_filepath), true);
    return $gatekeeper_events;
}

?>
