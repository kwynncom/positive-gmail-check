<?php

require_once('/opt/kwynn/crackObject.php');

require_once('OAuthGoo.php');
require_once('util.php');
require_once('isUserCookie.php');
require_once('enc.php');
require_once('gmailClient.php');

class posEmailOAuth extends GooOAuthWrapper {
	
	public function revokeAccess() {
		$o = $this;
		$o->revokeToken();
		$o->deleteToken();
		dao::expireCookies();
		return $o->urlbase;
	}

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
		$this->gmc = new gmailClient($this->getGoogleClient());
		$this->emailAddress = $this->gmc->getEmailAddress();
		$this->emailHash    = $this->dao->getEmailHash($this->emailAddress);
	}
	
	public function getLog() { return $this->log->get(); }
	
	public function getEmailHash() { return $this->emailHash;	}
	
	public function checkEmail() {
		try { 
			$this->gmc->checkEmail();
			$this->regUsage($this->emailHash);
		} 
		catch(Exception $exv) {
			if ($exv->getCode() === 401) return $this->doOAuth();
			else throw $exv;
		}
	}
	
	public function getText() {
		return $this->gmc->getText();
	}
	
	public function regUsage($em, $mtin = false) {
		isucookie::set();
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
		$rr = parent::revokeToken();
		$this->deleteToken();
		return;
    }
	
	public	  function deleteToken()   { $this->dao->deleteTokenKwDB();	}
	protected function getSavedToken() { return $this->dao->getTokenDB();	}
	private   function setDao()		   { $this->dao = new dao($this->log);		}
	
}
