<?php

$uriHost = 'http://www.minecraftwiki.net/wiki/';
$uriSource = 'Data_values';

$sourceDoc = new \DOMDocument();
@$sourceDoc->loadHTMLFile($uriHost . $uriSource);

$sourcePath = new \DOMXPath($sourceDoc);


$removeExpr = ['//sup'];
foreach ($removeExpr as $expr) {
	$nodeList = $sourcePath->evaluate($expr);
	$removeList = [];
	foreach ($nodeList as $node) {
		$removeList[] = $node;
	}
	foreach ($removeList as $node) {
		$node->parentNode->removeChild($node);
	}
}

$sourceExpr = '//tr[count(td) = 4]';

$sourceList = $sourcePath->evaluate($sourceExpr);
$attrList = [
	[
		'img' => 'http://slothsoft.dnsalias.net/getResource.php/minecraft/blocks/0.png',
		'id' => 0,
		'name' => 'Air',
		'style' => '',
	]
];
$attrExpr = [
	'img' => 'string(td[1]//img/@src)',
	'id' => 'number(td[2])',
	'name' => 'normalize-space(td[4])',
	'style' => 'concat("- ", td[1]//img/../@style)',
];
foreach ($sourceList as $sourceNode) {
	$attr = [];
	foreach ($attrExpr as $key => $expr) {
		$attr[$key] = trim($sourcePath->evaluate($expr, $sourceNode));
		if (!strlen($attr[$key])) {
			continue 2;
		}
	}
	$id = (int) $attr['id'];
	if (!isset($attrList[$id])) {
		$attrList[$id] = $attr;
	}
}

$dataRoot = $dataDoc->createDocumentFragment();
foreach ($attrList as $attr) {
	if (preg_match('/^(.+)\/thumb(.+?\.png)/', $attr['img'], $match)) {
		$attr['img'] = $match[1] . $match[2];
	}
	$node = $dataDoc->createElement('block');
	foreach ($attr as $key => $val) {
		$node->setAttribute($key, $val);
		$dataRoot->appendChild($node);
	}
}
return $dataRoot;