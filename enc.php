<?php

require_once('dao.php');

function vsid() { return hash('sha256', startSSLSession()); }

class dao extends dao_plain {
	
public function __construct($logo) {
	$this->log = $logo;
	parent::__construct();
	$this->cob = new enc_cookies(self::tfnm);
}

public function deleteTokenKwDB($ignore = null) {
	parent::deleteTokenKwDB($this->cob->getekida());
}

public function getTokenDB() {
	
	$etok  = parent::getTokenDBO($this->cob->getekida());
	if (!$etok) return false;
	$dtok = $this->cob->dec($etok);

	$datok = kwifs($dtok, 'access_token');
	if (!$datok) {
		$this->log->log('no at db');
		return false; // if decryption key is lost; do I need to delete stuff, too??? 
	}
	if (isset($dtok['refresh_token'])) { // if can't decrypt key name (enc key object key) needs to not exist
		$this->log->log('yes rt db');
	} 

	return $dtok;
}

public static function expireCookies() {
	enc_cookies::forceExpire();
}

public function getEmailHash($emain) { return $this->emailHash = $this->cob->emailHash($emain); }

public function upsertToken($ptok, $emailHash = null) {
	$etok = $this->cob->enc($ptok, $emailHash); unset($ptok);
	parent::upsertToken($etok, $emailHash);
}
} // class

class enc_cookies {
    //            1234567890123456 - 16 chars
    const iniv = 'P8ohKFo4nNae0ZBW';
	const goofs  = GooOAUTHWrapper::tnms;
	const eknm = 'enkey';
	const cooBasenm = 'gooObWr';
	const cooPub = 'kwPEpub';
	const cooPri = 'kwPEpri';

	const keybits = 1024; // less than 2048 only apt for testing
	
	public static function forceExpire() {
		if (isset($_COOKIE[self::cooBasenm])) kwscookie(self::cooBasenm, false, false);
		isucookie::unset();
	}
	
	public function __construct($goonm) {
		$this->oos = [];
		$this->pra = [];
		
		$this->goonm = $goonm;
		$this->loadCookies();
	}

	private function loadCookies() {
					
		if (($j = kwifs($_COOKIE, self::cooBasenm))) $this->oos = json_decode($j, true);
		else $this->setKeyOb();

	}
	
	public function getekida() { 
		$ids = [];
		$v = $this->oos;
		unset($v[self::eknm]);
		return $v;
		
	}
	
	public function dec($dbo) { 
		
		$pto = $dbo; unset($dbo);
		foreach(self::goofs as $f) {
			if (!isset($pto[$f])) continue;
			$dk = kwifs($this->oos, self::eknm);
			if ($dk) {
				 $tdc = openssl_decrypt($pto[$f], 'AES-256-CBC', $dk, 0, self::iniv);
				 if ($tdc) $this->oos[$this->goonm][$f] = $pto[$f] = $tdc;
				 else unset($pto[$f]);
			}
			else unset($pto[$f]);
			
		}

		return $pto;
	}
	
	private function puk10($eh) {
		if ($eh) $this->oos['emailHash'] = $eh;
		else return;
		
		if (1) {
			$pro = openssl_pkey_new(['private_key_bits' => self::keybits]);
			$put = openssl_pkey_get_details($pro)['key'];
			openssl_pkey_export($pro, $prt); unset($pro);
			$this->pra = [];
			$this->pra[self::cooPub] = $put;
			$this->pra[self::cooPri] = $prt;
			self::popIDs($this->pra, 'keypair');
		}
		return;
		
		
	}

    public function enc($ptok, $eh) { 
		
		$this->emailHash = $eh;
		
		$this->puk10($eh);
		 
		$dk = kwifs($this->oos, self::eknm);  kwas($dk, 'dec key should be set');
	
		$this->oos[$this->goonm] = $ptok;
		
		foreach(self::goofs as $f) {
			if (!isset($ptok[$f])) continue;
			$this->oos[$this->goonm][$f] = openssl_encrypt($ptok[$f], 'AES-256-CBC', $dk, 0, self::iniv); 
		}
		
		return $this->getoosWOKey();
	}
    
	private function getoosWOKey() { 
		$o20 = $this->oos;
		$key = kwifs($o20, self::eknm); 	kwas($key, 'confirming key so I can delete it - failed');
		unset       ($o20 [self::eknm], $key);
		$key = kwifs($o20, self::eknm); 
		kwas(!$key, 'confirm gone failed');
		return $o20;
	}
  
	private function setKeyOb() {
		$tek = self::base62();
		$a = [];
		$a[self::eknm] = $tek;
		self::popIDs($a, 'baseCookie');
		$this->oos = $a;
		$this->renewCookie();
	}
	
	public static function popIDs(array &$a, $nm) {
		$a['_id']   = dao_generic_3::get_oids();
		$a['idish'] = sprintf('%02d', random_int(1, 99)) . '-' . base62(2); // not unique, but rare; something to quickly visually check
		$a['r_' . $nm] = date('r');
		return;
	}
	
	private function renewCookie() {
		$this->renewPrivCoo();
		$this->renewBaseCoo();
	}

	private function renewPrivCoo() {
		foreach([self::cooPub, self::cooPri] as $f) {
			$a = kwifs($this, 'pra', $f);
			if ($a) kwscookie($f, json_encode($a), isucookie::getOpts());					
		}
	}

	private function renewBaseCoo() {
		$a = $this->oos;
		unset($a[$this->goonm]);
		$j = json_encode($a);
		kwscookie(self::cooBasenm, $j, isucookie::getOpts());			
	}
	
    private static function base62() {
		$len = random_int(47, 51);
		return base62::get($len);
    }

	public function emailHash($t) {
		if (!$t) return false;
		$this->renewCookie();
		isucookie::set();
		for ($i=0; $i < 2806; $i++) $t = crypt($t, 'apIpUIgaIsuu5y3kqiAXVBdiTGclx2');
		return $t;
	}
	
}
