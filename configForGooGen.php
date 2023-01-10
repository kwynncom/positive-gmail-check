<?php


require_once('configInterface.php');
require_once('GoogleClient.php');

class configForGooGen /* implements GooOAuthAppConfigI */ {
  	
	public function getGooClientO() { }
	
	public function doUponAuth() {}

	
	public function getScope() { return $this->thea['scope'];	}
	
    public function getSecretFilePath() { return $this->settings['goopath']; }
    
    private function set() {
		$this->urlbase = $oarurl = 'https://' . $_SERVER['SERVER_NAME'] .  $this->thea['upath'];
		$fname = $this->thea['sfb'] . $this->thea['sfx'];
		$set = [ 'goopath'   => $fname, 'oarurl'    => $oarurl];
		kwas(is_readable($set['goopath']) && strlen(file_get_contents($set['goopath'])) > 30, 'cannot read secret file - the input-only version');
		$this->settings = $set;
    }
    
	function __construct($ain) {
		$this->thea = $ain;
		$this->set();
		$this->tokenInit();
    }
	
	public function getBaseURL    () { return $this->urlbase; }
	
	private function tokenInit() {
		// $wro = new GoogleClientWrapper();
		
	}
	
	

	
	public function getRedirectURL() { 
		return $this->getBaseURL() . $this->thea['redbase']; 
		
	}

 
}