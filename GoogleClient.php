<?php

class GoogleClientWrapper {
    
    function __construct() {
	
	$this->ssw = new serverSwitch();
	$path = $this->ssw->getPath();
	
	$client = new Google_Client();
	$client->setAuthConfig($path);
	$client->setAccessType('offline');
	$client->setIncludeGrantedScopes(true);
	$this->client = $client;
	
	$this->dao = new dao();
    }
    
    public function addScope($scope) { $this->client->addScope($scope);  }
    
    public function getGoogleClient() { return $this->client; }
    
    public function revokeToken() {
	$res = $this->client->revokeToken(); // returns boolean true on success
	$x = 5;
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
    
    private function processAuthCode() {
	if (!($code = $this->getOAuthCode())) return false;
	$ar = $this->client->authenticate($code);
	file_put_contents('/tmp/qe', 'gmc-0149 ' . json_encode($ar) . date('r') . "\n" , FILE_APPEND);
	$accessToken = $this->client->getAccessToken();
	$this->dao->putToken($accessToken);

	// header('Location: ' . $this->ssw->getBaseURL() /* . '?redirLocHeader=1' */);
	exit(0);
    }
    
    public function doOAuth() {
	$this->dao->deleteToken();
	$this->processAuthCode();
	$this->client->setRedirectUri($this->ssw->getRedirectURL());
	$auth_url = $this->client->createAuthUrl();
	$this->oauthurl = $auth_url;
    }
    
    public function getOAuthURL() {
	if (isset  ($this->oauthurl)) 
	     return $this->oauthurl;
	else return false;
    }
}