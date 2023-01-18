<?php

require_once('/opt/kwynn/kwutils.php');
require_once('log.php');

class GooOAUTHWrapper {
	
	const err_secret_file_access = 1848; /* arbitrary, relatively rare code */
	const tnms = ['refresh_token', 'access_token'];
	
	protected readonly string $urlbase;
	
    private function setSpecificConfig() {
		if (!($sn = kwifs($_SERVER, 'SERVER_NAME'))) $sn = 'example.com';
		$this->urlbase = $oarurl = 'https://' .		 $sn .  $this->thea['upath'];
		$fname = $this->thea['sfb'] . $this->thea['sfx'];
		$set = [ 'goopath'   => $fname, 'oarurl'    => $oarurl];
		kwas(is_readable($set['goopath']) && strlen(file_get_contents($set['goopath'])) > 30, 
				'error - cannot read secret file - the input-only version', self::err_secret_file_access);
		$this->specSettings = $set;
    }

	public function saveToken($din) {
		
		if (false) { 
			$a = $this->thea;
			$f = $a['sfb'] . $a['osfx'] . $a['sfx'];
			file_put_contents($f, json_encode($din, JSON_PRETTY_PRINT));	
		}
	}
	
	function __destruct() {
		if (!isset($this->client)) return;
		// **** $this->saveToken($this->client->getAccessToken()); ****
	}

	function __construct($cdin) {
		
		$this->logs = '';
		
		$this->thea = $cdin;
		$this->setSpecificConfig();
		$client = new Google_Client();
		$client->setAuthConfig( $this->specSettings['goopath']);
		$client->setAccessType('offline');
		$client->setIncludeGrantedScopes(true);
		$this->client = $client;

		$this->processAuthCode(); 
		$this->client->setScopes($this->thea['scope']);
		$this->setToken();
    }
    
    public function getGoogleClient() { return $this->client; }
    
    public function revokeToken() {	
		return $this->client->revokeToken(); /* returns boolean true on success */    }
    
	protected function getSavedToken() { return false; }
	
	private function logdbr($t) {
		if		(isset($t['refresh_token'])) $this->log('rt fr db');
		else if (isset($t['access_token' ])) $this->log('at fr db');
	}
	
    private function setToken() {
		
		$accessToken = $this->getSavedToken();
		if (!$accessToken) {
			$this->log('no tok db');
			return $this->doOAuth();
		}
		else {
				$this->logdbr($accessToken);
				$this->client->setAccessToken($accessToken);
				if ($this->client->isAccessTokenExpired()) {
				try { 
					$this->log('refresh attempt');
					$creds = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
					$this->log('refresh OK');
				} catch(Exception $e)	{ 
					$this->log('refresh exception');
					return $this->doOAuth(); 
				}
				if (isset($creds['error'])) {
					$this->log('refresh ob err');
					return $this->doOAuth();
				}
				
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
    
	private function deleteToken() {}
	
	private function log($sin) {
		$this->log->log($sin);
	}

    private function processAuthCode() {
		
		if (!($code = $this->getOAuthCode())) return false;
		
		$this->doUponOAInitCode();
		
		$this->log('nonce code');
        
		$res = $this->client->authenticate($code);
		$this->deleteToken();
		if (kwifs($res, 'error')) kwas(false, json_encode($res));
		$this->doUponAuth();

		exit(0); // maybe a good idea
    }
    
    public function doOAuth() {
		$this->deleteToken();
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
