<?php

require_once(__DIR__ . '/positiveEmailDefaults.php');

class rdToken implements positiveEmailDefaults {
	public static function put($t) {
		if (0) { $if = self::peoaa['sfb'] . self::peoaa['sfx'];
		$tin = file_get_contents($if);
		$a = json_decode($tin, true);
		$aa = kwam($a, $t);
		}
		$fo = self::peoaa['sfb'] . self::peoaa['osfx'] . self::peoaa['sfx'];
		$jo = json_encode($t);
		$res = file_put_contents($fo, $jo);
		return;
	}
}
