<?php

require_once('/opt/kwynn/kwutils.php');
require_once('/opt/kwynn/crackObject.php');


$j = file_get_contents('/var/kwynn/gooauth/t');
$a = json_decode($j, true);

$a = crackObject::crackGoo($a);
$f = kwifs($a, "Google_Clienttoken");

kwynn();

// 		if (substr($p, 0, 3) === "\x00*\x00") return substr($p, 3);

$f = kwifs($a, "\x00Google\Client\x00token");
exit(0);

