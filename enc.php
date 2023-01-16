<?php

require_once('dao.php');

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
	if (!$emailHash) return;
	$this->usePubKeys(isset($etok[self::tfnm]['refresh_token']), $etok[self::tfnm]['created'], $etok['_id'], $emailHash);
}

private function usePubKeys(bool $isrt, int $cre, string $id, string $emh) {
	$a = parent::getPubKeys(     $isrt,     $cre,        $id,	     $emh);
	parent::updatePubsWithSym($this->cob->encWithPub($a));
}

} // class

class enc_cookies {
    //            1234567890123456 - 16 chars
    const iniv = 'P8ohKFo4nNae0ZBW';
	const goofs  = GooOAUTHWrapper::tnms;
	const eknm = 'symkey';
	const cooBas = 'bas';
	const cooPub = 'pub';
	const cooPri = 'pri';
	const ofs = [self::cooBas, self::cooPub, self::cooPri];
	const prifs = [self::eknm, self::cooPri];

	const keybits = 1024; // less than 2048 only apt for testing
	
	public static function forceExpire() {
		foreach(self::ofs as $f) if (isset($_COOKIE[$f])) kwscookie($f, false, false);
		isucookie::unset();
	}
	
	public function __construct($goonm) {
		$this->oas = [];
		foreach(self::ofs as $f) $this->oas[$f] = [];
		$this->goonm = $goonm;
		$this->loadCookies();
	}

	private function loadCookies() {
		foreach(self::ofs as $f) if (($j = kwifs($_COOKIE, $f))) $this->oas[$f] = json_decode($j, true);
	}
	
	public function getekida() : array { 
		$ids = [];
		$v = kwifs($this, 'oas', self::cooBas, '_id');
		if (!$v) return [];
		else return ['_id' => $v];
	}
	
	private function getSymKey() { return kwifs($this, 'oas', self::cooBas, self::eknm);	}
	
	public function dec($dbo) { 
		
		$pto = $dbo; unset($dbo);
		$dk = $this->getSymKey();
		
		foreach(self::goofs as $f) {
			if (!isset($pto[$f])) continue;
			if ($dk) {
				 $tdc = openssl_decrypt($pto[$f], 'AES-256-CBC', $dk, 0, self::iniv);
				 if ($tdc) $this->oas[self::cooBas][$this->goonm][$f] = $pto[$f] = $tdc;
				 else unset($pto[$f]);
			}
			else unset($pto[$f]);
			
		}

		return $pto;
	}
	
	private function savePESK() {
		
	}
	
	private function setAllOnTok() {
		isucookie::set();
		$this->setKeyOb();
		$this->renewCookie();		
	}
	
    public function enc($ptok, $eh) { 
		
		$this->emailHash = $eh;
		
		$this->setAllOnTok();

		$sk = kwifs($this, 'oas', self::cooBas, self::eknm);  kwas($sk, 'sym key should be set');
	
		$this->oas[self::cooBas][$this->goonm] = $ptok;
		
		foreach(self::goofs as $f) {
			if (!isset($ptok[$f])) continue;
			$this->oas[self::cooBas][$this->goonm][$f] = openssl_encrypt($ptok[$f], 'AES-256-CBC', $sk, 0, self::iniv); 
		}
		
		return $this->getoosWOKey();
	}
    
	private function getoosWOKey() { 
		$o20 = $this->oas[self::cooBas];
		unset       ($o20[self::eknm], $o20[self::cooPri]);
		return $o20;
	}
  
	public function encWithPub(array $a) : array {
		
		$po = openssl_pkey_get_public($this->oas[self::cooBas][self::cooPub]);
		$sk = $this->getSymKey();

		$ret = [];
		foreach($a as $r) {
			kwas($r['addr'] === $this->emailHash, 'email hashes do not match - pub key enc'); // sanity check
			openssl_public_encrypt($sk,  $cit, $po);
			$ret[$r['_id']]['symkeypue'] = base64_encode($cit); unset($cit);
		}
		
		return $ret;
	}
	


	
	private function setKeyOb() {
		
		if (kwifs($this, 'oas', self::cooBas)) {
			return;
		}

		$tek = self::base62();
		$a = [];
		$a[self::eknm] = $tek;
		
		$pro = openssl_pkey_new(['private_key_bits' => self::keybits]);
		$put = openssl_pkey_get_details($pro)['key'];
		openssl_pkey_export($pro, $prt); unset($pro);
		$a[self::cooPub] = $put;
		$a[self::cooPri] = $prt;
				
		self::popIDs($a, 'baseCookie');
		$this->oas[self::cooBas] = $a;
	}
	
	public static function popIDs(array &$a, $nm) {
		$a['_id']   = dao_generic_3::get_oids();
		$a['idish'] = sprintf('%02d', random_int(1, 99)) . '-' . base62(2); // not unique, but rare; something to quickly visually check
		$a['r_' . $nm] = date('r');
		return;
	}
	


	private function renewCookie() {
		$a = kwifs($this->oas, self::cooBas);
		unset($a[$this->goonm]);
		$j = json_encode($a);
		kwscookie(self::cooBas, $j, isucookie::getOpts());			
	}
	
    private static function base62() {
		$len = random_int(47, 51);
		return base62::get($len);
    }

	public function emailHash($t) {
		if (!$t) return false;
		for ($i=0; $i < 2806; $i++) $t = crypt($t, 'apIpUIgaIsuu5y3kqiAXVBdiTGclx2');
		return $t;
	}
	
}
