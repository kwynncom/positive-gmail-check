<?php

require_once('dao.php');

function vsid() { return hash('sha256', startSSLSession()); }

class dao extends dao_plain {

public function __construct($logo) {
	$this->log = $logo;
	parent::__construct();
	$this->cob = new enc_cookies();
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
	$fs = ['atekey', 'rtekey'];
	foreach($fs as $f) if (isset($_COOKIE[$f])) kwscookie($f, false, false);
	isucookie::unset();
}

public function upsertToken($ptok, $email = null) {
	$ptok['access_token' ] = $this->cob->enc($ptok['access_token' ], 'atekey');
	if ($rt = kwifs($ptok, 'refresh_token'))
		$ptok['refresh_token'] = $this->cob->enc($rt, 'rtekey'); unset($rt);
		
	$emh = $this->cob->emailHash($email); unset($email);
	parent::upsertToken($ptok, $emh);
}
} // class

class enc_cookies {
    //            1234567890123456 - 16 chars
    const iniv = 'P8ohKFo4nNae0ZBW';
	const cofs = ['atekeyo', 'rtekeyo'];
	const dbidfs = ['_id', 'r', 'rid'];
	
	public function getekida() {
		
		$ra = [];
		
		foreach(self::cofs as $fk => $f) {
			$ta = $this->getEKeyO($f);
			$this->$f = $ta;
			foreach(self::dbidfs as $df => $dv)  $ra[$fk][$df] = $dv;
		}
		
		return $ra;
	}
	
	public function dec($dbo) { 
		
		$pt = openssl_decrypt($ct, 'AES-256-CBC', $this->getKey($coname), 0, self::iniv);
		if (!$pt) return $pt;
		$this->putCache($coname, $pt, $ct);
		return $pt;
	}
	

	private function getCache($coname, $pt)		{ return kwifs($this, $coname, $pt);	}
    private function putCache($coname, $pt, $ct) { $this->$coname[$pt] = $ct;		}
	
    public function enc($pt, $coname) { 
		if ($o = $this->getCache($coname, $pt)) return $o;
		$ct = openssl_encrypt($pt, 'AES-256-CBC', $this->getKey($coname), 0, self::iniv); 
		$this->putCache($coname, $pt, $ct);
		return $ct;
	}
    
    private function getKey ($coname) {
		if ($k = $this->getEKeyO($coname)) return $k;
		self::getNewKeyO($coname);
		return $cikey;
    }
	
	private function getEKeyO($coname) {
		if (!($o = kwifs($_COOKIE,	    'coname'))) return false;
		return json_decode($o, true);
		
	}
    
	private static function getNewKeyO($coname) {
		$tek = self::base62();
		$a = [];
		$a[$coname] = $tek;
		$a['r'] = date('r');
		$a['_id'] = dao_generic_3::get_oids();
		$a['rid'] = sprintf('%02d', random_int(1, 99)) . '-' . base62(2); // not unique, but rare; something to quickly visually check
		$j = json_encode($a);
		kwscookie($coname, $j, isucookie::getOpts());	
	}
	
	private static function putKeyO() {
		
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
