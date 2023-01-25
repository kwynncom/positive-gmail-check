<?php

require_once('/opt/kwynn/kwutils.php');

require_once(__DIR__ . '/usageLimitDao.php');

class usageLimit {
    
    const fname = 'usage.txt';
    const testMode = false;
	const useTypes = ['checked', 'oauth', 'revoke'];
    
    function __construct () {

		startSSLSession();

		$lim[] = ['type' => 'checked', 'limits' => [86400 => 250, 3600 => 55, 60 => 8, 5 => 1]];
		$lim[] = ['type' => 'oauth'  , 'limits' => [86400 =>  50, 3600 => 15, 60 => 4, 5 => 2]];
		$lim[] = ['type' => 'revoke' , 'limits' => [86400 =>  50, 3600 => 15, 60 => 3, 5 => 1]];

		$this->lim = $lim;

		$this->dao = new daoUsage();
    }
    
    public function setEmail($email) { $this->dao->setEmail($email); }
    
    private function checkLimit() {
	
	foreach ($this->lim    as $arr) 
	foreach ($arr['limits'] as $key => $val)
	{
	    $cnt[$key] =  $this->dao->getUsage($key, $arr['type']);
	    if ($cnt[$key] > $val && self::testMode === false) $this->exception($key, $arr['type']);
	    
	    if ($arr['type'] === 'checked') $this->cnt = $cnt;
	}
	
	if (isset($this->oex)) throw $this->oex;
    }
    
    public function putUse(string $ty) { 
	
		kwas(in_array($ty, self::useTypes), 'bad usage limit use type');
		$this->dao->putUse($ty);    
		$this->checkLimit();
	
    }
    
    public function getLimitsTxt() {
	
	$str = '';
	if (	isset($this->cnt))
		foreach  ($this->cnt as $val) $str .= $val . ' ';
	return $str;
    }
    
    private function exception($key, $type) {

	$this->oex  = new Exception('u52043 - ' . $key . ' type: ' . $type, 261840);
	
    }
    
    public function goodOrEx() {
	if (isset($this->oex)) throw $this->oex;
    }
    
    public function setPrev($res) {
		if (is_array($res)) $setto = $res;
		else				$setto = [];
			
		$_SESSION['prevRes'] = $setto;
	
    }
    
    public function getPrev() {
		return isset($_SESSION['prevRes']) ? $_SESSION['prevRes'] : [];
    }
    
    
}

