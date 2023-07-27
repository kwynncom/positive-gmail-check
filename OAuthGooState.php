<?php

require_once('/opt/kwynn/kwutils.php');

class GooOAuthState extends dao_generic_3 {
	
	const dbname = 'goooa';
	const collname = 'goostates';
	const goostimeoutS = 1200;
	const consn = 'KWYNN_GOOSTATE_NONCE'; // const to define with state from database
	
	public static function set(string $reqOnce, string $uqin = '') {
		kwas(is_readable($reqOnce), "$reqOnce not a readable file");
		$o = new self($reqOnce, false, $uqin);
		return $o->getID();
	}
	
	public static function doit() {
		new self('', true);
	}
	
	public function getID() { return $this->_id; } 
	
	private function __construct(string $file = '', bool $doit = false, string $uqin = '') {
		$this->setUqIn($uqin);
		$this->db10();
		$this->ofile = $file;
		if ($this->ofile) $this->do10();
		if ($doit) $this->doit20();
	}
	
	private function setUqIn(string $u) {
		if ($u) { $this->uqin = $u; return; }
		if (defined(self::consn)) { $this->uqin = constant(self::consn); return; }
		$this->uqin = '';
	}
	
	private function db10() {
		parent::__construct(self::dbname);
		$this->creTabs(self::collname);
		$dr = $this->gcoll->deleteMany(['U' => ['$lte' => time() - self::goostimeoutS]]);
		return;
	}
	
	private function doit20() {
		$_id = dao_generic_3::oidsvd(isrv('state'), true);
		$r = $this->gcoll->findOne(['_id' => $_id]); 
		kwas($r, 'no pending Goo OAUTH request found - perahps timed out?');
		$f = $r['require_once'];
		kwas(is_readable($f), "$f not readable");
		define(self::consn, $_id);
		require_once($f);
		return;
		
	}
	
	private function do10() {
		$dat = [];
		
		if ($this->uqin) {
			kwas(dao_generic_3::oidsvd($this->uqin, true), 'bad uq id sent to Goo State State OAuth');
			 $this->_id = $dat['_id'] = $this->uqin; 	} // end if
		else $this->_id = $dat['_id'] = dao_generic_3::get_oids(true);
		$dat['require_once'] = $this->ofile;
		$dat['U'] = $U = time();
		$dat['r'] = date('r', $U);
		$res = $this->gcoll->upsert(['_id' => $dat['_id']], $dat, 1, ['kwnoup' => true]);
		return;
	}
	
}

