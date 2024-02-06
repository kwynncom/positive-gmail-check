<?php

function pemhsid(bool $start = true) : string { return enc_cookies::pemhsid($start); } 

class enc_cookies {
	
	private array $oas;
	private string $goonm;
	private readonly string | null $emailHash; // should not be null; not sure
	
    //            1234567890123456 - 16 chars
    const iniv = 'P8ohKFo4nNae0ZBW';
	const goofs  = GooOAUTHWrapper::tnms;
	const eknm = 'symkey';
	const cooBas = 'bas';
	const cooPub = 'pub';
	const cooPri = 'pri';
	const ofs = [self::cooBas, self::cooPub, self::cooPri];
	const prifs = [self::eknm, self::cooPri];

	const keybits  = dao_plain::keybits ; 
	const keybitsf = dao_plain::keybitsf;
	
	public static function pemhsid(bool $start = true) : string { 
		$sid = $start ? startSSLSession() : contSSLSession();
		return hash('sha256', $sid); 
	}
	
	public static function forceExpire() {
		if (isset($_COOKIE[self::cooBas])) kwscookie(self::cooBas, false, false);
		isucookie::unset();
	}
	
	public function __construct($goonm) {
		$this->oas = [];
		foreach(self::ofs as $f) $this->oas[$f] = [];
		$this->goonm = $goonm;
		$this->loadCookies();
	}

	private function loadCookies() {
		if (($j = kwifs($_COOKIE, self::cooBas))) $this->oas[self::cooBas] = json_decode($j, true);
	}
	
	public function getekida() : array { 
		$ids = [];
		$v = kwifs($this, 'oas', self::cooBas, '_id');
		if (!$v) return [];
		else return ['_id' => $v];
	}
	
	private function getSymKey() { return kwifs($this, 'oas', self::cooBas, self::eknm);	}
	
	public function dec(array $dbo, string $pusk) { 
		
		$pto = $dbo; unset($dbo);
		
		$this->decFromPriv($pusk);
		
		$dk = $this->getSymKey();
		
		$ok = 0;
		foreach(self::goofs as $f) {
			if (!isset($pto[$f])) continue;
			if ($dk) {
				 $tdc = openssl_decrypt($pto[$f], 'AES-256-CBC', $dk, 0, self::iniv);
				 if ($tdc) {
					 $ok++;
					 $this->oas[self::cooBas][$this->goonm][$f] = $pto[$f] = $tdc;
				 }
				 else unset($pto[$f]);
			}
			else unset($pto[$f]);
			
		}
		
		if ($ok) $this->renewCookie();

		
		return $pto;
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
		
		$sk = $this->getSymKey();

		$ret = [];
		foreach($a as $r) {
			kwas($r['addr'] === $this->emailHash, 'email hashes do not match - pub key enc'); // sanity check
			$po = openssl_pkey_get_public($r['pub']);
			kwas(openssl_public_encrypt($sk,  $cit, $po), 'pub key enc failed');
			$ret[$r['_id']][dao_plain::skf] = base64_encode($cit); unset($cit);
			$ret[$r['_id']][dao_plain::gtcollid] = $this->oas[self::cooBas]['_id'];
			continue;
		}
		
		return $ret;
	}
	
	private function decFromPriv(string $cit) {
		if (!$cit) return;
		if (!($priv = kwifs($this, 'oas', self::cooBas, self::cooPri))) return;
		if (!($pro = openssl_pkey_get_private($priv))) return;
		if (!openssl_private_decrypt(base64_decode($cit), $pt, $pro)) return;
		$this->oas[self::cooBas][self::eknm] = $pt;

		
	}

	
	private function setKeyOb() {
		
		if (kwifs($this, 'oas', self::cooBas)) return;
		
		$tek = self::base62();
		$a = [];
		$a[self::eknm] = $tek;
		
		$ba = [self::keybitsf => self::keybits];
		$a = kwam($a, $ba);
		$pro = openssl_pkey_new($ba);
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

	public static function emailHashF(string $t) {
		for ($i=0; $i < 2806; $i++) $t = crypt($t, 'apIpUIgaIsuu5y3kqiAXVBdiTGclx2');
		return $t;
	}
	
}

