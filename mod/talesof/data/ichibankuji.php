<?php

$dataRoot = $dataDoc->documentElement;
$retFragment = $dataDoc->createDocumentFragment();

$host = 'http://1kuji.bpnavi.jp';

if ($id = $this->httpRequest->getInputValue('id')) {
	$dataRoot->setAttribute('id', $id);
	
	$url = sprintf('/item/%s', $id);


	if ($xpath = \Storage::loadExternalXPath($host . $url, TIME_YEAR)) {
		$dataRoot->setAttribute('href', $host . $url);
		$dataRoot->setAttribute('name', $xpath->evaluate('string(//html:title)'));
		
		$productList = [];
		if ($nodeList = $xpath->evaluate(sprintf('//html:a[starts-with(@href, "%s")]', $url))) {
			foreach ($nodeList as $node) {
				if ($val = $node->getAttribute('href')) {
					$val = substr($val, strlen($url));
					$itemId = null;
					if (preg_match('/(\w+)/', $val, $match)) {
						$itemId = $id;
						$itemType = $match[1];
					}
					if (preg_match('/(\d+)/', $val, $match)) {
						$itemId = $match[1];
						$itemType = 'item';
					}
					if ($itemId) {
						$val = sprintf('%s/petit?t=%s&id=%s', $host, $itemType, $itemId);
						$productList[$val] = null;
					}
				}
			}
		}
		//my_dump($productList);
		//*
		if ($nodeList = $xpath->evaluate('//*[@id="wrapper"]//html:a[starts-with(@href, "/petit")]')) {
			foreach ($nodeList as $node) {
				if ($val = $node->getAttribute('href')) {
					$val = $host . $val;
					$productList[$val] = null;
					
					$val = parse_url($val, PHP_URL_QUERY);
					parse_str($val, $arr);
					switch ($arr['t']) {
						case 'double':
						case 'item':
							$val = sprintf('%s/%d', $url, $arr['id']);
							//$productList[$val] = null;
							break;
					}
				}
			}
		}
		//*/
		//my_dump($productList);
		if (!$productList) {
			return $xpath->document;
		}
		$itemNodeList = [];
		foreach ($productList as $url => $tmp) {
			parse_str(parse_url($url, PHP_URL_QUERY), $arr);
			$type = isset($arr['t'])
				? $arr['t']
				: 'unknown';
			if ($xpath = \Storage::loadExternalXPath($url, TIME_YEAR)) {
				//if ($nodeList = $xpath->evaluate('//*[@id="itemDetail"]//html:img')) {
				if ($nodeList = $xpath->evaluate('//*[@id="column"]//html:img')) {
					
					foreach ($nodeList as $node) {
						$name = $node->getAttribute('alt');
						$image = $node->getAttribute('src');
						$image = str_replace('large_jpg/', '', $image);
						
						if ($name) {
							$itemNode = $dataDoc->createElement('item');
							$itemNode->setAttribute('href', $url);
							$itemNode->setAttribute('name', $name);
							$itemNode->setAttribute('image', $image);
							$itemNode->setAttribute('type', $type);
							
							$itemNodeList[$type.$image] = $itemNode;
						}
					}
				}
			}
		}
		foreach ($itemNodeList as $itemNode) {
			$retFragment->appendChild($itemNode);
		}
	}
}

return $retFragment;