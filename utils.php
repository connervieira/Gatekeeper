<?php
include "./config.php";


function seconds_to_human_readable($seconds) {
    $date_time_from = new \DateTime('@0');
    $date_time_to = new \DateTime("@$seconds");
    return $date_time_from->diff($date_time_to)->format('%a days %h:%I:%S');
}

function speak_event($vehicle, $device, $type) {
    global $gatekeeper_config;
    $vehicle = strtoupper($vehicle);

    if (in_array($vehicle, array_keys($gatekeeper_config["vehicles"]))) {
        $vehicle_audio_path = "./assets/audio/voice/vehicles/" . $vehicle . "/name.mp3";
    } else {
        $vehicle_audio_path = "./assets/audio/voice/vehicles/unknown.mp3";
    }
    $device_audio_path = "./assets/audio/voice/devices/" . $device . "/name.mp3";
    $type_audio_path = "./assets/audio/voice/actions/" . $type . ".mp3";

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

?>
