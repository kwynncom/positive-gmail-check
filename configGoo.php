<?php

require_once('util.php');
require_once('dao.php');
require_once('FileToMongo/FileToMongo_general.php');
require_once('sortURLs.php');

class configGooOAUTH2 {
    
	const gooApps = ['positiveEmail' => 
						[ 
							'sfb'  => '/var/kwynn/gooauth/positive_email_secret', 
							'sfx' => '.json',
							'scope' => Google_Service_Gmail::GMAIL_METADATA, 
							'upath' => '/t/7/12/email/',
						   'asfx' => 'intro.php',
							'osfx' => '_live_active_output'
							]
			];
	
    const redExt = 'receiveAuthCode.php';
	
	private $oapp;
	
	public function fileToken($din) {
		$a = self::gooApps[$this->oapp];
		$f = $a['sfb'] . $a['osfx'] . $a['sfx'];
		file_put_contents($f, json_encode($din, JSON_PRETTY_PRINT));		
		
		return;
	}
	
	public function writeAndReturn($ob) {
		$a = (array) $ob;
		
	}
	
	private function popApp() {  // inefficient but I just don't care right now.  
		$a = self::gooApps;
		foreach($a as $k => $r) { 
			if (strpos($_SERVER['REQUEST_URI'], $r['upath']) !== false) { $this->oapp = $k; return; } // not sure proper $_SERVER property
		}
		
	}
	
	public function getScope() {
		return self::gooApps[$this->oapp]['scope'];
	}
	
	public static function getThisAPath() { 
		$a = self::gooApps[$this->oapp];
		return 'https://' . $_SERVER['SERVER_NAME'] .  $a['upath'] . $a['asfx'];
		
	}
 
    public function getPath() { return $this->settings['goopath']; }
    
    private function set() {
		$this->urlbase = $oarurl = 'https://' . $_SERVER['SERVER_NAME'] .  self::gooApps[$this->oapp]['upath'];
		$fname = self::gooApps[$this->oapp]['sfb'] . self::gooApps[$this->oapp]['sfx'];
		$set = [ 'goopath'   => $fname, 'oarurl'    => $oarurl];
		kwas(is_readable($set['goopath']) && strlen(file_get_contents($set['goopath'])) > 30, 'cannot read secret file - the input-only version');
		$this->settings = $set;
    }
    
	public function sortURLs($ain) {
		
		return sortURLs($ain, $this->getRedirectURL());
	}
	
    public function getBaseURL    () { return $this->urlbase; }
    public function getRedirectURL() { 
		return $this->getBaseURL() . self::redExt; 
		
	}
    
    function __construct(string $gooApp) {
		if ($gooApp) $this->oapp = $gooApp;
		else         $this->popApp();
		kwas(self::gooApps[$this->oapp], 'uknown Google app - Kwynn - 2317');
		$this->set();
    }
 
}