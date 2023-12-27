<?php
$gatekeeper_config_database_filepath = "./config.json";

if (!file_exists($gatekeeper_config_database_filepath)) { // Check to see if the configuration file needs to be created.
    $gatekeeper_config = array();
    $gatekeeper_config["auth"]["provider"] = "../dropauth/authentication.php";
    $gatekeeper_config["auth"]["admin"] = "";
    $gatekeeper_config["auth"]["authorized_users"] = array();
    $gatekeeper_config["interface"]["branding"]["name"] = "Gatekeeper";
    $gatekeeper_config["interface"]["theme"] = "dark";
    $gatekeeper_config["alerts"]["voice"]["enabled"] = true;
    $gatekeeper_config["alerts"]["notifications"]["enabled"] = false;
    $gatekeeper_config["alerts"]["notifications"]["provider"] = "ntfy";
    $gatekeeper_config["alerts"]["notifications"]["server"] = "https://ntfy.sh/mytopic";
    $gatekeeper_config["devices"] = array();
    $gatekeeper_config["vehicles"] = array();

    if (is_writable(dirname($gatekeeper_config_database_filepath))) {
        file_put_contents($gatekeeper_config_database_filepath, json_encode($gatekeeper_config, (JSON_UNESCAPED_SLASHES)));
    } else {
        echo "<p class='red'>Error: The " . realpath(dirname($gatekeeper_config_database_filepath)) . " directory is not writable.</p>";
    }
}

$gatekeeper_config = json_decode(file_get_contents($gatekeeper_config_database_filepath), true);

?>
