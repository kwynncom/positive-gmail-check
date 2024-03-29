<?php

require_once('/opt/kwynn/kwutils.php');
require_once('log.php');

class GooOAUTHWrapper {
	
	// for 8.2 BEGIN
	public $oauthurl; // Kwynn public for now PHP 8.2
	// should probably be readonly?? 2023/12
	
	protected $client; // Kwynn 8.2
	
	private $specSettings; // Kwynn 8.2
	
	private $initTokenSet; // same
	
	private $doRevoke;
	
	private $thea;
	
	// 8.2 end
	
	const audom = 'accounts.google.com';
	
	const err_secret_file_access = 1848; /* arbitrary, relatively rare code */
	const tnms = ['refresh_token', 'access_token'];
	
	protected readonly string $urlbase;
	
	public readonly bool $okRevoke;
	
	private readonly string $thereu;

	// PUBLIC FUNCTIONS BEGIN *******
    public function getOAuthURL() : string { // call after instantiation to see if you need to redirect to Goo for OAUTH
		if (isset  ($this->oauthurl)) 
			 return $this->oauthurl;
		else return '';
    }

	public function revokeToken() : bool {	return $this->client->revokeToken();  } // returns revokaction success or failure (based on HTTP 200 or not)
	
	public static function accessTokenTimeRemainingS(array | null | false $tin) : int { 
		if (!$tin || !is_array($tin)) return -1;
		return $tin['created'] + $tin['expires_in'] - time(); 	
	}
	
	public static function accessTokenHasTimeRemaining(array | null | false $tin) : bool {
	   if (!$tin || !is_array($tin)) return false;
	   $d = self::accessTokenTimeRemainingS($tin);
	   return $d > 0;
	}
	// PUBLIC END ****
	
	private static function vaft(array | null | false $tin) { // validate array for time; is this needed?
	   if (!$tin || !is_array($tin)) return false;
	   $fs = ['access_token', 'created', 'expires_in'];
	   foreach($fs as $f) if (!isset($tin[$f]))  return false;
	   return true;
	}
	
    private function setSpecificConfig() {
		if (!($sn = kwifs($_SERVER, 'SERVER_NAME'))) $sn = 'example.com';
		$this->urlbase = $oarurl = 'https://' .		 $sn .  $this->thea['upath'];
		$fname = $this->thea['sfb'] . $this->thea['sfx'];
		$set = [ 'goopath'   => $fname, 'oarurl'    => $oarurl];
		$secf = kwifsT($set, 'goopath', ['kwiff' => 'no file set in var goopath']);
		kwas(is_readable($secf) && strlen(file_get_contents($secf)) > 30, 
				'error - cannot read secret file ' . $secf . ' - the input-only version', self::err_secret_file_access);
		$this->specSettings = $set;
		
		$ru = $this->urlbase . $this->thea['redbase'];
		$this->thereu = $ru;
		self::kwrd($this->thereu);

    }

	function __construct(array $cdin, string $reqState = '', bool $revoke = false) {
		$this->thea = $cdin;
		$this->doRevoke = $revoke;
		$this->setSpecificConfig();
		$client = new Google_Client();
		$client->setAuthConfig( $this->specSettings['goopath']);
		$client->setAccessType('offline');
		$client->setIncludeGrantedScopes(true);
		$client->setRedirectUri($this->thereu);
		$this->client = $client;
		if ($reqState) $this->client->setState($reqState);
		

		if ($this->processAuthCode()) return; // if success, continuing results in a loop
		$this->client->setScopes($this->thea['scope']);
		$str = $this->setToken();
		return;
    }
    
    protected function getGoogleClient() { return $this->client; }
    
	protected function getSavedToken() { return false; } // create a child function!
	
	private function logdbr($t) {
		if		(isset($t['refresh_token'])) $this->log('rt fr db');
		else if (isset($t['access_token' ])) $this->log('at fr db');
	}
	
    protected function setToken() { // needs to be protected because children need to make sure it's a refreshed token
		
		$accessToken = $this->getSavedToken();
		if (!$accessToken) {
			$this->log('no tok db');
			return $this->doOAuth();
		}
		else {
				$this->logdbr($accessToken);
				$this->client->setAccessToken($accessToken);
				
				if ($this->doRevoke) {
					$this->okRevoke = $this->client->revokeToken();
					return $this->doOAuth();
				}
				
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
    
    private static function getOAuthCode() {
		if (!isset($_REQUEST['code'])) return false;
		return     $_REQUEST['code'];
    }
    
	private function deleteToken() {}
	
	private function log($sin) {
		static $lfinit = false;
		static $lf = false;
		
		if (!$lfinit) $lf = kwifs($this, 'log', 'log');
		
		if ($lf) $lf($sin);
	}

    private function processAuthCode() {
		
		if (!($code = $this->getOAuthCode())) return false;
		
		if (method_exists($this, 'doUponOAInitCode')) $this->doUponOAInitCode();
		
		$this->log('nonce code');
        
		$res = $this->client->authenticate($code);
		$this->deleteToken();
		if (kwifs($res, 'error')) kwas(false, json_encode($res));
		$this->receiveRefreshToken($this->client->getAccessToken());

		return true; // don't need an exit() but do need to be careful
    }
    
    protected function doOAuth() {
		$this->deleteToken();
		$this->processAuthCode();
		
		if (!iscli()) header('Access-Control-Allow-Origin: ' . self::audom);
		
		// $ru = $this->urlbase . $this->thea['redbase'];
		// self::kwrd($ru);
		// $this->client->setRedirectUri($ru);
		$auth_url = $this->client->createAuthUrl();
		$this->oauthurl = $auth_url;
    }
	
	private static function kwrd($d) {
		$s  = '';
		$s .= 'redirect URL' . "\n";
		$s .= date('r')  . "\n";
		$s .= print_r($d, true) . "\n";
		$s .= '*********' . "\n";
		file_put_contents('/tmp/reurl.txt', $s, FILE_APPEND);
	}
}
