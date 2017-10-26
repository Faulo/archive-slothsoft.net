<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\FileSystem;

$ret = '';
$ret .= sprintf('[%s] Starting updating...%s%s', date(DATE_DATETIME), PHP_EOL, PHP_EOL);

$doc = $this->getResourceDoc('cron/update-tools', 'xml');
$xpath = self::loadXPath($doc);

$updateNodeList = $xpath->evaluate('//update');
foreach ($updateNodeList as $updateNode) {
    $options = [];
    $options['download-cmd'] = null;
    $options['copy-cmd'] = null;
    $options['success-cmd'] = null;
    
    $name = $updateNode->getAttribute('name');
    $destPath = $updateNode->getAttribute('dest-path');
    $sourceURI = $updateNode->getAttribute('source-uri');
    $sourceXPath = $updateNode->getAttribute('source-xpath');
    foreach ($options as $key => &$val) {
        if ($updateNode->hasAttribute($key)) {
            $val = $updateNode->getAttribute($key);
        }
        if ($php = $xpath->evaluate('string(php)', $updateNode)) {
            $options['success-php'] = $php;
        }
    }
    unset($val);
    
    $ret .= sprintf('[%s] Updating "%s":%s', date(DATE_DATETIME), $name, PHP_EOL);
    $ret .= sprintf('	Checking website "%s"...%s', $sourceURI, PHP_EOL);
    // my_dump([$sourceURI, $sourceXPath]);
    if ($uri = FileSystem::getLinkByXPath($sourceURI, $sourceXPath)) {
        $ret .= sprintf('	Downloading "%s"...%s', $uri, PHP_EOL);
        
        // my_dump($uri);
        $res = FileSystem::downloadByURI($destPath, $uri, $options);
        
        $ret .= sprintf('	%s%s', $res, PHP_EOL);
    } else {
        $ret .= sprintf('	Download link not found in "%s" at "%s"!%s', $sourceURI, $sourceXPath, PHP_EOL);
    }
    $ret .= PHP_EOL;
}

$ret .= sprintf('[%s] ...done! \\o/', date(DATE_DATETIME));

$this->progressStatus |= self::STATUS_RESPONSE_SET;
$this->httpResponse->setStatus(HTTPResponse::STATUS_OK);
$this->httpResponse->setBody($ret);
$this->httpResponse->setEtag(HTTPResponse::calcEtag($ret));