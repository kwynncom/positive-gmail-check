<?php

require_once('configDB.php');

class dao_plain implements qemconfig {
    
    function __construct() {
    	$this->client = new MongoDB\Client();
	$this->sessc = $this->client->selectCollection(self::dbname, 'sessions');
	$this->tokc  = $this->client->selectCollection(self::dbname, 'gotokens');
	
	startSSLSession(1);
    }
    
    public function putToken($tok) {
	$sobj = array(
	    'ip'    => $_SERVER['REMOTE_ADDR'],
	    'date'  => date('Y-m-d H:i:s'),
	    'agent' => $_SERVER['HTTP_USER_AGENT'],
	    'sid'   => vsid(),
	    'tgtok' => $tok
	);
		
	$this->sessc->updateOne(['sid' => vsid()], ['$set' => $sobj], ['upsert' => true]);
	
	if (isset($tok['refresh_token']))
	    $this->tokc->updateOne(
		    ['sid1'  => vsid()],
		    ['$set' => ['gtok' => $tok, 'sid1' => vsid(), 'sids' => [vsid()]]],
		    ['upsert' => true]
	    );
   }
   
   private function freshOrRefresh($tin) {
	   if (isset( $tin['refresh_token'])) return (array) $tin;
	   $fs = ['access_token', 'created', 'expires_in'];
	   foreach($fs as $f) if (!isset($tin[$f]))  return false;
	   
	   $d = time() - ($tin['created'] + $tin['expires_in']); 
	   if ($d <= 0) return (array) $tin;
	   return false;
   }
    
    public function getToken() {

        $rest1 = $this->tokc->findOne(['sids' => ['$in' => [vsid()]]]); 
		if ($rest1 && isset($rest1->gtok)) return $this->freshOrRefresh($rest1->gtok);


		$ress1 = $this->sessc->findOne(['sid' => ['$eq' => vsid()]]); 

		if (isset($ress1->tgtok)) return $this->freshOrRefresh($ress1->tgtok);
		return false;
    }
    
    public function updateToken($token) {
	$this->tokc->updateOne(['sids' => ['$in' => [vsid()]]],   ['$set' => ['gtok' => $token]]);
    }
    
    public function updateEmail($addr) {
	$q2 = ['sid'  => vsid()];
	$this->tokc ->updateOne(['sid1' => vsid()],   ['$set' => ['addr' => $addr]]);
	$this->sessc->updateOne(['sid'  => vsid()],   ['$set' => ['addr' => $addr]]);
	$this->tokc->updateOne(
		['$and' => [['addr' => ['$eq' => $addr]],
			   ['sids' => ['$nin' => [vsid()]]]]
		],
		['$push' => ['sids' => vsid()]]
		);
    }
    
    public function deleteToken() {
        $this->tokc  ->deleteMany(array('sids' => array('$in' => [vsid()])), ['$unset' => ['gtok' => '']]); 
		$this->sessc ->deleteMany(['sid'  => vsid()], ['$unset' => ['tgtok' => '']]); 
    }
	
	public static function deleteTokenStatic() {
		$o = new self();
		$o->deleteToken();
	}

}
