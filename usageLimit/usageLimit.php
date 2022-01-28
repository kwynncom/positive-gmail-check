<?php

require_once('/opt/kwynn/kwutils.php');

require_once(__DIR__ . '/dao.php');

class usageLimit {
    
    const fname = 'usage.txt';
    
    const testMode = false;
    
    function __construct () {

	startSSLSession(1);
	
	$lim[] = ['type' => 'checked', 'limits' => [86400 => 100, 3600 => 30, 60 => 8, 5 => 1]];
	$lim[] = ['type' => 'oauth'  , 'limits' => [86400 =>  20, 3600 =>  6, 60 => 3, 5 => 1]];
	$lim[] = ['type' => 'revoke' , 'limits' => [86400 =>  20, 3600 =>  6, 60 => 3, 5 => 1]];
	
	$this->lim = $lim;

	$this->dao = new daoUsage();
	$this->ssw = new serverSwitch();
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
    
    public function putUse($type) { 
	
	if (isset($_REQUEST['revoke']) && $_REQUEST['revoke'] === 'Y') $type = 'revoke';
	
	$this->dao->putUse($type);    
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
	$_SESSION['prevRes'] = $res;
    }
    
    public function getPrev() {
	return isset($_SESSION['prevRes']) ? $_SESSION['prevRes'] : '';
    }
    
    
}

