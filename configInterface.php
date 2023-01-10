<?php

interface GooOAuthAppConfigI {
	public function getSecretFilePath();
	public function getScope();
	public function getRedirectURL();
	public function getGooClientO();
	
	public function saveToken(mixed $tokenEtc);
	public function doUponAuth();
}
