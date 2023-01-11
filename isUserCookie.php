<?php

require_once('configDB.php');

class isucookie {
	
	const cnm = 'pemck_user';
	const expiresS = 10 * DAY_S;
	
	static function   set() { kwscookie(self::cnm, true, self::getOpts()); }
	static function unset() { setcookie(self::cnm, false, time() - 100000); }
	static function is()  {	return kwifs($_COOKIE, self::cnm);	}
	
	public static function getOpts() { 
		$p = dirname($_SERVER['PHP_SELF']);
		return ['path' => $p, 'expires' => time() + self::expiresS];
	}
}

// Lets the server.php page know that the user has already seen the intro page and wants to OAUTH.  Otherwise, the server would keep cylcing back 
// to the intro page.  I am checking the URL with isrv().  I doubted this a moment ago.  Also, the thinking with that was that I didn't want to 
// start sessions / cookies until they commit to using (by giving auth).
class iaacl extends dao_generic_3 implements qemconfig {
	
	const timeoutS = 30;
	const cleanDBAfterS = self::timeoutS * 1.5;
	const codeLen  = 6;
	const codeURLQName = 'c';
	const Uoff = 1650866251;

	private function __construct() {
		parent::__construct(self::dbname);
		$this->creTabs('authHandoff');
		$this->cleanDB();
	}
	
	private function cleanDB() { $this->acoll->deleteMany(['U' => ['$lt' => time() - self::cleanDBAfterS]]); }
	
	public static function getURLQ() { 
		$o = new self();
		$uq = $o->getURLQI();
		$t = '';
		$t .= '?' . $uq;
		if (0 && ispkwd()) $t .= '&XDEBUG_SESSION_START=netbeans-xdebug';
		return $t;
	}
	
	public function getURLQI() {
		$dat = [];
		$dat['_id' ] = dao_generic_3::get_oids(false);
		$now = time();
		$dat['code'] = $code = $this->getCode();
		$dat['U'  ] = $now;
		$dat['r'  ] = date('r', $now);
		$dat['used'] = false;
		$this->acoll->insertOne($dat);
		return $this->getURL20I($now, $code);
	}
	
	private function getCode() { return base62(self::codeLen);	}
	
	public function getURL20I($ts, $code) {
		$t  = '';
		$t .= 'U=' . dechex($ts - self::Uoff);
		$t .= '&' . self::codeURLQName . '=' . $code;
		return $t;
	}
	
	private function getValidQ() {
		$hex = isrv('U'); 
		$ts = hexdec($hex); unset($hex); kwas($ts && is_numeric($ts), 'bad URL query iaacl pemck 0137');
		$tsi = intval($ts); unset($ts);
		$tsi += self::Uoff;
		kwas((abs($tsi - time()) <= self::timeoutS), 'bad URL query iaacl isUCook pemck');
		$code = isrv(self::codeURLQName);
		kwas(preg_match('/^[A-Za-z0-9]+$/', $code), 'invalid code format pemck iaacl 0113');
		kwas(strlen(trim($code)) === self::codeLen, 'invalid code format 20 pemck');
		return ['code' => $code, 'U' => $tsi];
		
	}
	
	public function isiaaI() {
		try {
			$uq = $q = $this->getValidQ();
			$q['used'] = false;
			$r = $this->acoll->findOne($q);
			kwas($r && isset($r['U']), 'URL Q not found iaacl pemck');
			$this->acoll->upsert($uq, ['used' => true]);
			$now = time();
			$d = $now - ($r['U'] + self::timeoutS);
			kwas(is_numeric($d), 'not numeric isiaaI pemck 0052');
			return $d <= 0;	
		} catch(Exception $ex) { return false; }
	
	}
	
	public static function isiaa() {
		$o = new self();
		return $o->isiaaI();
	}
	
	
}
