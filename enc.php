<?php

require_once('dao.php');

function vsid() { return hash('sha256', ptvsid()); }

class dao extends dao_plain {

public function __construct() {
	parent::__construct();
	$this->enc = new enc_cookies();
}
public function updateEmail($pt) { parent::updateEmail(hash('sha256', $pt)); }

public function getToken() {
	$tok  = parent::getToken();
	if (!$tok) return $tok;
	$ptat = $this->enc->dec($tok['access_token'], 'atkey');
	if (isset($tok['refresh_token'])) {
		$ptrt = $this->enc->dec($tok['refresh_token'], 'rtkey');
		$tok['refresh_token'] = $ptrt;	
	}
	$tok['access_token' ] = $ptat;

	return $tok;
}

public static function expireCookies() {
	$fs = ['atkey', 'rtkey'];
	foreach($fs as $f) if (isset($_COOKIE[$f])) kwscookie($f, false, false);
	isucookie::unset();
}

public function updateToken($tok) { $this->modToken($tok, 'update'); }

public function putToken($tok) { $this->modToken($tok, 'insert'); }

private function modToken($ptok, $act) {
	$ptok['access_token' ] = $this->enc->enc($ptok['access_token' ], 'atkey');
	if (isset(
	$ptok['refresh_token']))
	$ptok['refresh_token'] = $this->enc->enc($ptok['refresh_token'], 'rtkey');
	if ($act === 'insert') parent::putToken($ptok);
	if ($act === 'update') parent::updateToken($ptok);
}
} // class

class enc_cookies {
    
    const iv = 'K4e0pCNqBatnoNIV';
    
    public function enc($pt, $cokey) {
	return openssl_encrypt($pt, 'AES-256-CBC', $this->getKey($cokey), 0, self::iv);
    }
    
    public function dec($ct, $cokey) {
	return openssl_decrypt($ct, 'AES-256-CBC', $this->getKey($cokey), 0, self::iv);

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
