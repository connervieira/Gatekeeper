<?php
include "./config.php";


if (!function_exists("seconds_to_human_readable")) { // Check to see if this function needs to be created.
    function seconds_to_human_readable($seconds) {
        $date_time_from = new \DateTime('@0');
        $date_time_to = new \DateTime("@$seconds");
        return $date_time_from->diff($date_time_to)->format('%a days %h:%I:%S');
    }
}

if (!function_exists("speak_event")) { // Check to see if this function needs to be created.
    function speak_event($vehicles, $device, $type) {
        global $gatekeeper_config;
        $vehicles = array_change_key_case($vehicles, CASE_UPPER); // Convert all vehicles in the vehicles array to uppercase for standardization.

        if (sizeof($vehicles) <= 0) { // Check to see if the array of vehicles is empty.
            error_log("Gatekeeper's 'speak_event()' function was supplied with an empty list of vehicles. This should never occur, and indicates a bug.");
            exit();
        }

        if (sizeof($vehicles) > 1) { // Check to see if more than one vehicle was detected.
            $vehicle_audio_path = "./assets/audio/voice/vehicles/multiple.mp3";
        } else if (in_array($vehicles[0], array_keys($gatekeeper_config["vehicles"]))) {
            $vehicle_audio_path = "./assets/audio/voice/vehicles/" . $vehicles[0] . "/name.mp3";
        } else {
            $vehicle_audio_path = "./assets/audio/voice/vehicles/unknown.mp3";
        }
        $device_audio_path = "./assets/audio/voice/devices/" . $device . "/name.mp3";

        if ($type == "enter") {
            $type_audio_path = "./assets/audio/voice/actions/entered.mp3";
        } else if ($type == "exit") {
            $type_audio_path = "./assets/audio/voice/actions/exited.mp3";
        } else {
            $type_audio_path = "./assets/audio/voice/actions/passed.mp3";
        }

        if (file_exists($vehicle_audio_path) and file_exists($type_audio_path) and file_exists($device_audio_path)) { // Check to make sure all of the required voice samples exist.
            $vehicle_audio_length = floatval(shell_exec("soxi -D " . $vehicle_audio_path))*1000;
            $type_audio_length = floatval(shell_exec("soxi -D " . $type_audio_path))*1000;
            $device_audio_length = floatval(shell_exec("soxi -D " . $device_audio_path))*1000;

            echo "
            <script>
                var vehicle_audio_path = '" . $vehicle_audio_path . "';
                var type_audio_path = '" . $type_audio_path . "';
                var device_audio_path = '" . $device_audio_path . "';

                var vehicle_audio = new Audio(vehicle_audio_path);
                var type_audio = new Audio(type_audio_path);
                var device_audio = new Audio(device_audio_path);

                setTimeout(function(){ vehicle_audio.play() }, 0);
                setTimeout(function(){ type_audio.play() }, " . $vehicle_audio_length . ");
                setTimeout(function(){ device_audio.play() }, " . $vehicle_audio_length + $type_audio_length . ");
            </script>
            ";
        } else {
            if (!file_exists($vehicle_audio_path)) {
                echo "<p class='bad'>The " . $vehicle_audio_path . " audio file is missing.</p>";
            }
            if (!file_exists($device_audio_path)) {
                echo "<p class='bad'>The " . $device_audio_path . " audio file is missing.</p>";
            }
            if (!file_exists($type_audio_path)) {
                echo "<p class='bad'>The " . $type_audio_path . " audio file is missing.</p>";
            }
            if (file_exists("./assets/audio/voice/system/missingsamples.mp3")) { // Make sure the missing samples speech audio file exists.
                echo "
                <script>
                    var audio = new Audio('./assets/audio/voice/system/missingsamples.mp3');
                    audio.play();
                </script>
                ";
            } else {
                error_log("Gatekeeper's 'missing audio' voice sample is missing.");
            }
        }
    }
}

?>
