<?php

interface GooOAuthAppConfigI { // some of these aren't needed anymore
	public function saveToken(mixed $tok);
	public function getScope();
	public function getSecretFilePath();
	public function doUponAuth();
	public function getRedirectURL();
}
