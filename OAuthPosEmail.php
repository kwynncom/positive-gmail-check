<?php

require_once('/opt/kwynn/crackObject.php');

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
		$this->dao->updateJustUsedToken($this->getMemTok(), $em);
	}

	private function getMemTok() {
		
		$a10 = (array)$this->client;
		
		$a30 = kwifs($a10, "\x00Google\\Client\u0000token");
		
		exit(0);
		$j = json_encode($a10);
		file_put_contents('/tmp/t', $j);
		exit(0);
		$sz = strlen($j);
		$a20 = json_decode($j, true);
		
		$a40 = $a20['Google\Clienttoken'];
		
		// $a = crackObject::crack($a10['Google\Clienttoken']);
		return;
	}
	
	public function doUponAuth($tok) {
		isucookie::set();
		$this->dao->insertToken($tok);
		$em = gmailClient::getEmailStatic($this);
		$this->regUsage($em);
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
