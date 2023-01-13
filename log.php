<?php

class OAuthLog {
	
	public function __construct() {
		$this->logs = '';
	}
	
	public function log($sin = '') {
				
		$sin = ucfirst($sin);
		if ($sin && !strpos($sin, '.')) $sin .= '. ';
		$this->logs .= $sin;
	}
	
	public function get() { return $this->logs; }
	
}
