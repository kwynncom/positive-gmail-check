<?php

require_once('isUserCookie.php');

if (!isucookie::is()) require_once('intro.html');
else				  require_once('template.php');
