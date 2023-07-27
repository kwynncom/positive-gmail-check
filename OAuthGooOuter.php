<?php

require_once('OAuthGoo.php');
require_once('OAuthGooState.php');
require_once('gmailGet.php');

class GooOAUTHWrapperOuter extends GooOAUTHWrapper {
	
	public readonly string $emailAddressFromGOW;
	
	public function __construct(array $config, string $requireOnceF = '', bool $revoke = false, string $uqin = '') { 
		$uq = '';
		if ($requireOnceF) {
			$uq = GooOAuthState::set($requireOnceF, $uqin); 
		} unset($uqin);
		parent::__construct($config, $uq, $revoke);
		if ($uq) $this->client->setState($uq);
	}
	
	protected function getSavedToken() : array			 { return [];  } /* you probably want to define a child */
	protected function receiveRefreshToken(array $token) { 
		$this->setEmailAfterAuth();
	}
	
	protected function setEmailAfterAuth() : string {
		if (isset($this->emailAddressFromGOW)) return $this->emailAddressFromGOW;
		$tok = $this->client->getAccessToken();
		$mys = $tok['scope']; unset($tok);
		if ($emgm = $this->setEmailAddressFromGmail($mys)) return $emgm;
		
		kwas(strpos($mys, Google_Service_Oauth2::USERINFO_EMAIL) !== false, 'no way to set email address Goo out wrap');
		$oa = new Google_Service_Oauth2($this->client);
		$this->emailAddressFromGOW = $oa->userinfo->get()->email;
		return $this->emailAddressFromGOW;
	} // func
	
	private function setEmailAddressFromGmail(string $mys) : string {
		if (strpos($mys, gmailGetCl::emailAddressScope) !== false) {
			$e = gmailGetCl::getEmailAddress($this->client);
			$this->emailAddressFromGOW = $e;
			return $e;
		}
		return '';
		
	}
} // class


