<?php

require_once('OAuthGoo.php');
require_once('gmailGet.php');
require_once('OAuthGooState.php');

class GooOAUTHWrapperOuter extends GooOAUTHWrapper {
	
	public readonly string $emailAddressFromGoo;
	private $gmc;
	
	public function __construct(array $config, string $requireOnceF = '') { 
		$uq = '';
		if ($requireOnceF) $uq = GooOAuthState::set($requireOnceF);
		parent::__construct($config, $uq); 
		if ($uq) $this->client->setState($uq);
		
	}
	
	protected function getSavedToken() : array			 { return [];  } /* you probably want to define a child */
	protected function receiveRefreshToken(array $token) { $this->setEmailAddress($token); }
	
	private function setEmailAddress($token) {
		$this->gmc = new gmailGetCl($this->getGoogleClient());
		$this->emailAddressFromGoo = $this->gmc->emailAddressFromGooGmailClient;
	}

	protected function getGmailClient() { return $this->gmc; }
}


