<?php

class gmailGetCl {
	
    function __construct($googc) { // Goo general client
		$this->goomlo = false;
		$this->serv  = $service = new Google_Service_Gmail($googc);
		$this->checkEmail();
    }
  	
    private function checkEmail() { 
		$this->goomlo = $this->serv->users_messages->listUsersMessages('me', array('maxResults' => 10, 'labelIds' => 'UNREAD' ));   }
		
	public function getGoomlo() { return $this->goomlo; }
    
    public static function getCountText($goocli) {
		$so = new self($goocli); unset($goocli);
		$ro = $so->getGoomlo();
		if (!$ro) return 'GooMR error';
		$mo = $ro->getMessages();
		if (is_array($mo)) return count($mo);
		if (!$mo && $ro->getResultSizeEstimate() === 0) return 0;
	}
   
} // end class
