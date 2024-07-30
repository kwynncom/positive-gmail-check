<?php

require_once('/opt/kwynn/kwutils.php');
require_once('OAuthPosEmail.php');
require_once('usageLimit/usageLimit.php');
require_once('isUserCookie.php');

class gmailClient {
    
    private $refs = false;
    
    function __construct($igooaw = null) {

		isucookie::set();

		if (!$igooaw) $gooaw = new posEmailOAuth(); // Goo OAuth wrapper
		else $gooaw = $igooaw;
		$this->gooaw = $gooaw;
		if (!$igooaw && !$this->gooaw->initTokenStatus()) $this->cannotCheck = true;
		else $this->setUpService();
    }
	
	public static function getEmailStatic($gooaw) {
		$o = new self($gooaw);
		return $o->getEmail();
	}
    
    public function getRefCount() { return count($this->refs); } 
    
    public function getEmail() { return kwifs($this, 'email'); }
    
	private function setUpService() {
		$this->serv  = $service = new Google_Service_Gmail($this->gooaw->getGoogleClient());
		$this->email = $service->users->getProfile('me')->emailAddress;
	}
	
    public function checkEmail() {
	
		if (isset($this->cannotCheck)) return 'oauth';
	
		try { 
			$msgso = $this->serv->users_messages->listUsersMessages('me', array('maxResults' => 10, 'labelIds' => 'UNREAD' )); 
			$this->gooaw->regUsage($this->email);
		} 
		catch(Exception $exv) {
			if ($exv->getCode() === 401) {
			$this->gooaw->doOAuth();
			return;
			} else throw $exv;
		}

		$msgrefs = $msgso->getMessages();

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
