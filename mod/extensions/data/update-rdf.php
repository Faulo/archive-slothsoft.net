<?php
$ret = '';
$ret .= sprintf('[%s] Starting updating extensions...%s%s', date(DATE_DATETIME), PHP_EOL, PHP_EOL);

$docList = $this->getResourceDir('extensions/update', 'status');

foreach ($docList as $id => $doc) {
	$updateFile = $doc->documentElement->getAttribute('realpath');
	
	$signingList = $this->getResourceDir(sprintf('extensions/%s-signing', $id), 'status');
	if (count($signingList) === 3) {
		$ret .= sprintf('[%s] Updating "%s" at "%s":%s', date(DATE_DATETIME), $id, $updateFile, PHP_EOL);
		
		$signedFile = null;
		$version = null;
		foreach ($signingList as $name => $doc) {
			if (preg_match('/([0-9\.]+)-fx/', $name, $match)){
				$version = $match[1];
				$signedFile = $doc->documentElement->getAttribute('realpath');
				break;
			}
		}
		if ($signedFile and $version) {
			$extDir = dirname(dirname($signedFile)) . DIRECTORY_SEPARATOR;
			
			$ret .= sprintf('	Found version %s in %s%s', $version, $signedFile, PHP_EOL);
			
			$installFile = $signingList['install.rdf']->documentElement->getAttribute('realpath');
			$unsignedFile = $signingList['unsigned.xpi']->documentElement->getAttribute('realpath');
			
			$xpiFile = $extDir . $version . '.xpi';
			$rdfFile = $extDir . $version . '.rdf';
			
			if (file_exists($xpiFile) and file_exists($rdfFile)) {
				$ret .= sprintf('	Already up to date!%s', PHP_EOL);
			} else {
				rename($signedFile, $xpiFile);
				copy($installFile, $rdfFile);
			}
		}
	}
	
	$uri = sprintf('http://slothsoft.net/getFragment.php/extensions/update?id=%s', $id);
	if ($updateDoc = \Storage::loadExternalDocument($uri, 0)) {
		$updateDoc->save($updateFile);
		$ret .= sprintf('	UPDATE! Please sign file "%s"~%s', $updateFile, PHP_EOL);
	} else {
		$ret .= sprintf('	ERROR?! %s%s', $uri, PHP_EOL);
	}
}

return \CMS\HTTPFile::createFromString($ret);


$this->progressStatus |= self::STATUS_RESPONSE_SET;
$this->httpResponse->setStatus(\CMS\HTTPResponse::STATUS_OK);
$this->httpResponse->setBody($ret);
$this->httpResponse->setEtag(\CMS\HTTPResponse::calcEtag($ret));