<?php

require_once('/opt/kwynn/kwcod.php');
require_once('/opt/kwynn/kwutils.php');
require_once('FileToMongo_general.php');

if (time() < strtotime('2020-06-19 23:30')) { // init setup

    if      (isKwDev()) $path = '/home/[user]/.goauth/client_secret_2_web_2119...-xyz.json';
    else if (isAWS())   $path = '/home/ubuntu/.goauth/client_secret_....json';

    kwas(isset($path), 'path not set');

    $mo = new mongoToFile('qemail', 'GMail_app_secret_2018_to_2020_06_1', $path);
    $fname = $mo->getFName();
    $mo->close();
}

if (time() > strtotime('2020-06-20 02:30')) exit(0); // testing


$mo = new mongoToFile('qemail', 'GMail_app_secret_2018_to_2020_06_1');
$fname = $mo->getFName();
$mo->close();
