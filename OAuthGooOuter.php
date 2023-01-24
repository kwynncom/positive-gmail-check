<?php

require_once('OAuthGoo.php');
require_once('gmailGet.php');

class GooOAUTHWrapperOuter extends GooOAUTHWrapper {
	
	public readonly string $emailAddressFromGoo;
	private $gmc;
	
	public function __construct(array $config) { parent::__construct($config); }
	
	protected function getSavedToken() : array			 { return [];  } /* you probably want to define a child */
	protected function receiveRefreshToken(array $token) { $this->setEmailAddress($token); }
	
	private function setEmailAddress($token) {
		$this->gmc = new gmailGetCl($this->getGoogleClient());
		$this->emailAddressFromGoo = $this->gmc->emailAddressFromGooGmailClient;
	}
	
	protected function getGmailClient() { return $this->gmc; }
}


