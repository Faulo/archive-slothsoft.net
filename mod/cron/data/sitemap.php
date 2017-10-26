<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\Storage;
use Slothsoft\Core\Lambda\Manager;

$host = sprintf('http://%s/sitemap/', $this->httpRequest->clientHost);

if ($xpath = Storage::loadExternalXPath($host)) {
    $urlList = [];
    $nodeList = $xpath->document->getElementsByTagName('loc');
    foreach ($nodeList as $node) {
        $urlList[] = $xpath->evaluate('string(.)', $node);
    }
    
    $code = '
	$url = $args;
	//$ret = file_get_contents($url);
	if ($ret = \Storage::loadExternalDocument($url . "?dnt")) {
		$ret = $ret->saveXML();
	}
	$this->log($url . PHP_EOL . $ret . PHP_EOL);
	';
    
    return Manager::streamClosureList($code, $urlList);
}