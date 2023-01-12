<?php

require_once('dao.php');

function vsid() { return hash('sha256', startSSLSession()); }

class dao extends dao_plain {

public function __construct() {
	parent::__construct();
	$this->enc = new enc_cookies();
}

public function getToken() {
	$tok  = parent::getToken();
	if (!$tok) return $tok;
	$ptat = $this->enc->dec($tok['access_token'], 'atkey');

	if (!$ptat) return false; // if decryption key is lost; do I need to delete stuff, too??? 
	if (isset($tok['refresh_token'])) {
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

public function updateJustUsedToken($tok, $em) { 
	$this->modToken($tok, 'update', $em); 
}

public function insertToken($tok, $em = null) { $this->modToken($tok, 'insert', $em); }

private function modToken($ptok, $act, $email) {
	$ptok['access_token' ] = $this->enc->enc($ptok['access_token' ], 'atkey');
	if (isset(
	$ptok['refresh_token']))
	$ptok['refresh_token'] = $this->enc->enc($ptok['refresh_token'], 'rtkey');
	// if ($act === 'insert') parent::insertToken($ptok, $email);
	// if ($act === 'update') 
	parent::updateToken($ptok, $email);
}
} // class

class enc_cookies {
    
    const iv = 'K4e0pCNqBatnoNIV';
	
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

	$basea = [ord('A'), ord('a'), ord('0')];

	for ($i=0, $rs = ''; $i < $len; $i++)
	   for ($j=0, $ri = random_int(0, 61); $j < 62; $j++, $ri -= 26)
		if ($ri < 26) { $rs .= chr($basea[$j] + $ri); break; }

	return $rs;
    }
}
