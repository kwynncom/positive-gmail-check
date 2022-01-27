<?php

require_once('usageLimit/usageLimit.php');
require_once('gmailClient.php');

$msgtxt = 'Error';
$ulo = false;
try { 
    $ulo = new usageLimit();
    $gdo = new gmailClient();
    
    if (doRevoke()) $gdo->revokeToken();
    
    $ulo->putUse($gdo->check()); // *** get text / pre value of check, then check limit then check email
    $msgtxt = $gdo->getText();
    $ulo->setEmail($gdo->getEmail());
} catch (Exception $exv) {
	kwynn();
}
