<?php

function sortURLs($ain, $setu) { 
	$v = kwifs($ain, 'file', 'web', 'redirect_uris', 0);
	if ($v === $setu) return $ain;
	$set = false;
	$ain    ['file']['web']['redirect_uris'][0] = $setu;
	for ($i=1;	 $u = kwifs($ain, 'file', 'web', 'redirect_uris', $i) ; $i++) {
		if ($u === $setu) { $ain['file']['web']['redirect_uris'][$i] = $v;	$set = true; }
	}
	 
	if (!$set) $ain['file']['web']['redirect_uris'][count( $ain['file']['web']['redirect_uris'])] = $v;
	
	return $ain;
}