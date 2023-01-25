<?php 

require_once(__DIR__ . '/../positiveEmailDefaults.php');
require_once(__DIR__ . '/../OAuthGooOuter.php');

$goooa = new GooOAUTHWrapperOuter(positiveEmailDefaults::peoaa, __FILE__);
$url = $goooa->getOAuthURL();
kwynn();

if ($url) { header('Location: ' . $url); exit(0); }

header('Content-Type: text/plain');
echo('Logged in as ' . $goooa->emailAddressFromGoo);
exit(0);