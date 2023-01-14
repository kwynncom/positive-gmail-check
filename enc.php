<?php

require_once('dao.php');

function vsid() { return hash('sha256', startSSLSession()); }

class dao extends dao_plain {
	
public function __construct($logo) {
	$this->log = $logo;
	parent::__construct();
	$this->cob = new enc_cookies(self::tfnm);
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
	$etok = $this->cob->enc($ptok); unset($ptok);
	$emh = $this->cob->emailHash($email); unset($email);
	parent::upsertToken($etok, $emh);
}
} // class

class enc_cookies {
    //            1234567890123456 - 16 chars
    const iniv = 'P8ohKFo4nNae0ZBW';
	const goofs  = GooOAUTHWrapper::tnms;
	const obsfx  = '_ob';
	const eknm = 'enkey';
	
	public function __construct($goonm) {
		$this->oos = [];
		$this->rawGooTokO = [];
		$this->goonm = $goonm;
		$this->loadCookies();
	}

	private function loadCookies() {
		
		foreach(self::goofs as $f) {
			
			$j = kwifs($_COOKIE, $f . self::obsfx);
			if (!$j) continue;
			$a = [];
			if ($j) $a = json_decode($j, true);
			$this->oos[$f . self::obsfx] = $a;
		}
		
		return $this->getekida();
	}
	
	public function getekida() { 
		$ids = [];
		
		foreach($this->oos as $k => $v) {
			unset($v[self::eknm]);
			$ids[$k] = $v;
			
		}
		
		return $ids;
		
	}
	
	public function dec($dbo) { 
		if ($raw = $this->rawGooTokO) {
			return $raw; 
		}

		unset($raw);
		
		$pto = $dbo; unset($dbo);
		foreach(self::goofs as $f) {
			if (!isset($pto[$f])) continue;
			$sfx =  $f . self::obsfx;
			$dk = kwifs($this->oos, $sfx, self::eknm);
			if ($dk) {
				 $tdc = openssl_decrypt($pto[$f], 'AES-256-CBC', $dk, 0, self::iniv);
				 if ($tdc) $this->oos[$sfx][$f] = $pto[$f] = $tdc;
				 else unset($pto[$f]);
			}
			else unset($pto[$f]);
			
		}

		return $pto;
	}

    public function enc($ptok) { 
		
		$this->rawGooTokO = $ptok;
	
		$ra = [];
		$etok = $ptok;
		foreach(self::goofs as $f) {
			if (!isset($ptok[$f])) continue;
			 
			$dk = kwifs($this->oos, $f . self::obsfx, self::eknm); 
			if (!$dk) {
				$this->setKeyOb($f);
				$dk = kwifs($this->oos, $f . self::obsfx, self::eknm); 
			}
			
			kwas($dk, 'no enc key cook enc');
			$this->oos [$f . self::obsfx][$this->goonm] = $ptok;
			$etok[$f] = $this->oos [$f . self::obsfx][$this->goonm][$f] = openssl_encrypt($ptok[$f], 'AES-256-CBC', $dk, 0, self::iniv); 
			unset($ptok[$f]);
			$ra[$f][$this->goonm] = $etok;
		
		}
		
		return $this->getoosWOKey();
	}
    
	private function getoosWOKey() { 
		$o20 = $this->oos;
		foreach($o20 as $nm => $a) {
			$key = kwifs($o20, $nm, self::eknm); 	kwas($key, 'confirming key so I can delete it - failed');
			unset       ($o20 [$nm][self::eknm], $key);
			$key = kwifs($o20, self::eknm); 
			kwas(!$key, 'confirm gone failed');
		}
		return $o20;
	}
  
	private function setKeyOb($gobnm) {
		$tek = self::base62();
		$a = [];
		$a[self::eknm] = $tek;
		$a['tokty'] = $gobnm;
		$a['r_cookie'] = date('r');
		$a['_id'] = dao_generic_3::get_oids();
		$a['rid'] = sprintf('%02d', random_int(1, 99)) . '-' . base62(2); // not unique, but rare; something to quickly visually check
		$obsfx = $gobnm . self::obsfx;
		$this->oos[$obsfx] = $a;
		$j = json_encode($a);
		kwscookie($obsfx, $j, isucookie::getOpts());	

		
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
