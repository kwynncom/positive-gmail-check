<?php

require_once('enc.php');

function setSessionDets(&$o) {
    $o->ip    = isset($_SERVER['REMOTE_ADDR'    ]) ? $_SERVER['REMOTE_ADDR'    ] : null ;
    $o->agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    $o->sid   = vsid();
}

function doRevoke() {
    if (!isset($_REQUEST['revoke'])) return false;
    if ($_REQUEST['revoke'] === 'Y') return true;
    return false;
}