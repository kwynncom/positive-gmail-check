<?php

class gmailGetCl {
	
	const emailAddressScope = Google_Service_Gmail::GMAIL_METADATA; // GMail send may not work
	public readonly string $emailAddressFromGmailClient;
	
    private function __construct($googc, $setEmail = false) { // Goo general client
		$this->goomlo = false;
		$this->serv  = $service = new Google_Service_Gmail($googc);
		if ($setEmail) $this->emailAddressFromGmailClient = $this->serv->users->getProfile('me')->emailAddress;
		
	}
	
	public static function getEmailAddress($goocl) : string {
		$o = new self($goocl, true);
		return $o->emailAddressFromGmailClient;
	}
	
	private function checkEmail() { 
		$this->goomlo = $this->serv->users_messages->listUsersMessages('me', array('maxResults' => 10, 'labelIds' => 'UNREAD' ));   }
		
	public function getGoomlo() { 
		$this->checkEmail();
		return $this->goomlo; 
	}
    
    public static function getCountText($goocli) {
		$so = new self($goocli); unset($goocli);
		$ro = $so->getGoomlo();
		if (!$ro) return 'GooMR error';
		$mo = $ro->getMessages();
		if (is_array($mo)) return count($mo);
		if (!$mo && $ro->getResultSizeEstimate() === 0) return 0;
	}
   
} // end class
