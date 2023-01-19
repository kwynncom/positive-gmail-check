<?php


require_once(__DIR__ . '/../configDB.php');

class daoUsage extends dao_generic_3 implements qemconfig {
	
	const keepUsageDays = 45;
	const keepUsageS    = self::keepUsageDays * DAY_S;
    
    function __construct() {
		parent::__construct(qemconfig::dbname);
		$this->creTabs('usage');
		$this->clean();
    }
	
	private function clean() {
		$inres = $this->ucoll->createIndex(['U' => 1 /* should be 1 because we're looking to start with oldest */]);
		$dres  = $this->ucoll->deleteMany (['U' => ['$lt' => time() - self::keepUsageS]]);
	}
	
	public static function hasEmailSid(string $e) : bool { // email hash, actually
		$o = new self();
		return $o->hasSidByEmailOb($e);
	}
	
	public function hasSIDbyEmailOb(string $e) : bool {
		$inres = $this->ucoll->createIndex(	 ['sid' => 1,		  'email' =>  1, 'type' => 1]);
		$res = $this->ucoll->findOne(['sid' => pemhsid(false), 'email' => $e, 'type' => 'checked']);
		return $res ? true : false;
	}
    
    private function setIID($res) { $this->iid = $res->getInsertedId();  }
    
    public function setEmail($email) {
		if (!isset($this->iid)) return;
		$res = $this->ucoll->updateOne(['_id' => $this->iid], ['$set' => ['email' => $email]]);
    }

	private static function setSessionDets(array &$a) { setSessionDets($a, true);	}

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