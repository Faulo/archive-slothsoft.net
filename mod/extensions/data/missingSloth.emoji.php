<?php
$repository = 'lib/twemoji-svg';

$docList = $this->getResourceDir($repository);
$keyList = array_keys($docList);
sort($keyList);







$doc = $this->getResourceDoc('core/unicode', 'xml');

$codeList = [];
$nodeList = $doc->documentElement->childNodes;
foreach ($nodeList as $node) {
	$codeList[strtolower($node->getAttribute('key'))] = strtolower($node->getAttribute('val'));
}

//$xpath = self::loadXPath($doc);


$retFragment = $dataDoc->createDocumentFragment();

/*
$rangeList = [];
$rangeList[] = [0x1F300, 0x1F53F];
$rangeList[] = [0x1F550, 0x1F6FF];
//$rangeList[] = [0x02600, 0x026FF];

$rowList = [];
foreach ($rangeList as $range) {
	$rangeStart = $range[0];
	$rangeEnd = $range[1];
	$row = [];
	for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
		$key = dechex($i);
		$val = isset($codeList[$key])
			? $codeList[$key]
			: '';
		//$query = sprintf('string(line[@key = "%s"]/@val)', strtoupper($key));
		//$val = $xpath->evaluate($query, $doc->documentElement);
		$row[$key] = $val;
		if ($i % 16 === 15) {
			if (strlen(implode('', $row))) {
				$rowList[] = $row;
			}
			$row = [];
		}
	}
}
//*/


$rowList = [];
//my_dump($codeList);
for ($j = 1; $j < 4; $j++) {
	foreach ($keyList as $i => $key) {
		$val = [];
		foreach (explode('-', $key) as $k) {
			$k = str_pad($k, 4, '0', STR_PAD_LEFT);
			if (isset($codeList[$k])) {
				$val[] = $codeList[$k];
			} else {
				$val[] = $k;
				//my_dump($k);
				//my_dump($key);
				//my_dump($codeList);
				//die();
			}
		}
		if (count($val) === $j) {
			//$query = sprintf('string(line[@key = "%s"]/@val)', strtoupper($key));
			//$val = $xpath->evaluate($query, $doc->documentElement);
			$row[$key] = $val;
			//if ($i % 16 === 15) {
			if (count($row) === 16) {
				$rowList[] = $row;
				$row = [];
			}
		}
	}
}
$rowList[] = $row;

foreach ($rowList as $row) {
	$rowNode = $dataDoc->createElement('row');
	foreach ($row as $key => $val) {
		$char = explode('-', $key);
		foreach ($char as &$c) {
			$c = html_entity_decode('&#x' . $c . ';');
		}
		unset($c);
		
		$cellNode = $dataDoc->createElement('cell');
		$cellNode->setAttribute('uri', sprintf('/getResource.php/%s/%s', $repository, $key));
		$cellNode->setAttribute('key', $key);
		$cellNode->setAttribute('val', implode('', $char));
		$cellNode->setAttribute('name', implode(' + ', $val));
		$rowNode->appendChild($cellNode);
	}
	$retFragment->appendChild($rowNode);
}

return $retFragment;