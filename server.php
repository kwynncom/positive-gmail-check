<?php
require_once('usageLimit/usageLimit.php');
require_once('positiveEmail.php');
require_once('isUserCookie.php');

class pemsDoit {

	function __construct() {
		$this->do10();
	}
	
	private function isRevReq() { 	    return isrv('revoke') === 'Y'; 	}

	private function condRevOnErr() {
	    if (!$this->isRevReq()) return;
	    new positiveEmailCl(null, true);
	    isucookie::unset(); // doesn't seem to do what I want, but can't hurt
	    $url = 'https://' . $_SERVER['HTTP_HOST'] . '/' . positiveEmailDefaults::peoaa['upath'];
	    kwjae(['url' => $url]);
	}

	function do10() {

		$url = '';
		$msgtxt = 'errSrvUnk';
		$gco = false;
		$ulo = false;

		try { 
			$ulo = new usageLimit();
			$gco = new positiveEmailCl($ulo);
			
			if ($url = $gco->getOAuthURL()) kwas(false, 'oauth - server');
			if ($this->isRevReq()) {
				$url = $gco->revokeAccess();
				kwas(false, 'revoking');
			}
			

			$msgtxt = $gco->getEmailCountTxt();
			if (time() < strtotime('2022-01-27 23:51')) kwas(false, 'test ex');
		} catch (Exception $exv) { 
		    $this->condRevOnErr();
		    kwnull();
		}
		
		if ($gco) $logs = $gco->getLog();

		$ulo = $gco ? $gco->getLimitsO() : new usageLimit();
		
		unset($gco);
		
		
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
