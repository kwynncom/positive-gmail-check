<?php

require_once('OAuthGoo.php');
require_once('OAuthGooState.php');

class GooOAUTHWrapperOuter extends GooOAUTHWrapper {
	
	public readonly string $emailAddressFromGooOauth2;
	
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
	
	protected function setEmailAfterAuth() {
		if (isset($this->emailAddressFromGooOauth2)) return;
		$bs = $this->client->getScopes();
		$this->client->setScopes(Google_Service_Oauth2::USERINFO_EMAIL);
		$oa = new Google_Service_Oauth2($this->client);
		$this->emailAddressFromGooOauth2 = $oa->userinfo->get()->email;
		$this->client->setScopes($bs);
	}

}


