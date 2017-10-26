<?php
namespace Slothsoft\CMS;

$tmpDoc = $this->getFragmentDoc('core/sitemap');
$tmpPath = self::loadXPath($tmpDoc);

$ret = [];

$nodeList = $tmpPath->evaluate('//*[name() = "loc"]');
foreach ($nodeList as $node) {
    $ret[] = sprintf('start /b /wait curl "%s" -o batch.xhtml', $node->textContent);
}

$ret = implode(PHP_EOL, $ret);

$this->httpResponse->setBody($ret);
$this->httpResponse->setDownload(false);
$this->progressStatus = self::STATUS_RESPONSE_SET;
return;