<?php

require_once('configGoo.php');
require_once('dao.php');
require_once('isUserCookie.php');

class GoogleClientWrapper {
	
	const redExt = 'receiveAuthCode.php';
	
	function __destruct() {
		if (!isset($this->client)) return;
		$this->ssw->fileToken($this->client->getAccessToken());
	}
    
	public static function fromRedirectURL() {
		new self();
	}
	
	private function getRedirectURL() { 
		return $this->ssw->getBaseURL() . self::redExt; 
		
	}
	
	public function getScope() { return $this->ssw->getScope(); }
	
    function __construct(string $gooApp = '') {
	
		$this->ssw = new configGooOAUTH2($gooApp);
		$path = $this->ssw->getSecretFilePath();

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

		isucookie::set();

		header('Location: ' . $this->ssw->getBaseURL() . iaacl::getURLQ());
		exit(0);
    }
    
    public function doOAuth() {
	$this->dao->deleteToken();
	$this->processAuthCode();
	$this->client->setRedirectUri($this->getRedirectURL());
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