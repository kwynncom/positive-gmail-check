<?php

require_once('util.php');
require_once('dao.php');
require_once('/opt/kwynn/creds.php');
require_once('FileToMongo/FileToMongo_general.php');
require_once('sortURLs.php');

class serverSwitch {
    
    const clientSecretName = 'GMail_app_secret_2018_to_2020_06_1';
    const redExt = 'receiveAuthCode.php';
	
	const thisupath = '/t/7/12/email/';
	const thisapath = self::thisupath . 'intro.php';
	
	public static function getThisAPath() {
		return 'https://' . $_SERVER['SERVER_NAME'] .  self::thisapath;
	}
    
    private static function getSecretDomain() {     
		$co = new kwynn_creds();
		$dom = $co->getType('Kwynn_testing_domain_2014_to_2020');
		kwas(isset($dom['domain']), 'no secret domain');
		return     $dom['domain'];
    }
    
    public function getPath() { return $this->settings['goopath']; }
    
    private function set() {
		$this->urlbase = $oarurl = 'https://' . $_SERVER['SERVER_NAME'] .  self::thisupath;
		// $mo = new mongoToFile(dao_plain::dbname, self::clientSecretName, false, [$this, 'sortURLs']);
		// $fname = $mo->getFName();
		$fname = '/var/kwynn/gooauth/positive_email_secret.txt';
		$set = [ 'goopath'   => $fname, 'oarurl'    => $oarurl];
		kwas(file_exists($set['goopath']) && strlen(file_get_contents($set['goopath'])) > 30);
		$this->settings = $set;
    }
    
	public function sortURLs($ain) {
		
		return sortURLs($ain, $this->getRedirectURL());
	}
	
    public function getBaseURL    () { return $this->urlbase; }
    public function getRedirectURL() { return $this->getBaseURL() . self::redExt; }
    
    function __construct() {
		$this->set();
    }
 
}