<?php
require_once('usageLimit/usageLimit.php');
require_once('positiveEmail.php');
require_once('isUserCookie.php');

class pemsDoit {

	function __construct() {
		$this->do10();
	}
	
	function do10() {

		$url = '';
		$ulo = false;
		$msgtxt = 'errSrvUnk';

		try { 
			$ulo = new usageLimit();
			$gco = new positiveEmailCl();
			if ($url = $gco->getOAuthURL()) kwas(false, 'oauth - server');
			if (kwifs($_REQUEST, 'revoke') === 'Y') {
				$url = $gco->revokeAccess();
				kwas(false, 'revoking');
			}
			
			$gdo = $gco;
			$ulo->putUse($gdo->checkEmail()); // *** get text / pre value of check, then check limit then check email
			$msgtxt = $gdo->getText();
			$ulo->setEmail($gdo->getEmailHash());
			if (time() < strtotime('2022-01-27 23:51')) kwas(false, 'test ex');
		} catch (Exception $exv) { }


		unset($gdo, $gco);
		
		$now   = time();
		$dates = date('g:i A', $now) . ' (' . date('s', $now) . 's) ' . date('l, F j, Y', $now); unset($now);
		$glt = $ulo->getLimitsTxt();
		$dvs = get_defined_vars(); // **** This may be a bad idea.  Subject to clutter.
		$this->doerr ($dvs);
		$this->dook10($dvs);
	}
	
	function doerr($vsin) {
		if (!isset($vsin['exv']))  return;
		extract($vsin); unset($vsin, $dates, $glt, $msgtxt);
		$emsg = $exv->getMessage();
		$pres = $ulo->getPrev();
		unset($pres['url']);
				
		extract($pres); unset($pres);
		$vs20 = get_defined_vars();
		$this->cleanseV($vs20);
		kwjae($vs20);
	}
	
	function dook10($vsin) {
		// if (!kwifs($vsin, 'url')) isucookie::set();
		extract($vsin);
		$this->cleanseV($vsin);
		$ulo->setPrev($vsin);
		kwjae($vsin);		
	}
	
	function cleanseV(&$rin) { unset($rin['ulo'], $rin['exv'], $rin['revoke']); }
}

new pemsDoit();
