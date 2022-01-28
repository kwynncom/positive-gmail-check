<?php

require_once('util.php');
require_once('dao.php');
require_once('/opt/kwynn/creds.php');
require_once('FileToMongo/FileToMongo_general.php');

class serverSwitch {
    
    const clientSecretName = 'GMail_app_secret_2018_to_2020_06_1';
    const redExt = 'gmailClient.php';
    
    const kwynnloc = 'kwynnLocal';
	
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
    
    private function set($key) {
	
	$mo = new mongoToFile(dao_plain::dbname, self::clientSecretName);
	$fname = $mo->getFName();
	
	$kwsecdom = 'n/a';
	// if ($key === self::kwynnloc) $kwsecdom = self::getSecretDomain();

	$set = 
	    array(
		/* self::kwynnloc => array(
		    'goopath'   => $fname,
		    'oarurl'    => 'https://' . $kwsecdom . '/'
		), */

		// 'kwynn.com' => array(
		    'goopath'   => $fname,
 		    'oarurl'    => 'https://' . $_SERVER['SERVER_NAME'] .  self::thisupath
		// )
	    );
	
	// kwas(isset($settings[$key]),  '1-535 ' . $key);
	// $set =     $settings[$key];
	kwas(file_exists($set['goopath']) && strlen(file_get_contents($set['goopath'])) > 30);
	$this->settings = $set;
    }
    
    public function getBaseURL    () { return $this->settings['oarurl']; }
    public function getRedirectURL() { return $this->getBaseURL() . self::redExt; }
    
    function __construct() {
	if (getenv('KWYNN_ID_201701') === 'aws-nano-1') $name = 'kwynn.com';
	else $name = self::kwynnloc;
	$this->set($name);
    }
 
}