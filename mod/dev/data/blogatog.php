<?php
$url = 'http://markrosewater.tumblr.com/page/%d';

$htmlDoc = new DOMDocument('1.0', 'UTF-8');
$htmlDoc->appendChild($htmlDoc->createElementNS('http://www.w3.org/1999/xhtml', 'main'));
for ($i = 1; $xpath = \Storage::loadExternalXPath(sprintf($url, $i), TIME_DAY); $i ++) {
    $nodeList = $xpath->evaluate('//*[@id="content"]/*');
    foreach ($nodeList as $node) {
        $htmlDoc->documentElement->appendChild($htmlDoc->importNode($node, true));
    }
    if (! $nodeList->length or $i > 100) {
        break;
    }
}

return $htmlDoc;