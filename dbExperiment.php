<?php

require_once('/opt/kwynn/kwutils.php');

class testKeys extends dao_generic_3 {
	public function __construct() {
		parent::__construct('test');
		$this->creTabs('test');
		$this->test10();
	}
	
	private function test10() {
		$dat = ['key' => 1, 'blah' => ['rt' => 'A', 'at' => 'B']];
		$this->tcoll->insert($dat);
	}
}

new testKeys();
