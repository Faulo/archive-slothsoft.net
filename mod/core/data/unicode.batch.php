<?php

//$resDoc = $this->getResourceDoc('/core/unicode-all', 'xml');
$resDoc = \DOMHelper::loadDocument(__DIR__ . '/../res/ucd.all.flat.xml');
//die($resDoc->saveXML($resDoc->documentElement->cloneNode(false)));

$charList = [];
$nodeList = $resDoc->getElementsByTagName('char');
foreach ($nodeList as $node) {
	$key = 'x' . $node->getAttribute('cp');
	$val = $node->getAttribute('na');
	if (!strlen($val)) {
		$val = $node->getAttribute('na1');
	}
	if (strlen($val)) {
		$charList[$key] = $val;
	}
}

my_dump($charList);
die();