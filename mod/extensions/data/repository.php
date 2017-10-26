<?php

$HASHFUNC_BIN = 'sha512';

$retNode = null;

if ($id = $this->httpRequest->getInputValue('id')) {
	$retNode = $dataDoc->createElement('repository');
	$retNode->setAttribute('id', $id);
	
	$xpiList = $this->getResourceDir(sprintf('extensions/%s', $id), 'status');
	$rdfList = $this->getResourceDir(sprintf('extensions/%s-manifest', $id), 'xml');
	
	if (count($xpiList) === count($rdfList)) {
		$xpiList = array_values($xpiList);
		$rdfList = array_values($rdfList);
		
		foreach ($xpiList as $i => $xpiDoc) {
			$rdfDoc = $rdfList[$i];
			//$rdfPath = self::loadXPath($rdfDoc);
			//$rdfPath->registerNamespace('em', self::NS_EM);
			
			//$version = $rdfPath->evaluate('string(.//em:version)');
			
			$xpiFile = $xpiDoc->documentElement->getAttribute('realpath');
			$xpiLink = sprintf('http://%s%s', $this->requestedDomain->getAttribute('name'), $xpiDoc->documentElement->getAttribute('uri'));
			$hash = hash_file($HASHFUNC_BIN, $xpiFile);
			
			$arr = [];
			$arr['bin-path'] = $xpiFile;
			$arr['bin-uri'] = $xpiLink;
			$arr['bin-hash-val'] = $hash;
			$arr['bin-hash-alg'] = $HASHFUNC_BIN;
			
			$packageNode = $dataDoc->createElement('package');
			foreach ($arr as $key => $val) {
				$packageNode->setAttribute($key, $val);
			}
			$packageNode->appendChild($dataDoc->importNode($rdfDoc->documentElement->firstChild, true));
			$retNode->appendChild($packageNode);
		}
		//$dataDoc->documentElement->appendChild($retNode);
		//output($dataDoc);
		//die();
	}
}

return $retNode;