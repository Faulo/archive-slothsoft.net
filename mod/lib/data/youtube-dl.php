<?php

$url = $this->httpRequest->getInputValue('url');

$ret = 'null';

if ($url) {
	$cmd = sprintf('youtube-dl %s -J', escapeshellarg($url));

	$res = `$cmd`; //http://de2.php.net/manual/de/language.operators.execution.php
	if (@json_decode($res, true)) {
		$ret = $res;
	}
}


return \CMS\HTTPFile::createFromString($ret, 'youtube-dl.json');