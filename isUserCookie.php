<?php

require_once('/opt/kwynn/kwutils.php');

class isucookie {
	
	const cnm = 'pemck_user';
	const cex = 86400 * 4;
	
	static function   set() { 
		$lt = time() + self::cex;
		$o = ['expires' => $lt, 'secure' => true, 'httponly' => true, 'samesite' => 'Strict'];
		setcookie(self::cnm, true, $o); 
		
	}
	static function unset() { 
		setcookie(self::cnm, false, time() - 100000); 
	}
	static function is()  {	return kwifs($_COOKIE, self::cnm);	}
}