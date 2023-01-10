<?php

require_once('dao.php');
require_once('isUserCookie.php');

class GoogleClientWrapper {
	
	// begin from configForGooGen
	public function getScope() { return $this->thea['scope'];	}
	
    public function getSecretFilePath() { return $this->specSettings['goopath']; }

    private function setSpecificConfig() {
		$this->urlbase = $oarurl = 'https://' . $_SERVER['SERVER_NAME'] .  $this->thea['upath'];
		$fname = $this->thea['sfb'] . $this->thea['sfx'];
		$set = [ 'goopath'   => $fname, 'oarurl'    => $oarurl];
		kwas(is_readable($set['goopath']) && strlen(file_get_contents($set['goopath'])) > 30, 'cannot read secret file - the input-only version');
		$this->specSettings = $set;
    }
	
	public function getBaseURL    () { return $this->urlbase; }

	
	public function getRedirectURL() { 
		return $this->getBaseURL() . $this->thea['redbase']; 
		
	}	
	
	// end configForGooGen

	public function saveToken($din) {
		$a = $this->thea;
		$f = $a['sfb'] . $a['osfx'] . $a['sfx'];
		file_put_contents($f, json_encode($din, JSON_PRETTY_PRINT));		
		
		return;
	}
		function __destruct() {
		if (!isset($this->client)) return;
		$this->saveToken($this->client->getAccessToken());
	}

    
	// private function getRedirectURL() { 		return $this->ssw->getRedirectURL();	}

	
	// public function getScope() { return $this->ssw->getScope(); }
	
    function __construct($cdin) {
		
		$this->thea = $cdin;
		
		$this->setSpecificConfig();
	
		// $this->ssw = $cono;
		$path = $this->getSecretFilePath();

		$client = new Google_Client();
		$client->setAuthConfig($path);
		$client->setAccessType('offline');
		$client->setIncludeGrantedScopes(true);
		$this->client = $client;

		$this->processAuthCode(); 
	
		$this->setDao();
    }
    
    public function setScopes($scope) { $this->client->setScopes($scope);  }
    
    public function getGoogleClient() { return $this->client; }
    
    public function revokeToken() {
		$res = $this->client->revokeToken(); // returns boolean true on success
    }
    
    public function setToken() {
		
		$accessToken = $this->dao->getToken();
		if (!$accessToken) return $this->doOAuth();
		else {
				$this->client->setAccessToken($accessToken);
				if ($this->client->isAccessTokenExpired()) {
				try {
					$creds = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
				} catch(Exception $e) {
					return $this->doOAuth();
				}
				if (isset($creds['error'])) return $this->doOAuth();
			}
		}
	
		return true;
    }
    
    private static function getOAuthCode() {
		if (!isset($_REQUEST['code'])) return false;
		return     $_REQUEST['code'];
    }
    
	private function setDao() {
		if (!isset($this->dao)) $this->dao = new dao();		
	}
	
    private function processAuthCode() {
		
		if (!($code = $this->getOAuthCode())) return false;
        
		$res = $this->client->authenticate($code);
		$this->setDao();
		$this->dao->deleteToken();
		if (kwifs($res, 'error')) kwas(false, json_encode($res));
        $accessToken = $this->client->getAccessToken();
		$this->setDao();
		
		$this->dao->putToken($accessToken);
		
		$this->doUponAuth();

		exit(0); // probably a good idea
    }
    
    public function doOAuth() {
		$this->dao->deleteToken();
		$this->processAuthCode();
		$this->client->setRedirectUri($this->getRedirectURL());
		$auth_url = $this->client->createAuthUrl();
		$this->oauthurl = $auth_url;
    }
  
	public function forceGetOAuthURL() { return $this->client->createAuthUrl();	}
	
    public function getOAuthURL() {
		if (isset  ($this->oauthurl)) 
			 return $this->oauthurl;
		else return false;
    }

}