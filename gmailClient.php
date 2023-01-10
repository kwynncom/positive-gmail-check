<?php

require_once('/opt/kwynn/kwutils.php');
require_once('enc.php');

require_once('OAuthPosEmail.php');
// require_once('GoogleClient.php');
require_once('usageLimit/usageLimit.php');
require_once('isUserCookie.php');

class gmailClient {
    
    private $refs = false;
    
    function __construct() {

		isucookie::set();

		$this->dao = new dao();

		$client = new posEmailOAuth();
		$this->client = $client;
		if (!$this->client->initTokenStatus()) $this->cannotCheck = true;
    }
    
    public function getRefCount() { return count($this->refs); } 
    
    public function getEmail() { return isset($this->email) ? $this->email : null; }
    
    public function check() {
	
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
	
		$msgRefs = isset($this->refs) ? $this->refs : '';
		if (!$msgRefs && !is_array($msgRefs)) return 'msg error';
		else if (is_array($msgRefs)) return count($msgRefs);
		else return 'nock';
    }
   
} // end class
