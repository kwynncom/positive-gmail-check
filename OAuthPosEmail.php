<?php

require_once('dao.php');
require_once('OAuthGoo.php');
// require_once('configForGooGen.php');
require_once('util.php');
require_once('isUserCookie.php');
require_once('enc.php');

class posEmailOAuth extends GooOAuthWrapper {

	const peoaa = [
							'sfb'  => '/var/kwynn/gooauth/positive_email_secret', 
							'sfx' => '.json',
							'scope' => Google_Service_Gmail::GMAIL_METADATA, 
							'upath' => '/t/7/12/email/',
						   'asfx' => 'intro.php',
							'osfx' => '_live_active_output',
							'redbase' => 'receiveAuthCode.php',
							];
	
	public function __construct() { 
		parent::__construct(self::peoaa); 	
	}

	public function doUponAuth() {
		isucookie::set();
		header('Location: ' .  $this->urlbase . iaacl::getURLQ());
		exit(0);
	}
	
	public function revokeToken() {
		$dao = new dao();
		$dao->deleteToken();
		$res = parent::revokeToken();
    }
}
