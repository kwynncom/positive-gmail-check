<?php

require_once('OAuthGoo.php');
require_once('OAuthGooState.php');
require_once('gmailGet.php');

class GooOAUTHWrapperOuter extends GooOAUTHWrapper {
	
	public readonly string $emailAddressFromGOW;
	
	public static function haveScope() {
		
	}
	
	public function __construct(array $config, string $requireOnceF = '') { 
		$uq = '';
		if ($requireOnceF) $uq = GooOAuthState::set($requireOnceF);
		parent::__construct($config, $uq); 
		if ($uq) $this->client->setState($uq);
		
	}
	
	protected function getSavedToken() : array			 { return [];  } /* you probably want to define a child */
	protected function receiveRefreshToken(array $token) { 
		$this->setEmailAfterAuth();
	}
	
	protected function setEmailAfterAuth() : string {
		if (isset($this->emailAddressFromGOW)) return $this->emailAddressFromGOW;
		if ($e = gmailGetCl::getEmailAddress($this->client)) { $this->emailAddressFromGOW = $e; return $e; }
		$ss =  $this->client->getScopes();
		kwas(in_array(Google_Service_Oauth2::USERINFO_EMAIL, $ss), 'no way to set email address Goo out wrap');
		$oa = new Google_Service_Oauth2($this->client);
		$this->emailAddressFromGOW = $oa->userinfo->get()->email;
		return $this->emailAddressFromGOW;
	} // func
} // class


