<?php

interface positiveEmailDefaults {
	const peoaa = [
					'sfb'  => '/var/kwynn/gooauth/positive_email_secret', 
					'sfx' => '.json',
					'scope' => Google_Service_Gmail::GMAIL_METADATA, 
					'upath' => '/t/7/12/email/',
					'osfx' => '_live_active_output',
					'redbase' => 'receiveAuthCode.php',
				];
}