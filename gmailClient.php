<?php

require_once('OAuthPosEmail.php');

class gmailClient {
    
    function __construct($igooaw = null) {

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
    
    public function getEmail() { return kwifs($this, 'email'); }
    
	private function setUpService() {
		$this->serv  = $service = new Google_Service_Gmail($this->gooaw->getGoogleClient());
		$this->email = $service->users->getProfile('me')->emailAddress;
	}
	
    public function checkEmail() {
	
		if (isset($this->cannotCheck)) return 'oauth';
	
		try { 
			$this->goomlo = $goomlo = $this->serv->users_messages->listUsersMessages('me', array('maxResults' => 10, 'labelIds' => 'UNREAD' )); 
			$this->gooaw->regUsage($this->email);
		} 
		catch(Exception $exv) {
			if ($exv->getCode() === 401) {
			$this->gooaw->doOAuth();
			return;
			} else throw $exv;
		}

		return 'checked';
    }
    
    public function getText() {
	
		$ro = kwifs($this, 'goomlo');
		if (!$ro) return 'GooMR error';
		$mo = $ro->getMessages();
		if (is_array($mo)) return count($mo);
		if (!$mo && $ro->getResultSizeEstimate() === 0) return 0;
		return 'nock';
	}
   
} // end class
