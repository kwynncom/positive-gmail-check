<?php

require_once('/opt/kwynn/kwutils.php');

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

class iaacl {
	
	const iaareq = 'pemck_iaa';
	const iaapre = '/tmp/' . self::iaareq . '_';
	const timeoutS = 30;
	
	public static function getURLQ() { 
		$fn = self::iaapre . dao_generic_3::get_oids(true);
		$now = time();
		file_put_contents($fn, $now);
		return '?' . self::iaareq . '=' . $fn . '&XDEBUG_SESSION_START=netbeans-xdebug';
	}
	
	public static function isiaa() {
		$fn = isrv(self::iaareq);
		if (!$fn) return false;
		if (!($ftr = file_get_contents($fn))) return false;
		$ft = intval(trim($ftr));
		$now = time();
		$d = $now - ($ft + self::timeoutS);
		return $d <= 0;
	}
	
	
}
