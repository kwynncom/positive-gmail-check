<?php

require_once('isUserCookie.php');

if (isucookie::is() || iaacl::isiaa() || isrv('introUseOnce')) 
	 require_once(__DIR__ . '/html/' . 'template.php');
else require_once(__DIR__ . '/html/' . 'intro.html');
