<?php

require_once('serverSwitch.php');
require_once('dao.php');

class GoogleClientWrapper {
    
	public static function fromRedirectURL() {
		new self(true);
	}
	
    function __construct($fromRU = false) {
	
		$this->ssw = new serverSwitch();
		$path = $this->ssw->getPath();

		$client = new Google_Client();
		$client->setAuthConfig($path);
		$client->setAccessType('offline');
		$client->setIncludeGrantedScopes(true);
		$this->client = $client;

		if ($fromRU) $this->processAuthCode();
	
		$this->setDao();
    }
    
    public function addScope($scope) { $this->client->addScope($scope);  }
    
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
    
    public static function getOAuthCode() {
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

	header('Location: ' . $this->ssw->getBaseURL() /* . '?redirLocHeader=1' */);
	exit(0);
    }
    
    public function doOAuth() {
	$this->dao->deleteToken();
	$this->processAuthCode();
	$this->client->setRedirectUri($this->ssw->getRedirectURL());
	$auth_url = $this->client->createAuthUrl();
	$this->oauthurl = $auth_url;
    }
  
	public function forceGetOAuthURL() {
		return $this->client->createAuthUrl();	
		
	}
	
    public function getOAuthURL() {
	if (isset  ($this->oauthurl)) 
	     return $this->oauthurl;
	else return false;
    }

}