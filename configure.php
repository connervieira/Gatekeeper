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


            // Add the targets set by the user to the configuration.
            $original_vehicle_count = sizeof($gatekeeper_config["vehicles"]); // This variables holds the number of vehicles in the configuration database before processing the new ones.
            $gatekeeper_config["vehicles"] = array(); // Reset the array of vehicles.
            $vehicle_count = 0; // This is a placeholder that will keep track of each target sequentially.
            for ($i =0; $i <= $vehicle_target_count + 1; $i++) { // Run once for each vehicle in the configuration, plus one to account for the new entry.
                $vehicle_plate = $_POST["vehicles>" . strval($vehicle_count) . ">plate"];
                if (strlen($vehicle_plate) > 0) {
                    // TODO: Add input validation.
                    $gatekeeper_config["vehicles"][$vehicle_plate]["name"] = $_POST["vehicles>" . strval($target_count) . ">name"];
                    $gatekeeper_config["vehicles"][$vehicle_plate]["make"] = $_POST["vehicles>" . strval($target_count) . ">make"];
                    $gatekeeper_config["vehicles"][$vehicle_plate]["model"] = $_POST["vehicles>" . strval($target_count) . ">model"];
                    $gatekeeper_config["vehicles"][$vehicle_plate]["year"] = intval($_POST["vehicles>" . strval($target_count) . ">year"]);
                    $gatekeeper_config["vehicles"][$vehicle_plate]["trust"] = intval($_POST["vehicles>" . strval($target_count) . ">trust"]);
                }
                $target_count = $target_count + 1;
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
            <label for="interface>show_ip">Show Target Addresses:</label> <input name="interface>show_ip" id="interface>show_ip" type="checkbox" <?php if ($gatekeeper_config["interface"]["show_ip"] == true) { echo "checked"; } ?>><br>

            <label for="interface>theme">Theme:</label>
            <select name="interface>theme" id="interface>theme">
                <option value="light" <?php if ($gatekeeper_config["interface"]["theme"] == "light") { echo "selected"; } ?>>Light</option>
                <option value="dark" <?php if ($gatekeeper_config["interface"]["theme"] == "dark") { echo "selected"; } ?>>Dark</option>
            </select>


            <hr><h3>Vehicles</h3>
            <?php
            $shown_targets = 0;
            foreach ($gatekeeper_config["vehicles"] as $key => $data) { // Iterate through each vehicle currently in the configuration.
                echo "<h4>" . $key . "</h4>";
                echo "<label for='vehicles>" . $shown_targets . ">plate'>Plate:</label> <input name='vehicles>" . $shown_targets . ">plate' id='vehicles>" . $shown_targets . ">plate' placeholder='AAA0000' type='text' value=\"" . $key . "\"><br>";
                echo "<label for='vehicles>" . $shown_targets . ">name'>Name:</label> <input name='vehicles>" . $shown_targets . ">name' id='vehicles>" . $shown_targets . ">name' placeholder='Bob\'s Car' type='text' value=\"" . $data["name"] . "\"><br>";
                echo "<label for='vehicles>" . $shown_targets . ">make'>Make:</label> <input name='vehicles>" . $shown_targets . ">make ' id='vehicles>" . $shown_targets . ">make' placeholder='Toyota' type='text' value=\"" . $data["make"] . "\"><br>";
                echo "<label for='vehicles>" . $shown_targets . ">model'>Model:</label> <input name='vehicles>" . $shown_targets . ">model' id='vehicles>" . $shown_targets . ">model' placeholder='Corolla' type='text' value=\"" . $data["model"] . "\"><br>";
                echo "<label for='vehicles>" . $shown_targets . ">year'>Year:</label> <input name='vehicles>" . $shown_targets . ">year' id='vehicles>" . $shown_targets . ">year' placeholder='2013' type='number' min='1700' step='1' value=\"" . $data["year"] . "\"><br>";
                echo "<label for='vehicles>" . $shown_targets . ">trust'>Trust:</label> <input name='vehicles>" . $shown_targets . ">trust' id='vehicles>" . $shown_targets . ">trust' placeholder='3' type='number' min='-5' max='5' step='1' value=\"" . $data["trust"] . "\"><br>";
                $shown_targets = $shown_targets + 1;
            }
            echo "<h4>New Vehicle</h4>";
            echo "<label for='vehicles>" . $shown_targets . ">plate'>Plate:</label> <input name='vehicles>" . $shown_targets . ">plate' id='vehicles>" . $shown_targets . ">plate' placeholder='AAA0000' type='text'><br>";
            echo "<label for='vehicles>" . $shown_targets . ">name'>Name:</label> <input name='vehicles>" . $shown_targets . ">name' id='vehicles>" . $shown_targets . ">name' placeholder='Bob\'s Car' type='text'><br>";
            echo "<label for='vehicles>" . $shown_targets . ">make'>Make:</label> <input name='vehicles>" . $shown_targets . ">make ' id='vehicles>" . $shown_targets . ">make' placeholder='Toyota' type='text'><br>";
            echo "<label for='vehicles>" . $shown_targets . ">model'>Model:</label> <input name='vehicles>" . $shown_targets . ">model' id='vehicles>" . $shown_targets . ">model' placeholder='Corolla' type='text'><br>";
            echo "<label for='vehicles>" . $shown_targets . ">year'>Year:</label> <input name='vehicles>" . $shown_targets . ">year' id='vehicles>" . $shown_targets . ">year' placeholder='2013' type='number' min='1700' step='1'><br>";
            echo "<label for='vehicles>" . $shown_targets . ">trust'>Trust:</label> <input name='vehicles>" . $shown_targets . ">trust' id='vehicles>" . $shown_targets . ">trust' placeholder='3' type='number' min='-5' max='5' step='1' value=\"0\"><br>";
            ?>
            <hr><input class="button" type="submit" value="Submit" name="submit" id="submit">
        </form>
    </body>
</html>
