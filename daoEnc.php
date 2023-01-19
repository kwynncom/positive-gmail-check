<?php

require_once('daoPlain.php');
require_once('enc.php');

class dao extends dao_plain {
	
	public function __construct(object $logo = null) {
		$this->log = $logo ? $logo : new OAuthLog();
		parent::__construct();
		$this->cob = new enc_cookies(self::tfnm);
	}

	public function deleteTokenKwDB($ignore = null) {
		parent::deleteTokenKwDB($this->cob->getekida());
	}

	public function getTokenDB() {

		$etok  = parent::getTokenDBO($this->cob->getekida());
		if (!$etok) return false;

		$sk = '';
		if (kwifs($etok, 'peer')) {
			$sk = $etok[self::skf]; 
			$etok = $etok[self::tfnm];
		}


		$dtok = $this->cob->dec($etok, $sk);

		$datok = kwifs($dtok, 'access_token');
		if (!$datok) {
			// $this->log->log('no at fr db');
			return false; // if decryption key is lost; do I need to delete stuff, too??? 
		}
		if (isset($dtok['refresh_token'])) { // if can't decrypt key name (enc key object key) needs to not exist
			// $this->log->log('rt fr db');
		} 

		return $dtok;
	}

	public static function expireCookies() { enc_cookies::forceExpire();}

	public function getEmailHash(string $emain) { return $this->emailHash = enc_cookies::emailHashF($emain); }
	
	public function upsertToken($ptok, $emailHash = null) {
		$etok = $this->cob->enc($ptok, $emailHash); unset($ptok);
		parent::upsertToken($etok, $emailHash);
		if (!$emailHash) return;
		$this->usePubKeys(isset($etok[self::tfnm]['refresh_token']), $etok[self::tfnm]['created'], $etok['_id'], $emailHash);
	}

	private function usePubKeys(bool $isrt, int $cre, string $id, string $emh) {
		$a = parent::getPubKeys(     $isrt,     $cre,        $id,	     $emh);
		parent::updatePubsWithSym($this->cob->encWithPub($a));
	}

} // class
