<?php


require_once(__DIR__ . '/../configDB.php');
require_once(__DIR__ . '/../dao.php');

class daoUsage implements qemconfig {
    
    private $iid = null;
    
    function __construct() {
	$this->client = new MongoDB\Client();
	$this->usage = $this->client->selectCollection(dao_plain::dbname, 'usage');
	$this->usage->deleteMany(['time' => ['$lt' => time() - 86400 * 30]]);
    }
    
    private function setIID($res) { $this->iid = $res->getInsertedId();  }
    
    public function setEmail($email) {
	if (!isset($this->iid)) return;
	$res = $this->usage->updateOne(['_id' => $this->iid], ['$set' => ['email' => $email]]);
    }
    
    public function putUse($type) {

	$ddbo  = new stdClass();
	$utime  = microtime(true);
	$dateo = new DateTime();
	$ddbo->timed = new stdClass();
	$ddbo->timed->dateo = $dateo;
	$ddbo->timed->time  = intval($dateo->format('U'));
	$ddbo->timed->utime = doubleval($dateo->format('U.u'));
	$ddbo->timed->uonly = doubleval('0.' . $dateo->format('u'));
	$ddbo->on = $dateo->format('D, M j, g:i:s A (Y +0.u') . 's)';
	setSessionDets($ddbo);
	
	$ddbo->type = $type;
	
	$res = $this->usage->insertOne($ddbo);
	$this->setIID($res);
    }
    
        
    public function getUsage($span, $type) { 
	$mq = ['timed.utime' => ['$gte' => microtime(true) - $span], 'type' => $type];
	$res = $this->usage->count($mq);
	$x = 2;
	return $res;
    }
}