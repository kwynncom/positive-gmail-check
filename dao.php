<?php

require_once('configDB.php');

class dao_plain extends dao_generic_3 implements qemconfig {
	
	const tfnm = 'gooTokenActual';
    
    function __construct() {

		$this->dbset();

		startSSLSession();
    }

	private function dbset($act = '') {
		if (!$act) { 
			parent::__construct(self::dbname);
			$this->creTabs(['t' => 'gooTokens_actual']);
			$this->creidx();
		} else if ($act === 'drop') {
			if (time() < strtotime('2023-01-11 23:15')) {
				$this->tcoll->drop();
				$this->creidx();
			}
		}
		
		
		
		
		
	}
	
	private function creidx() {
		$this->tcoll->createIndex(['addr' => -1], ['unique' => true]);		
	}
	
	// private function 

    protected function updateToken($tok, $email) {
		
		// $this->deleteTokenKwDB(); // probably not, actually
		
		// **** inserting and updating should follow the same rules / should be the same function
		// if more recent refresh token, replace; if more recent access token, replace
		// addr becomes unique index
		// $addToSet
		
		if ($email) {
			$f = self::tfnm . '.' . 'access_token';
			$uq = ['$or' => [[$f => $tok['access_token']], ['addr' => $email]]];			
			$dat = ['addr' => $email, 'addrValid' => true];
			$set = ['$set' => $dat];
			$ats = ['$addToSet' => ['sids'   => vsid()]];
			$p2 = kwam($set, $ats);
			// $dat = kwam($dat, $set, $ats);
			$this->tcoll->updateOne($uq , $p2);

			return;
		} else {
		
			$id = dao_generic_3::get_oids();
			$dat = [
				'_id'  => $id,
				'addr' => $id,
				'addrValid' => false,
				'created_tok' => date('r', $tok['created']),
				self::tfnm => $tok,
				'sids' => [vsid()],
			];

			$this->dbset('drop');
			$this->tcoll->insertOne($dat);
		}
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
    
    protected function updateTokenolder($token, $email) {
		
		if (0) { // This is where I need to make sure I'm not creating new but unnecessary / useless data
			$upup = ['create_tok_re' => date('r', $token['created'])];
			$this->tcoll->updateOne(['sids' => ['$in' => [vsid()]]],   
									['$set' => [self::tfnm => $token, $upup]]);
		}
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
