<?php

require_once('OAuthGoo.php');

class GooOAUTHWrapperOuter extends GooOAUTHWrapper {
	
	public readonly string $emailAddressFromGoo;
	private $gmc;
	
	protected function getSavedToken() : array			 { kwas(false, 'define getSavedToken child function'); }
	protected function receiveRefreshToken(array $token) { $this->setEmailAddress($token); }
	
	private function setEmailAddress($token) {
		$this->gmc = new gmailGetCl($this->getGoogleClient());
		$this->emailAddressFromGoo = $this->gmc->emailAddressFromGooGmailClient;
	}
	
	protected function getGmailClient() { return $this->gmc; }
}


