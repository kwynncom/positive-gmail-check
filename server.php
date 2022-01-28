<?php
require_once('usageLimit/usageLimit.php');
require_once('gmailClient.php');

class pemsDoit {

	function __construct() {
		$this->do10();
	}
	
	function do10() {

		$ulo = false;
		$msgtxt = 'Error';
		try { 
			$ulo = new usageLimit();
			$gdo = new gmailClient();

			if (doRevoke()) $gdo->revokeToken();

			$ulo->putUse($gdo->check()); // *** get text / pre value of check, then check limit then check email
			$msgtxt = $gdo->getText();
			$ulo->setEmail($gdo->getEmail());
		} catch (Exception $exv) { }

		if (isset($gdo)) $oaurl = $gdo->getOAuthURL();
	
		unset($gdo);

		$dates  = date('g:i A ');
		$dates .= '(' . date('s') . 's) ' . date('l, F j, Y');

		$glt = $ulo->getLimitsTxt();

		$dvs = get_defined_vars();
		$this->doerr ($dvs);
		$this->dook10($dvs);
	}
	
	function doerr($vsin) {
		if (!isset($vsin['exv']))  return;
		extract($vsin);
		$emsg = $exv->getMessage();
		$pres = $ulo->getPrev();
		$vs20 = get_defined_vars();
		$this->cleanseV($vs20);
		kwjae($vs20);
		
	}
	
	function cleanseV(&$rin) {
		unset($rin['ulo']);
		unset($rin['res']);		
		unset($rin['exv']);
	}
	
	function dook10($vsin) {
		extract($vsin);
		$this->cleanseV($vsin);
		$ulo->setPrev($vsin);
		kwjae($vsin);		
	}
}
new pemsDoit();
