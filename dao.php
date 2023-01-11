<?php

require_once('configDB.php');

class dao_plain extends dao_generic_3 implements qemconfig {
	
	const tfnm = 'gooTokenActual';
    
    function __construct() {
		parent::__construct(self::dbname);
		$this->creTabs(['t' => 'gooTokens_actual']);

		startSSLSession();
    }


    public function putToken($tok) {
		
		$this->deleteTokenKwDB();
		
		$dat = array(
			'ip'    => $_SERVER['REMOTE_ADDR'],
			'date'  => date('Y-m-d H:i:s'),
			'agent' => $_SERVER['HTTP_USER_AGENT'],
			'sids'   => [vsid()],
			self::tfnm => $tok
		);

		$this->tcoll->insertOne($dat);
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

        $rest1 = $this->tcoll->findOne(['sids' => ['$in' => [vsid()]]]);
		$t = kwifs($rest1, self::tfnm);
		if ($t) $t = $this->freshOrRefresh($t);
		if ($t) return $t;
		else 	return false;
    }
    
    public function updateToken($token) {
		$this->tcoll->updateOne(['sids' => ['$in' => [vsid()]]],   ['$set' => [self::tfnm => $token]]);
    }
    
    public function updateEmail($addr) {
	$q2 = ['sid'  => vsid()];
	$this->tcoll->updateOne(
		['$and' => [['addr' => ['$eq' => $addr]],
			   ['sids' => ['$nin' => [vsid()]]]]
		],
		['$push' => ['sids' => vsid()]]
		);
    }
    
    public function deleteTokenKwDB() {
		
		$qsid = ['sids' => ['$in' => [vsid()]]];
		
		$rs = $this->tcoll->find($qsid);
		$addrs = [];
		foreach($rs as $r) {
			$addr = kwifs($r, 'addr');
			if ($addr) $addrs[$addr] = true;
		}
		
		$this->tcoll->deleteMany($qsid); 
		
		$a20 = array_keys($addrs);
		$this->tcoll->deleteMany(['addr' => ['$in' => $a20]]);
	}
	
	public static function deleteTokenStatic() {
		$o = new self();
		$o->deleteTokenKwDB();
	}

}
