<?php

require_once('/opt/kwynn/kwutils.php');
require_once('dao.php'); // needs its own require because it's being called directly
require_once('testMode.php');
require_once('serverSwitch.php'); // might need this
require_once('GoogleClient.php');
require_once('usageLimit/usageLimit.php');

if ($code = GoogleClientWrapper::getOAuthCode()) new gmailClient($code);

class gmailClient {
    
    private $refs = false;
    
    function __construct($code = false) {
	
	if (getTestMode()) {
	    $this->testMode = true;
	    return;
	}

	$this->dao = new dao();
	
	$client = new GoogleClientWrapper();
	$client->addScope(Google_Service_Gmail::GMAIL_METADATA);
	$token = $client->setToken(); // must be called from outside because after scope
	$this->client = $client;
	
	if (!$token || $code) $this->cannotCheck = true;
    }
    
    public function getRefCount() { return count($this->refs); } 
    
    public function getEmail() { return isset($this->email) ? $this->email : null; }
    
    public function check() {
	
	if (isset($this->testMode) && $this->testMode) return 'test - no check';
	if (isset($this->cannotCheck)) return 'oauth';
	
	$service = new Google_Service_Gmail($this->client->getGoogleClient());

	try {	
	    $this->email = $service->users->getProfile('me')->emailAddress;
	    $this->dao->updateEmail($this->email);
	    $messagesResponse = $service->users_messages->listUsersMessages('me', array('maxResults' => 10, 'labelIds' => 'UNREAD' ));
	} catch(Exception $exv) {
	    if ($exv->getCode() === 401) {
		$this->client->doOAuth();
		return;
	    } else throw $exv;
	}

	$msgrefs = $messagesResponse->getMessages();
	
	$this->refs = $msgrefs;
	
	return 'checked';
    }
    
    public function getText() {
	
	if (getTestMode() === 'ret_1') return '-1';
	
	$msgRefs = isset($this->refs) ? $this->refs : '';
	if (!$msgRefs && !is_array($msgRefs)) return 'msg error';
	else if (is_array($msgRefs)) return count($msgRefs);
	else return 'nock';
    }
    
    public function getOAuthURL() {
	 if (!isset($this->client)) return false;
	 header('Access-Control-Allow-Origin:  https://accounts.google.com');
	 $url = $this->client->getOAuthURL();
	 return $url;
    }
    
    public function revokeToken() {
	$x = 2;
	$this->dao->deleteToken();
	$res = $this->client->revokeToken();
	$y = 3;
    }
    
} // end class
