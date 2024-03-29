<?php

require_once('/opt/kwynn/kwutils.php');

interface qemconfig { const dbname = 'qemail'; }

require_once('enc.php');

function setSessionDets(array | null &$a, bool $addSid = false) {
	// if (!$a) $a = [];
	$a['ip']    = isset($_SERVER['REMOTE_ADDR'    ]) ? $_SERVER['REMOTE_ADDR'    ] : null;
	$a['agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
	if ($addSid) $a['sid']   = pemhsid();
}
