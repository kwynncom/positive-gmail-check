<?php 

require_once(__DIR__ . '/../positiveEmailDefaults.php');
require_once(__DIR__ . '/../OAuthGooOuter.php');

$ca = positiveEmailDefaults::peoaa;
$ca['scope'] = Google_Service_Oauth2::USERINFO_EMAIL;

$goooa = new GooOAUTHWrapperOuter($ca, __FILE__);
$url = $goooa->getOAuthURL();
kwynn();

if ($url) { header('Location: ' . $url); exit(0); }

header('Content-Type: text/plain');
echo('Logged in as ' . $goooa->emailAddressFromGOW);
exit(0);