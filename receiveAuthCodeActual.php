<?php

if ((time() > strtotime('2023-01-24 02:30'))) {
	require_once('positiveEmail.php');
	new positiveEmailCl();
	kwynn();
}
else {
	require_once(__DIR__ . '/login/index.php');
}
