<?php

require_once('configDB.php');

class dao_plain extends dao_generic_3 implements qemconfig {
	
	const tfnm = 'gooTokEncDB';
	const atf  =  self::tfnm . '.' . 'access_token';
	const skf = 'symkeypue';
	const gtcollid = 'tokDocID';
	const keybits  = 2048; // less than 2048 only apt for testing
	const keybitsf = 'private_key_bits'; // NOT arbitrary name; based on PHP built-in func
	
	public static function test() {
		cliOrDie();
		new self('test');
	}
    
	private function getPeerToken(string $_id) : array {
		//
		$r = $this->pcoll->findOne(['_id' => $_id,   self::gtcollid => ['$exists' => true], self::skf => ['$exists' => true]],  
									['projection' => ['_id' => true, self::gtcollid => true, self::skf => true]]);
		$tp = $this->tcoll->findOne(['_id' => kwifs($r, self::gtcollid)]);
		if (!$tp) return [];
		
		$this->log->log('rt peer');
		return [self::tfnm => $tp[self::tfnm], self::skf => $r[self::skf], 'peer' => true];
		
	}
	
	private function testI() {
		cliOrDie();
		$r = $this->getPeerToken('0116-0127-2023-24s-0e1dc63eae18ff2d');
		return;
	}
	
    function __construct($istest = false) {

		$this->dbset();
		startSSLSession();
		if ($istest) $this->testI();
    }

	private function dbset($act = '') {
		if (!$act) { 
			parent::__construct(self::dbname);
			$this->creTabs(['t' => 'gooTokens_actual', 'p' => 'pubkeys_actual']);
			$this->creidx();
		} else if ($act === 'drop') {
			if (time() < strtotime('2023-01-11 23:15')) {
				$this->tcoll->drop();
				$this->creidx();
			}
		}
		
		$this->policeBits();
	}
	
	private function creidx() {
		// $this->tcoll->createIndex(['addr' => -1], ['unique' => true]);		
	}

	private function toktoset($t) { // not overwriting refresh token
		$a = [];
		foreach($t as $f => $v) $a[self::tfnm . '.' . $f] = $v;
		return $a;
	}

	public static function ssdwp(array &$a, string $p = '') {
		
		if ($p) $p .= '_';
		
		setSessionDets($d);
		foreach($d as $k => $v) $a[$p . '' . $k] = $v;
		return;
	}
	
    protected function upsertToken($trwo, $email) {
		
		$goo = $trwo[self::tfnm];

		$dat20 = [
			'addr' => $email ? $email : $trwo['_id'],
			'addrValid' => $email ? true : false,
			'created_tok' => date('r', $goo['created']),
			// 'sids' => [hashSessionIDNotRealName()],
		];

		self::ssdwp($dat20, 'privowner');
		$dat = kwam($dat20, $trwo);
		
		$this->savePub($dat); 
		unset($dat[enc_cookies::cooPub]);
		

		$this->tcoll->upsert(['_id' => $trwo['_id']], $dat);

	}
	
	protected function updatePubsWithSym(array $a) {
		
		setSessionDets($dets);
		
		foreach($a as $_id => $dat) {
			self::ssdwp($dat, 'keyprovider');
			$res = $this->pcoll->upsert(['_id' => $_id], $dat);
			continue;
		}
		
	}
	
	private function policeBits() {
		$q10 = [self::keybitsf => ['$lt' => self::keybits]];
		$q20 = [self::keybitsf => ['$exists' => false]];
		$q30 = ['$or' => [$q10, $q20]];
		
		$df = $this->pcoll->deleteMany($q30);
		return;
	}
   //  string | null  // for $emh - removing for now - emergency
	protected function getPubKeys(bool $isrt, int $cre, string $_id,$emh) : array {
		if (!$emh) return [];
		
		if (!$isrt) return [];
		
		$q10 = ['addrValid' => true, 'addr' => $emh, '_id' => ['$ne' => $_id]];
		
		$a = $this->pcoll->find($q10,
								['projection' => ['_id' => true, 'addr' => true, 'pub' => true]]);
		
		if (!$a) return [];
		return $a;
		
	}
	
	private function savePub(array $dat) {
		unset($dat[dao::tfnm]);
		$res = $this->pcoll->upsert(['_id' => $dat['_id']], $dat);
		return;
	}

    
   private function freshOrCanRefresh($tin, $testing) : bool {
	   if (isset( $tin['refresh_token'])) {
		   $this->log->log('rt me');
		   return true;
	   }
	   
	   // only need reversed logic for testing - that is, I was testing with the rt below the at check
	   if (!$testing && GooOAUTHWrapper::accessTokenHasTimeRemaining($tin))  return true;
	   return false;
   }
   
    protected function getTokenDBO(array $ido) : array {
		
		$id = kwifs($ido, '_id');
		if (!$id) return [];
		
		$rest1 = $this->tcoll->findOne(['_id' => $id], ['sort' => [self::tfnm . '.created' => -1]]);
		$t = kwifs($rest1, self::tfnm);
		if ($this->freshOrCanRefresh($t, time() < strtotime('2023-01-16 05:30'))) return $t;
		// return [];
		return $this->getPeerToken($id);		 // ***** almost working
    }
    

	
    protected function deleteTokenKwDB($a) {
		
		$r = $this->tcoll->deleteOne(['_id' => $a['_id']]);
		return;
	}
	
	public static function deleteTokenStatic() {
		$o = new self();
		$o->deleteTokenKwDB();
	}

}

if (iscli()) dao_plain::test();
