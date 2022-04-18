<?php

require_once('/opt/kwynn/kwutils.php');

class isucookie {
	
	const cnm = 'pemck_user';
	const cnmimm = 'pemck_user_imm';
	const cookall = [self::cnm, self::cnmimm];
	
	static function   set() { 
		kwscookie(self::cnm, true); 
	}
	static function unset() { 
		foreach(self::cookall as $c) 
			setcookie($c, false, time() - 100000); 
	}
	static function is()  {	
		foreach(self::cookall as $c) if ($ist = kwifs($_COOKIE, $c)) return $ist;
		return false;
	}
	
	static function setimm() {
		kwscookie(self::cnmimm, true, 20);
	}
}