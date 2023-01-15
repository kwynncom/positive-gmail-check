<?php

// if named ...Code.php, it hits my ../.htaccess rewrite rule

require_once(__DIR__ . '/' . 'OAuthProj2.php');

$o = new proj2Test();
$url = $o->getOAuthURL();
if ($url) {
	header('Location: ' . $url);
	exit(0);
}
