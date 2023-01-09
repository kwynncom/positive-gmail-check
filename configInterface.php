<?php

interface GooOAuthAppConfigI {
	public function getBaseURL();
	public function fileToken(mixed $tok);
	public function getScope();
	public function getSecretFilePath();
	public function getURLSfx();
	public function uponAuth();
	public function getRedirectURL();
}
