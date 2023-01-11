<?php

require_once('configDB.php');

class dao_plain extends dao_generic_3 implements qemconfig {
	
	const tfnm = 'gooTokenActual';
    
    function __construct() {
		parent::__construct(self::dbname);
		$this->creTabs(['t' => 'gooTokens_actual']);

		startSSLSession();
    }


    protected function insertToken($tok) {
		
		$this->deleteTokenKwDB();
		
		$dat = array(
			'ip'    => $_SERVER['REMOTE_ADDR'],
			'date'  => date('Y-m-d H:i:s'),
			'agent' => $_SERVER['HTTP_USER_AGENT'],
			'sids'   => [vsid()],
			'created_tok' => date('r', $tok['created']),
			self::tfnm => $tok,
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
    
    protected function getToken() {

        $rest1 = $this->tcoll->findOne(['sids' => ['$in' => [vsid()]]]);
		$t = kwifs($rest1, self::tfnm);
		if ($t) $t = $this->freshOrRefresh($t);
		if ($t) return $t;
		else 	return false;
    }
    
    protected function updateToken($token) {
		
		$upup = ['create_tok_re' => date('r', $token['created'])];
		
		$this->tcoll->updateOne(['sids' => ['$in' => [vsid()]]],   
									['$set' => [self::tfnm => $token, $upup]]);
    }
    
    public function updateEmail($addr) {

			// I do NOT want to upsert.
		$r10 = $this->tcoll->updateOne(['addr' => ['$exists' => false], 'sids' => ['$in' =>  [vsid()]]], ['$set'  => ['addr' => $addr ]]);
		$r20 = $this->tcoll->updateOne(['addr' => ['$eq'	 => $addr], 'sids' => ['$nin' => [vsid()]]], ['$push' => ['sids' => vsid()]]);
		
		return;
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
