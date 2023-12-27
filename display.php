<?php
include "./config.php";
include "./utils.php";
if (isset($gatekeeper_config["auth"]["provider"])) {
    $force_login_redirect = true;
    include $gatekeeper_config["auth"]["provider"];
} else {
    echo "<p>There is no authentication provider configured.</p>";
    exit();
}
include "./events.php";
include "./alerts.php";



$events_database = load_events_database();
$events_database = array_reverse($events_database, true); // Reverse the events database so that more recent events are at the beginning.

$unheard_alerts = check_for_alerts();
?>
<html lang="en">
    <head>
        <title><?php echo htmlspecialchars(strval($gatekeeper_config["interface"]["branding"]["name"])); ?></title>
        <link rel="stylesheet" type="text/css" href="./styles/main.css">
        <link rel="stylesheet" type="text/css" href="./styles/themes/<?php echo $gatekeeper_config["interface"]["theme"]; ?>.css">
        <?php
        if (sizeof($unheard_alerts) > 0) {
            echo '<meta http-equiv="refresh" content="3" />';
        } else {
            echo '<meta http-equiv="refresh" content="1" />';
        }
        ?>
    </head>

    <body>
        <?php

        $displayed_events = 0; // This is a placeholder that will be incremented for each event shown to keep track of how many events are displayed.
        foreach ($events_database as $time => $data) { // Iterate through each event in the event database.
            if ($displayed_events >= 5) { break; } // Exit the loop after the 5 most recent events have been displayed.

            $age = time() - $time; // Calculate the number of seconds that have passed since this event occurred.

            echo "<div class='eventdisplay'>";
            echo "<h3>" . date("Y-m-d H:i:s", $time) . "</h3>"; // Display the date and time that this event occurred.
            echo "<p class=\"agetext\">" . seconds_to_human_readable($age) . " ago</p>"; // Display the age of this event in a human readable format.
            foreach ($data["plates"] as $plate) { // Iterate through each plate in this event.
                if (in_array($plate[0]["plate"], array_keys($gatekeeper_config["vehicles"]))) { // Check to see if this plate matches a known plate from the configuration.
                    echo "<p class=\"platetext\"><b>" . $plate[0]["plate"] . "</b></p>"; // Display this plate in bold.
                } else {
                    echo "<p class=\"platetext\">" . $plate[0]["plate"] . "</p>"; // Display this plate in plain text.
                }
            }
            echo "</div>";
            $displayed_events++; // Increment the displayed events counter.
        }


        $first_unheard_alert = array_slice($unheard_alerts, 0, 1, true);
        foreach ($first_unheard_alert as $time => $alert) {
            speak_alert($time, $alert["alert"][1], $alert["alert"][0], $alert["alert"][2]);
        }
        ?>
    </body>
</html>