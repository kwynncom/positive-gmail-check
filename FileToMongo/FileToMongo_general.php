<?php

require_once('/opt/kwynn/kwutils.php');

class dao_mongoToFile extends dao_generic {
    
    const collName = 'kwtom_files';
    
    public function __construct($dbname, $cbf) {
		parent::__construct($dbname);
		$this->fcoll = $this->client->selectCollection($dbname, self::collName);
		$this->cbf = $cbf;
    }
   
    public function get($newname) {
		$resr = $this->fcoll->findOne(['name' => $newname]);
		$tf = $this->cbf;
		$res = $tf($resr);
		return json_encode($res['file']);
    }
    
    public function put($newname, $datin) {
	$dat['file'] = $datin;
	$ts = time();
	$dat['ts'] = $ts;
	$dat['r']  = date('r', $ts);
	$dat['name'] = $newname;
	
	if (         isset(  $datin['created'])) 
	    $tcs = date('r', $datin['created']);
	else $tcs = false;
	
	if ($tcs) $dat['tokCreatedR'] = $tcs;
	
	$this->fcoll->upsert(['name' => $newname], $dat);
	
    }
    
    public function getSeq($newname) { return parent::getSeq($newname); }
}

class mongoToFile  {
    
    const pbase = '/tmp/';
    const lfilebase = self::pbase . 'cpcs_mongoToFile_lock';
    
    public function __construct($dbname, $newname, $origPath = false, $cbf = false) {
	$this->dao = new dao_mongoToFile($dbname, $cbf);
	
	$lfn = self::lfilebase . '_' . get_current_user();
	
	$this->lfh = fopen($lfn, 'c'); kwas($this->lfh, 'cannot open lock file - mtofile');
	$flr = flock($this->lfh, LOCK_EX); kwas($flr, 'lock file failed mtofile'); unset($flr);
	
	$existing = $this->dao->get($newname);
	if (!$existing && $origPath) $this->put($newname, $origPath);
	$txt = $this->dao->get($newname); kwas($txt, 'mongoToFile fail 1');
	$seq = $this->dao->getSeq($newname); kwas($seq && is_numeric($seq) && $seq > 0, 'seq fail mtofile');
	$fname = self::pbase . $newname . '_' . $seq;
	$fpr = file_put_contents($fname, ''); kwas($fpr !== false, 'init mtofile fail'); unset($fpr);
	$chr = chmod($fname, 0600); kwas($chr, 'chmod fail mtofile'); unset($chr);
	$ffpr = file_put_contents($fname, $txt, FILE_APPEND); kwas($ffpr && is_numeric($ffpr) && $ffpr > 20, 'bad write mtofile'); unset($ffpr);
	$this->fname = $fname;
	$this->name  = $newname;
    }
    
    public function close() {
	$this->put($this->name, $this->fname);
	$flr = flock($this->lfh, LOCK_UN);  kwas($flr, 'lock file unlock failed mtofile'); unset($flr);
	// unlink($this->fname);
    }
    
    public function getFName() { return $this->fname; }
    
    private function put($newname, $opath) {
	$txt = file_get_contents($opath); kwas($txt, 'cannot read opath mongoToFile');
	$this->dao->put($newname, (array)json_decode($txt));
	
    }
} // starting from not-so-secret project version 2020/06/19 10:54pm
