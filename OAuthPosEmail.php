<?php

require_once('/opt/kwynn/crackObject.php');

require_once('OAuthGoo.php');
require_once('util.php');
require_once('isUserCookie.php');
require_once('enc.php');
require_once('gmailClient.php');

class posEmailOAuth extends GooOAuthWrapper {

	const peoaa = [
							'sfb'  => '/var/kwynn/gooauth/positive_email_secret', 
							'sfx' => '.json',
							'scope' => Google_Service_Gmail::GMAIL_METADATA, 
							'upath' => '/t/7/12/email/',
							'osfx' => '_live_active_output',
							'redbase' => 'receiveAuthCode.php',
							];
	
	protected $log;
	
	public function __construct() { 
		$this->log = new OAuthLog();
		$this->setDao();
		parent::__construct(self::peoaa);
	}
	
	public function getLog() { return $this->log->get(); }
	
	public function regUsage($em, $mtin = false) {
		if ($mtin) $mt = $mtin;
		else	   $mt = $this->getMemTok();
		$this->dao->upsertToken($mt, $em);
	}

	private function getMemTok() {
		$a = crackObject::crackGoo($this->client);
		$f = kwifs($a, 'Google_Clienttoken');
		return $f;
	}
	
	public function doUponAuth() {
		isucookie::set();
		$mt = $this->getMemTok();
		$this->dao->upsertToken($mt);
		$em = gmailClient::getEmailStatic($this);
		$this->regUsage($em, $mt);
		header('Location: ' .  $this->urlbase . iaacl::getURLQ());
		exit(0);
	}
	
	public function revokeToken() {
		$this->deleteToken();
		parent::revokeToken();
    }
	
	private	  function deleteToken()   { $this->dao->deleteTokenKwDB();	}
	protected function getSavedToken() { return $this->dao->getToken();	}
	private   function setDao()		   { $this->dao = new dao($this->log);		}
	
}
