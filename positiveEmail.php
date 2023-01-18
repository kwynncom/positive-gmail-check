<?php

require_once('OAuthGoo.php');
require_once('isUserCookie.php');
require_once('enc.php');
require_once('gmailGet.php');
require_once('usageLimit/usageLimit.php');

class positiveEmailCl extends GooOAuthWrapper {
	
	const peoaa = [
					'sfb'  => '/var/kwynn/gooauth/positive_email_secret', 
					'sfx' => '.json',
					'scope' => Google_Service_Gmail::GMAIL_METADATA, 
					'upath' => '/t/7/12/email/',
					'osfx' => '_live_active_output',
					'redbase' => 'receiveAuthCode.php',
				];
	
	const srvFile = 'server.php';
	const srvPath = self::peoaa['upath'] . '/' . self::srvFile;
	
	protected OAuthLog		$log;
	private   usageLimit	$ulo;

	public function __construct(usageLimit $ulo = null) { 
		
		$this->ulo = $ulo ? $ulo : new usageLimit();
		$this->log = new OAuthLog();
		$this->setDao();
		parent::__construct(self::peoaa);
		if (isset($this->oauthurl)) return;
		$this->setEmailSpecificStuff();

	}
	
	protected function doUponOAInitCode() { $this->ulo->putUse('oauth');	}

	
	private function setEmailSpecificStuff() {
		$this->gmc = new gmailGetCl($this->getGoogleClient());
		$this->emailAddress = $this->gmc->getEmailAddress();
		$this->emailHash    = $this->dao->getEmailHash($this->emailAddress);		
	}
	
	public function getLog() { return $this->log->get(); }
	
	public function getEmailHash() { return $this->emailHash;	}
	
	public function getLimitsO() { return $this->ulo; }
	
	public function checkEmail() {
		try { 
			$this->ulo->putUse('checked');
			$this->gmc->checkEmail();
			$this->ulo->setEmail ($this->emailHash);
			$this->regUsage($this->emailHash);
			$this->log->log(GooOAUTHWrapper::accessTokenTimeRemainingS($this->client->getAccessToken()), 'atsec');
		} 
		catch(Exception $exv) {
			if ($exv->getCode() === 401) return $this->doOAuth();
			else throw $exv;
		}
	}
	
	public function getText() { return $this->gmc->getText();	}
	
	public function regUsage(string $em, array $mtin = []) {
		isucookie::set();
		if ($mtin) $mt = $mtin;
		else	   $mt = $this->client->getAccessToken();
		$this->dao->upsertToken($mt, $em);
	}

	public function doUponAuth() {
		isucookie::set();
		$mt = $this->client->getAccessToken();
		$this->dao->upsertToken($mt); // this versus regUsage seems redundant but must be done this way
		$this->setEmailSpecificStuff();
		$this->regUsage($this->emailHash, $mt);
		header('Location: ' .  $this->urlbase . iaacl::getURLQ());
		exit(0);
	}
	
	public function revokeToken() {
		$this->ulo->putUse('revoke');
		$rr = parent::revokeToken();
		$this->deleteToken();
		return;
    }

	public function revokeAccess() {
		$o = $this;
		$o->revokeToken();
		$o->deleteToken();
		dao::expireCookies();
		return $o->urlbase;
	}
	
	public	  function deleteToken()   { $this->dao->deleteTokenKwDB();	}
	protected function getSavedToken() { return $this->dao->getTokenDB();	}
	private   function setDao()		   { $this->dao = new dao($this->log);		}
}
