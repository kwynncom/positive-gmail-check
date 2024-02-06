<?php

class OAuthLog {
	
	private string $logs;
	
	public function __construct() {
		$this->logs = '';
	}
	
	public function log($sin = '', $ty = '') {
		
		if ($ty === 'atsec') {
			$sec = $sin;
			$d   = sprintf('%0.2f', $sec / 60);
			$sin = 'ATm=' . $d . '.';
		}
				
		$sin = ucfirst($sin);
		if ($sin && !strpos($sin, '.')) $sin .= '. ';
		$this->logs .= $sin;
	}
	
	public function get() { return $this->logs; }
	
}
