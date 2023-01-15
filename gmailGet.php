<?php

class gmailGetCl {
    
    function __construct($googc) { // Goo general client
		$this->serv  = $service = new Google_Service_Gmail($googc);
		$this->email = $service->users->getProfile('me')->emailAddress;
    }
    
    public function getEmailAddress() { return kwifs($this, 'email'); }
	
    public function checkEmail() { 
		$this->goomlo = $this->serv->users_messages->listUsersMessages('me', array('maxResults' => 10, 'labelIds' => 'UNREAD' ));   }
    
    public function getText() {
		$ro = kwifs($this, 'goomlo');
		if (!$ro) return 'GooMR error';
		$mo = $ro->getMessages();
		if (is_array($mo)) return count($mo);
		if (!$mo && $ro->getResultSizeEstimate() === 0) return 0;
	}
   
} // end class