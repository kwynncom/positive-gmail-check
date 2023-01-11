<?php

require_once('OAuthGoo.php');
require_once('util.php');
require_once('isUserCookie.php');
require_once('enc.php');
require_once('gmailClient.php');

class posEmailOAuth extends GooOAuthWrapper {

	const peoaa = [
							'sfb'  => '/var/kwynn/gooauth/positive_email_secret', 
							'sfx' => '.json',
							'scope' => Google_Service_Gmail::GMAIL_METADATA, 
							'upath' => '/t/7/12/email/',
							'osfx' => '_live_active_output',
							'redbase' => 'receiveAuthCode.php',
							];
	
	public function __construct() { 
		$this->setDao();
		parent::__construct(self::peoaa);
	}
	
	public function regUsage($em) {
		$this->dao->updateJustUsedToken($this->client->getAccessToken(), $em);
	}

	public function doUponAuth($tok) {
		isucookie::set();
		$em = false;
		if (1) $em = gmailClient::getEmailStatic();
		$this->dao->insertToken($tok, $em);
		header('Location: ' .  $this->urlbase . iaacl::getURLQ());
		exit(0);
	}
	
	public function revokeToken() {
		$this->deleteToken();
		$res = parent::revokeToken();
    }
	
	private function deleteToken() {		$this->dao->deleteTokenKwDB();			}
	
	protected function getSavedToken() {
		return $this->dao->getToken();	
	}

	
	private function setDao() {	$this->dao = new dao();	}
	
}
