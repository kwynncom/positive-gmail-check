<?php

interface GooOAuthAppConfigI {
	public function getBaseURL();
	public function fileToken(mixed $tok);
	public function getScope();
	public function getSecretFilePath();
}
