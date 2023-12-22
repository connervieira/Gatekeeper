<?php

function seconds_to_human_readable($seconds) {
    $date_time_from = new \DateTime('@0');
    $date_time_to = new \DateTime("@$seconds");
    return $date_time_from->diff($date_time_to)->format('%a days %h:%I:%S');
}

?>
