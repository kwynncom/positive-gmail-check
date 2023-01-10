<?php

require_once(__DIR__ . '/../' . 'OAuthGoo.php');


class proj2Test extends GooOAuthWrapper {

	const peoaa = [
							'sfb'  => '/var/kwynn/law/law_test_calendar_oauth_20230109-1', 
							'sfx' => '.json',
							'scope' => 'https://www.googleapis.com/auth/drive https://www.googleapis.com/auth/calendar.events https://www.googleapis.com/auth/gmail.send',
							'upath' => '/t/7/12/email/proj2/', // need trailing /
							'osfx' => '_live_active_output',
							'redbase' => 'receiveAuthCodeP2.php',
							];
	
	public function __construct() { 
		parent::__construct(self::peoaa); 	
	}

	public function doUponAuth($tok) {
		$this->saveToken($tok);
		header('Location: ' .  $this->urlbase . 'result.php');
		exit(0);
	}
}
