<?php

function getTestMode() {

    if (getenv('KWYNN_ID_201701') === 'aws-nano-1') return false;
    
    return false;
    
    if (isKwDev()) return 'nock';
    
    $ret = false;
    if (time() < strtotime('2018-01-28 04:15')) {
	$ret = 'noemail';
    }
    return $ret;
}

