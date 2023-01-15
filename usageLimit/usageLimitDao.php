<?php


require_once(__DIR__ . '/../configDB.php');

class daoUsage extends dao_generic_3 implements qemconfig {
    
    private $iid = null;
    
    function __construct() {
		parent::__construct(qemconfig::dbname);
		$this->creTabs('usage');
		$this->ucoll->deleteMany(['U' => ['$lt' => time() - 86400 * 30]]);
    }
    
    private function setIID($res) { $this->iid = $res->getInsertedId();  }
    
    public function setEmail($email) {
		if (!isset($this->iid)) return;
		$res = $this->ucoll->updateOne(['_id' => $this->iid], ['$set' => ['email' => $email]]);
    }

	private static function setSessionDets(&$o) {
	    $o['ip']    = isset($_SERVER['REMOTE_ADDR'    ]) ? $_SERVER['REMOTE_ADDR'    ] : null ;
	    $o['agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
		$o['sid']   = vsid();
	}

    public function putUse($type) {

		$dat = [];
		$U = time();
		$dat['r'] = date('r', $U);
		$dat['U']  = $U;
		
		self::setSessionDets($dat);

		$dat['type'] = $type;

		$res = $this->ucoll->insertOne($dat, ['kwnoup' => true]);
		$this->setIID($res);
    }
        
    public function getUsage($span, $type) { 
		$res = $this->ucoll->count(['U' => ['$gte' => time() - $span], 'type' => $type]);   
		return $res;
		
	}
}