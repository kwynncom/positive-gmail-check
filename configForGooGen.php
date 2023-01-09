<?php


require_once('configInterface.php');

class configForGooGen implements GooOAuthAppConfigI {
  	
	public function uponAuth() {}
	
	public function getRedirectURL() {
		kwas(false, 'redirect URL undefined');
	}

	
	public function fileToken($din) {
		$a = $this->thea;
		$f = $a['sfb'] . $a['osfx'] . $a['sfx'];
		file_put_contents($f, json_encode($din, JSON_PRETTY_PRINT));		
		
		return;
	}
	
	public function getScope() { return $this->thea['scope'];	}
	
	public static function getThisAPath() { $a = $this->thea;		return 'https://' . $_SERVER['SERVER_NAME'] .  $a['upath'] . $a['asfx'];	}
 
    public function getSecretFilePath() { return $this->settings['goopath']; }
    
    private function set() {
		$this->urlbase = $oarurl = 'https://' . $_SERVER['SERVER_NAME'] .  $this->thea['upath'];
		$fname = $this->thea['sfb'] . $this->thea['sfx'];
		$set = [ 'goopath'   => $fname, 'oarurl'    => $oarurl];
		kwas(is_readable($set['goopath']) && strlen(file_get_contents($set['goopath'])) > 30, 'cannot read secret file - the input-only version');
		$this->settings = $set;
    }
    
    public function getBaseURL    () { return $this->urlbase; }
	
	public function getURLSfx() { return ''; }

	function __construct($ain) {
		$this->thea = $ain;
		$this->set();
    }
 
}