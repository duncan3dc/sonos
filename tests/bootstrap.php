<?php

require __DIR__ . "/../vendor/autoload.php";

if (in_array("--live-tests", $_SERVER["argv"])) {
    echo "\nWARNING: These tests will make changes to the Sonos devices on the network:\n";

    $warnings = [
        "Queue contents will be changed",
        "Music will play",
        "Volume will be changed",
        "Playlists will be created",
        "Alarms will be created",
    ];
    foreach ($warnings as $warning) {
        echo "    * {$warning}\n";
    }

    if (!in_array("--skip-wait", $_SERVER["argv"])) {
        $sleep = 5;
        echo "\nTests will run in " . $sleep . " seconds";
        for ($i = 0; $i < $sleep; $i++) {
            echo ".";
            sleep(1);
        }
        echo "\n";
    }

    echo "\n";
}
