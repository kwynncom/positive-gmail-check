<?php

require_once('dao.php');

function vsid() { return hash('sha256', startSSLSession()); }

class dao extends dao_plain {

public function __construct($logo) {
	$this->log = $logo;
	parent::__construct();
	$this->enc = new enc_cookies();
}

public function getToken() {
	$tok  = parent::getToken();
	if (!$tok) return false;
	$ptat = $this->enc->dec($tok['access_token'], 'atkey');

	if (!$ptat) {
		$this->log->log('no at db');
		return false; // if decryption key is lost; do I need to delete stuff, too??? 
	}
	if (isset($tok['refresh_token'])) {
		$this->log->log('yes rt db');
		$ptrt = $this->enc->dec($tok['refresh_token'], 'rtkey');
		$tok['refresh_token'] = $ptrt;	
		if (!$ptrt) return false;
	}
	$tok['access_token' ] = $ptat;

	return $tok;
}

public static function expireCookies() {
	$fs = ['atkey', 'rtkey'];
	foreach($fs as $f) if (isset($_COOKIE[$f])) kwscookie($f, false, false);
	isucookie::unset();
}

public function upsertToken($ptok, $email = null) {
	$ptok['access_token' ] = $this->enc->enc($ptok['access_token' ], 'atkey');
	if ($rt = kwifs($ptok, 'refresh_token'))
		$ptok['refresh_token'] = $this->enc->enc($rt, 'rtkey'); unset($rt);
		
	$emh = $this->enc->emailHash($email); unset($email);
	parent::upsertToken($ptok, $emh);
}
} // class

class enc_cookies {
    
    const iv = 'K4e0pCNqBatnoNIV';
	
	public function emailHash($t) {
		if (!$t) return false;
		for ($i=0; $i < 2806; $i++) $t = crypt($t, 'apIpUIgaIsuu5y3kqiAXVBdiTGclx2');
		return $t;
	}
	
	private function getCache( $cokey, $pt) {
		$cached = kwifs($this, $cokey, $pt);
		if ($cached) return $cached;
	}
    
	private function putCache($cokey, $pt, $ct) {
		$this->$cokey[$pt] = $ct;	
		return;
	}
	
    public function enc($pt, $cokey) { 
		if ($cct = $this->getCache($cokey, $pt)) return $cct;
		$ct = openssl_encrypt($pt, 'AES-256-CBC', $this->getKey($cokey), 0, self::iv); 
		$this->putCache($cokey, $pt, $ct);
		return $ct;
	}
    public function dec($ct, $cokey) { 
		$pt = openssl_decrypt($ct, 'AES-256-CBC', $this->getKey($cokey), 0, self::iv); 
		$this->putCache($cokey, $pt, $ct);
		return $pt;
	}
    
    public static function getKey($cokey) {
		if (isset($_COOKIE[$cokey])) return $_COOKIE[$cokey];
		$cikey = self::base62();
		kwas(isset($cikey[40]), 'key not long enough');
		kwscookie($cokey, $cikey, isucookie::getOpts());
		return $cikey;
    }
    
    private static function base62() {
		$len = random_int(45, 50);
		return base62::get($len);
    }
}
