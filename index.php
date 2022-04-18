<?php

require_once('isUserCookie.php');

if (isucookie::is() || iaacl::isiaa()) 
	 require_once('template.php');
else require_once('intro.html');
