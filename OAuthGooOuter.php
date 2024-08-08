<?php

require_once('OAuthGoo.php');
require_once('OAuthGooState.php');
require_once('gmailGet.php');

class GooOAUTHWrapperOuter extends GooOAUTHWrapper {
	
    public  readonly string $emailAddressFromGOW;
    private readonly string $emailAddressOAuthedPr; // see note 2024/08/07 21:08 - at bottom

    public function __construct(array $config, string $requireOnceF = '', bool $revoke = false, string $uqin = '') { 
	$uq = '';
	if ($requireOnceF) {
		$uq = GooOAuthState::set($requireOnceF, $uqin); 
	} unset($uqin);
	parent::__construct($config, $uq, $revoke);
	if ($uq) $this->client->setState($uq);
    }

    protected function getSavedToken() : array			 { return [];  } /* you probably want to define a child */
    protected function receiveRefreshToken(array $token) { // create a child function to receive the token in the param; don't forget to call this parent from the child before getting email
        $this->setEmailAfterAuth();
    }

    protected function setEmailAfterAuth() : string {
	if (isset($this->emailAddressOAuthedPr)) return $this->emailAddressOAuthedPr;
	$tok = $this->client->getAccessToken();
	$mys = $tok['scope']; unset($tok);
	if ($emgm = $this->setEmailAddressFromGmail($mys)) return $emgm;

	kwas(strpos($mys, Google_Service_Oauth2::USERINFO_EMAIL) !== false, 'no way to set email address Goo out wrap');
	$oa = new Google_Service_Oauth2($this->client);
	return $this->set2EmailOVars($oa->userinfo->get()->email);
    } // func

    private function setEmailAddressFromGmail(string $mys) : string {
	if (strpos($mys, gmailGetCl::emailAddressScope) !== false) {
		$e = gmailGetCl::getEmailAddress($this->client);
		$this->set2EmailOVars($e);
		return $e;
	}
	return '';
    }

    private function set2EmailOVars($setTo) : string {
	return $this->emailAddressFromGOW = $this->emailAddressOAuthedPr = $setTo;
    }

    public function getOAuthedEmail() : string { 
	if (!isset($this->emailAddressOAuthedPr)) return '';  // kwifs does NOT work because of the private property.  
	return     $this->emailAddressOAuthedPr;
    }

} // class

/* While it's nice to be able to publicly access the var, I should set a private var to make sure the approval was from Goo and not just assigned by anyone.
 * I'll try to consider the public var depprecated and remove it.  It's in use right now, though. *   */


