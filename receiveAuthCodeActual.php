<?php

// the switch is just for testing
if (FALSE && (time() > strtotime('2023-01-24 02:20'))) {
	require_once('positiveEmail.php');
	new positiveEmailCl();
	kwynn();
}
else if (FALSE) {
	require_once(__DIR__ . '/login/index.php');
}

require_once('OAuthGooState.php');

GooOAuthState::doit();
