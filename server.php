<?php

require_once('main.php');

$res = new stdClass();

if (isset($gdo) && ($oaurl = $gdo->getOAuthURL())) $res->url = $oaurl;

$res->msgtxt = $msgtxt;

$dates  = date('g:i A ');
$dates .= '(' . date('s') . 's) ' . date('l, F j, Y');

$res->dates = $dates;

$glt = $ulo->getLimitsTxt();

$res->glt = $glt;

if (isset($exv)) {
    $emsg = $exv->getMessage();
    $pres  = $ulo->getPrev();
    
    $res->emsg = $emsg;
	if ($pres) {
		$res->msgtxt = $pres->msgtxt;
		$res->dates =  $pres->dates;
	} else {
		$res->msgtxt = $res->dates = 'no history';
	}

} else $ulo->setPrev($res);

$json = json_encode($res);

echo $json;

// $msgtxt is defined and I think needs to be defined
