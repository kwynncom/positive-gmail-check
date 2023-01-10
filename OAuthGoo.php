<?php

require_once('dao.php');
require_once('isUserCookie.php');

class GooOAUTHWrapper {
	
    private function setSpecificConfig() {
		$this->urlbase = $oarurl = 'https://' . $_SERVER['SERVER_NAME'] .  $this->thea['upath'];
		$fname = $this->thea['sfb'] . $this->thea['sfx'];
		$set = [ 'goopath'   => $fname, 'oarurl'    => $oarurl];
		kwas(is_readable($set['goopath']) && strlen(file_get_contents($set['goopath'])) > 30, 'cannot read secret file - the input-only version');
		$this->specSettings = $set;
    }

	public function saveToken($din) {
		$a = $this->thea;
		$f = $a['sfb'] . $a['osfx'] . $a['sfx'];
		file_put_contents($f, json_encode($din, JSON_PRETTY_PRINT));		
	}
	
	function __destruct() {
		if (!isset($this->client)) return;
		$this->saveToken($this->client->getAccessToken());
	}

	function __construct($cdin) {
		
		$this->thea = $cdin;
		$this->setSpecificConfig();
		$client = new Google_Client();
		$client->setAuthConfig( $this->specSettings['goopath']);
		$client->setAccessType('offline');
		$client->setIncludeGrantedScopes(true);
		$this->client = $client;

		$this->processAuthCode(); 
	
		$this->setDao();
		
		$this->client->setScopes($this->thea['scope']);
		$this->setToken();
    }
    
    public function getGoogleClient() { return $this->client; }
    
    public function revokeToken() {
		$res = $this->client->revokeToken(); // returns boolean true on success
    }
    
    private function setToken() {
		
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
	
		$this->initTokenSet = true;
		return $this->initTokenSet;
    }
    
	public function initTokenStatus() {
		if (isset($this->initTokenSet)) return $this->initTokenSet;
		else return false;
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
		$this->client->setRedirectUri($this->urlbase . $this->thea['redbase']);
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