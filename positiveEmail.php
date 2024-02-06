<?php

require_once('OAuthGooOuter.php');
require_once('isUserCookie.php');
require_once('daoEnc.php');
require_once('usageLimit/usageLimit.php');
require_once('positiveEmailDefaults.php');

class positiveEmailCl extends GooOAuthWrapperOuter implements positiveEmailDefaults {
	
	const peoaa = positiveEmailDefaults::peoaa;
	const runnerF = __DIR__ . '/positiveEmailRun.php';
	
	protected OAuthLog	   $log;
	private   usageLimit   $ulo;
	private readonly object $dao;
	private readonly string $emailHash;

	public function __construct(usageLimit $ulo = null) { 
		
		$this->ulo = $ulo ? $ulo : new usageLimit();
		$this->log = new OAuthLog();
		$this->setDao();
		parent::__construct(self::peoaa, self::runnerF);
		if (isset($this->oauthurl)) return;
		$this->setEmailAfterAuth();
	}
	
	protected function doUponOAInitCode() { $this->ulo->putUse('oauth');	}
	
	public function getLog() { return $this->log->get(); }
	
	public function getEmailHash() : string { return kwifs($this, 'emailHash', ['kwiff' => '']);	}
	
	public function getLimitsO() { return $this->ulo; }
	
	public function getEmailCountTxt() {
		try { 
			$this->ulo->putUse('checked');
			$cntt = gmailGetCl::getCountText($this->client);
			$this->ulo->setEmail ($this->emailHash);
			$this->regUsage($this->emailHash);
			$this->log->log(GooOAUTHWrapper::accessTokenTimeRemainingS($this->client->getAccessToken()), 'atsec');
		} 
		catch(Exception $exv) {
			if ($exv->getCode() === 401) return $this->doOAuth();
			else throw $exv;
		}
		
		return $cntt;
	}
	
	public function regUsage(string $em, array $mtin = []) {
		isucookie::set();
		if ($mtin) $mt = $mtin;
		else	   $mt = $this->client->getAccessToken();
		$this->dao->upsertToken($mt, $em);
	}

	protected function setEmailAfterAuth() : string {
		$rawe = parent::setEmailAfterAuth();
		$this->emailHash    = $this->dao->getEmailHash($rawe);	
		return $rawe;
	}
	
	protected function receiveRefreshToken($atin) {
		isucookie::set();
		$this->dao->upsertToken($atin); // this versus regUsage seems redundant but must be done this way
		$this->setEmailAfterAuth();
		$this->regUsage($this->emailHash, $atin);
		header('Location: ' .  $this->urlbase . iaacl::getURLQ());
		exit(0);
	}
	
	public function revokeToken() : bool {
		$this->ulo->putUse('revoke');
		$rr = parent::revokeToken();
		$this->deleteToken();
		return $rr;
    }

	public function revokeAccess() {
		$o = $this;
		$o->revokeToken();
		$o->deleteToken();
		dao::expireCookies();
		return $o->urlbase;
	}
	
	public	  function deleteToken()		   { $this->dao->deleteTokenKwDB();	}
	protected function getSavedToken() : array { 
		if ($r = $this->dao->getTokenDB()) {
			require_once(__DIR__ . '/rdToken.php');
			rdToken::put($r);
			
			return $r;	
		}
		return [];	
		
	}
	private   function setDao()				   { $this->dao = new dao($this->log);		}
}
