<?php
include "./config.php";
if (isset($gatekeeper_config["auth"]["provider"])) {
    $force_login_redirect = true;
    include $gatekeeper_config["auth"]["provider"];
} else {
    echo "<p>There is no authentication provider configured.</p>";
    exit();
}

if ($_POST["interface>theme"] == "dark" or $_POST["interface>theme"] == "light") { $gatekeeper_config["interface"]["theme"] = $_POST["interface>theme"]; } // Update the theme before loading the rest of the page so that the new page theme reflects the changes just made by the user.
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars(strval($gatekeeper_config["interface"]["branding"]["name"])); ?> - Configure</title>
        <link rel="stylesheet" type="text/css" href="./styles/main.css">
        <link rel="stylesheet" type="text/css" href="./styles/themes/<?php echo $gatekeeper_config["interface"]["theme"]; ?>.css">
    </head>

    <body>
        <div class="navbar">
            <a class="button" href="./">Back</a>
        </div>
        <h1><?php echo htmlspecialchars($gatekeeper_config["interface"]["branding"]["name"]); ?></h1>
        <h2>Configure</h2>

        <?php

        if ($username !== $gatekeeper_config["auth"]["admin"] and $gatekeeper_config["auth"]["admin"] !== "") { // Check to see if the current user is unauthorized to make configuration changes.
            echo "<p>You do not have permission to configure this instance. Please make sure you are signed in with the correct account.</p>";
            exit();
        }


        if ($_POST["submit"] == "Submit") { // Check to see if the configuration form has been submitted.
            $configuration_valid = true; // This is a placeholder that will be changed to 'false' if an invalid configuration value is encountered.

            if ($_POST["auth>admin"] !== preg_replace("/[^a-zA-Z0-9]/", '', $_POST["auth>admin"])) { // Verify that the admin userinput only contains permitted characters.
                echo "<p class='bad'>The administrator username <b>" . htmlspecialchars($_POST["auth>admin"]) . "</b> contains disallowed characters.</p>";
                $configuration_valid = false;
            }
            $gatekeeper_config["auth"]["admin"] = $_POST["auth>admin"];
            

            $gatekeeper_config["auth"]["authorized_users"] = array();
            if (strlen($_POST["auth>authorized_users"]) > 0) {
                foreach (explode(",", $_POST["auth>authorized_users"]) as $authorized_user) {
                    if (strlen($authorized_user) > 0) {
                        $authorized_user = trim($authorized_user); // Trim any trailing or leading whitespace from this entry.
                        if (trim($authorized_user) == preg_replace("/[^a-zA-Z0-9]/", '', trim($authorized_user))) { // Verify that this entry only contains permitted characters.
                            echo "<p class='bad'>The <b>" . htmlspecialchars($authorized_user) . "</b> username contains disallowed characters.</p>";
                            $configuration_valid = false;
                        } else {
                            array_push($gatekeeper_config["auth"]["authorized_users"], trim(preg_replace("/[^a-zA-Z0-9]/", '', $authorized_user)));
                        }
                    }
                }
            }


            if ($_POST["interface>theme"] == "dark" or $_POST["interface>theme"] == "light") {
                $gatekeeper_config["interface"]["theme"] = $_POST["interface>theme"];
            } else {
                echo "<p class='bad'>The interface theme is set to an invalid value.</p>";
                $configuration_valid = false;
            }



            if ($_POST["alerts>voice>enabled"] == "on") { $gatekeeper_config["alerts"]["voice"]["enabled"] = true; } else { $gatekeeper_config["alerts"]["voice"]["enabled"] = false; }
            if ($_POST["alerts>notifications>enabled"] == "on") { $gatekeeper_config["alerts"]["notifications"]["enabled"] = true; } else { $gatekeeper_config["alerts"]["notifications"]["enabled"] = false; }
            if ($_POST["alerts>notifications>provider"] == "ntfy" or $_POST["alerts>notifications>provider"] == "gotify") {
                $gatekeeper_config["alerts"]["notifications"]["provider"] = $_POST["alerts>notifications>provider"];
            } else {
                echo "<p class='bad'>The alert notification provider is set to an invalid value.</p>";
                $configuration_valid = false;
            }
            $gatekeeper_config["alerts"]["notifications"]["server"] = $_POST["alerts>notifications>server"];


            // Add the vehicles set by the user to the configuration.
            $original_vehicle_count = sizeof($gatekeeper_config["vehicles"]); // This variables holds the number of vehicles in the configuration database before processing the new ones.
            $gatekeeper_config["vehicles"] = array(); // Reset the array of vehicles.
            $vehicle_count = 0; // This is a placeholder that will keep track of each vehicle sequentially.
            for ($i =0; $i <= $original_vehicle_count + 1; $i++) { // Run once for each vehicle in the configuration, plus one to account for the new entry.
                $vehicle_plate = strtoupper($_POST["vehicles>" . strval($vehicle_count) . ">plate"]);
                if (strlen($vehicle_plate) > 0) {
                    $gatekeeper_config["vehicles"][$vehicle_plate]["name"] = $_POST["vehicles>" . strval($vehicle_count) . ">name"];
                    $gatekeeper_config["vehicles"][$vehicle_plate]["make"] = $_POST["vehicles>" . strval($vehicle_count) . ">make"];
                    $gatekeeper_config["vehicles"][$vehicle_plate]["model"] = $_POST["vehicles>" . strval($vehicle_count) . ">model"];
                    $gatekeeper_config["vehicles"][$vehicle_plate]["year"] = intval($_POST["vehicles>" . strval($vehicle_count) . ">year"]);
                    $gatekeeper_config["vehicles"][$vehicle_plate]["trust"] = intval($_POST["vehicles>" . strval($vehicle_count) . ">trust"]);

                    if ($vehicle_plate !== preg_replace("/[^A-Z0-9]/", '', $vehicle_plate)) {
                        echo "<p class='bad'>The vehicle license plate '" . htmlspecialchars($vehicle_plate) . "' is invalid.</p>";
                        $configuration_valid = false;
                    }
                    if ($gatekeeper_config["vehicles"][$vehicle_plate]["name"] !== preg_replace("/[^a-zA-Z0-9\-\ \.\,\?\']/", '', $gatekeeper_config["vehicles"][$vehicle_plate]["name"])) {
                        echo "<p class='bad'>The vehicle name '" . htmlspecialchars($gatekeeper_config["vehicles"][$vehicle_plate]["name"]) . "' for '" . htmlspecialchars($vehicle_plate) . "' is invalid.</p>";
                        $configuration_valid = false;
                    }
                    if ($gatekeeper_config["vehicles"][$vehicle_plate]["make"] !== preg_replace("/[^a-zA-Z0-9]/", '', $gatekeeper_config["vehicles"][$vehicle_plate]["make"])) {
                        echo "<p class='bad'>The vehicle make '" . htmlspecialchars($gatekeeper_config["vehicles"][$vehicle_plate]["make"]) . "' for '" . htmlspecialchars($vehicle_plate) . "' is invalid.</p>";
                        $configuration_valid = false;
                    }
                    if ($gatekeeper_config["vehicles"][$vehicle_plate]["model"] !== preg_replace("/[^a-zA-Z0-9]/", '', $gatekeeper_config["vehicles"][$vehicle_plate]["model"])) {
                        echo "<p class='bad'>The vehicle model '" . htmlspecialchars($gatekeeper_config["vehicles"][$vehicle_plate]["model"]) . "' for '" . htmlspecialchars($vehicle_plate) . "' is invalid.</p>";
                        $configuration_valid = false;
                    }
                    if (intval($gatekeeper_config["vehicles"][$vehicle_plate]["year"]) < 0 and intval($gatekeeper_config["vehicles"][$vehicle_plate]["year"]) > 3000) {
                        echo "<p class='bad'>The vehicle year '" . htmlspecialchars($gatekeeper_config["vehicles"][$vehicle_plate]["year"]) . "' for '" . htmlspecialchars($vehicle_plate) . "' is invalid.</p>";
                        $configuration_valid = false;
                    }
                    if (intval($gatekeeper_config["vehicles"][$vehicle_plate]["trust"]) < -5 and intval($gatekeeper_config["vehicles"][$vehicle_plate]["trust"]) > 5) {
                        echo "<p class='bad'>The vehicle year '" . htmlspecialchars($gatekeeper_config["vehicles"][$vehicle_plate]["trust"]) . "' for '" . htmlspecialchars($vehicle_plate) . "' is invalid.</p>";
                        $configuration_valid = false;
                    }
                }
                $vehicle_count = $vehicle_count + 1;
            }



            // Add the devices set by the user to the configuration.
            $original_device_count = sizeof($gatekeeper_config["devices"]); // This variables holds the number of devices in the configuration database before processing the new ones.
            $gatekeeper_config["devices"] = array(); // Reset the array of deviecs.
            $device_count = 0; // This is a placeholder that will keep track of each device sequentially.
            for ($i =0; $i <= $original_device_count + 1; $i++) { // Run once for each device in the configuration, plus one to account for the new entry.
                $device_id = $_POST["devices>" . strval($device_count) . ">id"];
                if (strlen($device_id) > 0) {
                    $gatekeeper_config["devices"][$device_id]["name"] = $_POST["devices>" . strval($device_count) . ">name"];
                    $gatekeeper_config["devices"][$device_id]["type"] = $_POST["devices>" . strval($device_count) . ">type"];

                    if ($device_id !== preg_replace("/[^a-zA-Z0-9\ \.\-]/", '', $device_id)) {
                        echo "<p class='bad'>The device identifier '" . htmlspecialchars($vehicle_plate) . "' is invalid.</p>";
                        $configuration_valid = false;
                    }
                    if ($gatekeeper_config["devices"][$device_id]["name"] !== preg_replace("/[^a-zA-Z0-9\-\ \.\,\?\']/", '', $gatekeeper_config["devices"][$device_id]["name"])) {
                        echo "<p class='bad'>The device name '" . htmlspecialchars($gatekeeper_config["devices"][$device_id]["name"]) . "' for '" . htmlspecialchars($device_id) . "' is invalid.</p>";
                        $configuration_valid = false;
                    }
                    if ($gatekeeper_config["devices"][$device_id]["type"] !== "enter" and $gatekeeper_config["devices"][$device_id]["type"] !== "exit" and $gatekeeper_config["devices"][$device_id]["type"] !== "both" and $gatekeeper_config["devices"][$device_id]["type"] !== "neither") {
                        echo "<p class='bad'>The device type '" . htmlspecialchars($gatekeeper_config["devices"][$device_id]["type"]) . "' for '" . htmlspecialchars($device_id) . "' is invalid.</p>";
                        $configuration_valid = false;
                    }
                }
                $device_count = $device_count + 1;
            }


            if ($configuration_valid == true) {
                file_put_contents($gatekeeper_config_database_filepath, json_encode($gatekeeper_config, (JSON_UNESCAPED_SLASHES)));
                echo "<p class='good'>Successfully updated configuration.</p>";
            } else {
                echo "<p class='bad'>The configuration was not updated.</p>";
            }
        }
        ?>

        <form method="post">
            <hr><h3>Authentication</h3>
            <label for="auth>admin">Administrator:</label> <input name="auth>admin" id="auth>admin" placeholder="admin" type="text" value="<?php echo $gatekeeper_config["auth"]["admin"]; ?>"><br>
            <label for="auth>authorized_users">Authorized Users:</label> <input name="auth>authorized_users" id="auth>authorized_users" placeholder="user1, user2, user3" type="text" value="<?php $users = ""; foreach ($gatekeeper_config["auth"]["authorized_users"] as $user) { $users = $users . $user . ","; } echo substr($users, 0, strlen($users)-1); ?>"><br>


            <hr><h3>Interface</h3>
            <label for="interface>theme">Theme:</label>
            <select name="interface>theme" id="interface>theme">
                <option value="light" <?php if ($gatekeeper_config["interface"]["theme"] == "light") { echo "selected"; } ?>>Light</option>
                <option value="dark" <?php if ($gatekeeper_config["interface"]["theme"] == "dark") { echo "selected"; } ?>>Dark</option>
            </select>


            <hr><h3>Alerts</h3>
            <h4>Voice</h4>
            <label for="alerts>voice>enabled">Enabled:</label> <input type="checkbox" id="alerts>voice>enabled" name="alerts>voice>enabled" <?php if ($gatekeeper_config["alerts"]["voice"]["enabled"]) { echo "checked"; } ?>>
            <h4>Notifications</h4>
            <label for="alerts>notifications>enabled">Enabled:</label> <input type="checkbox" id="alerts>notifications>enabled" name="alerts>notifications>enabled"  <?php if ($gatekeeper_config["alerts"]["notifications"]["enabled"]) { echo "checked"; } ?>><br>
            <label for="alerts>notifications>provider">Provider:</label>
            <select name="alerts>notifications>provider" id="alerts>notifications>provider">
                <option value="ntfy" <?php if ($gatekeeper_config["alerts"]["notifications"]["provider"] == "ntfy") { echo "selected"; } ?>>NTFY</option>
                <option value="gotify" <?php if ($gatekeeper_config["alerts"]["notifications"]["provider"] == "gotify") { echo "selected"; } ?>>Gotify</option>
            </select><br>
            <label for="alerts>notifications>server">Server:</label> <input name="alerts>notifications>server" id="alerts>notifications>server" placeholder="http://example.com" type="text" value="<?php echo $gatekeeper_config["alerts"]["notifications"]["server"]; ?>"><br>


            <hr><h3>Devices</h3>
            <?php
            $shown_devices = 0;
            foreach ($gatekeeper_config["devices"] as $key => $data) { // Iterate through each device currently in the configuration.
                echo "<h4>" . $key . "</h4>";
                echo "<label for='devices>" . $shown_devices . ">id'>Identifier:</label> <input name='devices>" . $shown_devices . ">id' id='devices>" . $shown_devices . ">id' placeholder='abcdef123456789' type='text' value=\"" . $key . "\"><br>";
                echo "<label for='devices>" . $shown_devices . ">name'>Name:</label> <input name='devices>" . $shown_devices . ">name' id='devices>" . $shown_devices . ">name' placeholder='North Driveway' type='text' value=\"" . $data["name"] . "\"><br>";
                echo "<label for='devices>" . $shown_devices . ">type'>Type:</label>";
                echo "<select name='devices>" . $shown_devices . ">type' id='devices>" . $shown_devices . ">type'>";
                echo "    <option value='enter'"; if ($data["type"] == "enter") { echo "selected"; } echo ">Entrance</option>";
                echo "    <option value='exit'"; if ($data["type"] == "exit") { echo "selected"; } echo ">Exit</option>";
                echo "    <option value='both'"; if ($data["type"] == "both") { echo "selected"; } echo ">Both</option>";
                echo "    <option value='neither'"; if ($data["type"] == "neither") { echo "selected"; } echo ">Neither</option>";
                echo "</select><br>";
                $shown_devices = $shown_devices + 1;
            }
            echo "<h4>New Device</h4>";
            echo "<label for='devices>" . $shown_devices . ">id'>Identifier:</label> <input name='devices>" . $shown_devices . ">id' id='devices>" . $shown_devices . ">id' placeholder='abcdef123456789' type='text'><br>";
            echo '<label for="devices>' . $shown_devices . '>name">Name:</label> <input name="devices>' . $shown_devices . '>name" id="devices>' . $shown_devices . '>name" placeholder="North Driveway" type="text"><br>';
            echo "<select name='devices>" . $shown_devices . ">type' id='devices>" . $shown_devices . ">type'>";
            echo "    <option value='enter'>Entrance</option>";
            echo "    <option value='exit'>Exit</option>";
            echo "    <option value='both'>Both</option>";
            echo "    <option value='neither'>Neither</option>";
            echo "</select><br>";
            ?>


            <hr><h3>Vehicles</h3>
            <?php
            $shown_vehicles = 0;
            foreach ($gatekeeper_config["vehicles"] as $key => $data) { // Iterate through each vehicle currently in the configuration.
                echo "<h4>" . $key . "</h4>";
                echo "<label for='vehicles>" . $shown_vehicles . ">plate'>Plate:</label> <input name='vehicles>" . $shown_vehicles . ">plate' id='vehicles>" . $shown_vehicles . ">plate' placeholder='AAA0000' type='text' value=\"" . $key . "\"><br>";
                echo "<label for='vehicles>" . $shown_vehicles . ">name'>Name:</label> <input name='vehicles>" . $shown_vehicles . ">name' id='vehicles>" . $shown_vehicles . ">name' placeholder=\"Bob\'s Car\" type='text' value=\"" . $data["name"] . "\"><br>";
                echo "<label for='vehicles>" . $shown_vehicles . ">make'>Make:</label> <input name='vehicles>" . $shown_vehicles . ">make' id='vehicles>" . $shown_vehicles . ">make' placeholder='Toyota' type='text' value=\"" . $data["make"] . "\"><br>";
                echo "<label for='vehicles>" . $shown_vehicles . ">model'>Model:</label> <input name='vehicles>" . $shown_vehicles . ">model' id='vehicles>" . $shown_vehicles . ">model' placeholder='Corolla' type='text' value=\"" . $data["model"] . "\"><br>";
                echo "<label for='vehicles>" . $shown_vehicles . ">year'>Year:</label> <input name='vehicles>" . $shown_vehicles . ">year' id='vehicles>" . $shown_vehicles . ">year' placeholder='2013' type='number' step='1' value=\"" . $data["year"] . "\"><br>";
                echo "<label for='vehicles>" . $shown_vehicles . ">trust'>Trust:</label> <input name='vehicles>" . $shown_vehicles . ">trust' id='vehicles>" . $shown_vehicles . ">trust' placeholder='3' type='number' min='-5' max='5' step='1' value=\"" . $data["trust"] . "\"><br>";
                $shown_vehicles = $shown_vehicles + 1;
            }
            echo "<h4>New Vehicle</h4>";
            echo "<label for='vehicles>" . $shown_vehicles . ">plate'>Plate:</label> <input name='vehicles>" . $shown_vehicles . ">plate' id='vehicles>" . $shown_vehicles . ">plate' placeholder='AAA0000' type='text'><br>";
            echo '<label for="vehicles>' . $shown_vehicles . '>name">Name:</label> <input name="vehicles>' . $shown_vehicles . '>name" id="vehicles>' . $shown_vehicles . '>name" placeholder="Bob\'s Car" type="text"><br>';
            echo "<label for='vehicles>" . $shown_vehicles . ">make'>Make:</label> <input name='vehicles>" . $shown_vehicles . ">make' id='vehicles>" . $shown_vehicles . ">make' placeholder='Toyota' type='text'><br>";
            echo "<label for='vehicles>" . $shown_vehicles . ">model'>Model:</label> <input name='vehicles>" . $shown_vehicles . ">model' id='vehicles>" . $shown_vehicles . ">model' placeholder='Corolla' type='text'><br>";
            echo "<label for='vehicles>" . $shown_vehicles . ">year'>Year:</label> <input name='vehicles>" . $shown_vehicles . ">year' id='vehicles>" . $shown_vehicles . ">year' placeholder='2013' type='number' step='1'><br>";
            echo "<label for='vehicles>" . $shown_vehicles . ">trust'>Trust:</label> <input name='vehicles>" . $shown_vehicles . ">trust' id='vehicles>" . $shown_vehicles . ">trust' placeholder='3' type='number' min='-5' max='5' step='1' value=\"0\"><br>";
            ?>


            <hr><input class="button" type="submit" value="Submit" name="submit" id="submit">
        </form>
    </body>
</html>
