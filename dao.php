<?php

require_once('configDB.php');

class dao_plain extends dao_generic_3 implements qemconfig {
	
	const tfnm = 'gooTokEncDB';
	const atf  =  self::tfnm . '.' . 'access_token';
    
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
		// $this->tcoll->createIndex(['addr' => -1], ['unique' => true]);		
	}

	private function toktoset($t) { // not overwriting refresh token
		$a = [];
		foreach($t as $f => $v) $a[self::tfnm . '.' . $f] = $v;
		return $a;
	}
/*
	private function upByEmail($tok, $email) { // update token itself, being careful of refresh_token
		
		$ats = [];
		// $ats = ['$addToSet' => ['sids'   => vsid()]];
		
		
		$atk = [self::atf => $tok['access_token']];
		$rtv = kwifs($tok, 'refresh_token');
		if ($rtv) { // This is the encrypted form, so it will be specific to keys the client can decrypt
			$uq = ['$or' => [$atk, 'refresh_token' => $rtv]];
		} else $uq = $atk;
		
		$toset = $this->toktoset($tok);
		if ($email) $toset = kwam($toset, ['addr' => $email, 'addrValid' => true]);
		$set = [];
		if ($toset) $set = ['$set' => $toset];

		$p2 = kwam($set, $ats);
		$this->tcoll->updateOne($uq , $p2);
		
		$this->tcoll->deleteMany([self::atf => $tok['access_token'], 'addrValid' => false]);
		
		return;		
	} 	*/
    protected function upsertToken($trwo2, $email) {
		
		foreach($trwo2 as $ty => $trwo) {
		
			$goo = $trwo[self::tfnm];

			// if ($email) $this->upByEmail($goo, $email);

			$dat20 = [
				'addr' => $email ? $email : $trwo['_id'],
				'addrValid' => false,
				'created_tok' => date('r', $goo['created']),
				// 'sids' => [vsid()],
			];

			$dat = kwam($dat20, $trwo);
						
			$this->tcoll->upsert(['_id' => $trwo['_id']], $dat);
		}
	}
   

    
   private function isActiveAT($tin) {
	   $fs = ['access_token', 'created', 'expires_in'];
	   foreach($fs as $f) if (!isset($tin[$f]))  return false;
	   $d = time() - ($tin['created'] + $tin['expires_in']); 
	   if ($d <= 0) return true;
	   return false; 
   }
 
   private function freshOrCanRefresh($tin) : bool {
	   
	   if ($this->isActiveAT($tin))		  return true;
	   if (isset( $tin['refresh_token'])) return true;
	   return false;

   }
   
    protected function getTokenDBO($idos) {

		foreach($idos as $ido) {
			$rest1 = $this->tcoll->findOne(['_id' => $ido['_id']], ['sort' => [self::tfnm . '.created' => -1]]);
			$t = kwifs($rest1, self::tfnm);
			if ($this->freshOrCanRefresh($t)) return $t;
		}
		return false;
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
