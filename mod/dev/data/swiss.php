<?php

$id = 'swiss-wertvoll';

$saveDir = sprintf('%sdev/res/%s/', $this->includeDir, $id);
$resName = sprintf('dev/%s', $id);

$resDoc = $this->getResourceDoc($resName, 'xml');
$resXPath = $this->loadXPath($resDoc);

if ($userName = $this->httpRequest->getInputValue('user')) {
	$saveFile = sprintf('%s%s.xml', $saveDir, $userName);
	
	$userDoc = new DOMDocument();
	if (file_exists($saveFile)) {
		$userDoc->load($saveFile);
	} else {
		$node = $userDoc->createElement('user');
		$node->setAttribute('name', $userName);
		$userDoc->appendChild($node);
	}
	
	$userXPath = $this->loadXPath($userDoc);
	
	$questionList = [];
	$answerList = $this->httpRequest->getInputValue('answer', []);
	$nodeList = $resXPath->evaluate('//question');
	foreach ($nodeList as $node) {
		$question = $node->getAttribute('name');
		$questionList[$question] = [];
	}
	
	foreach ($questionList as $key => &$val) {
		foreach ($questionList as $question => $tmp) {
			if ($key !== $question and !isset($questionList[$question][$key])) {
				$val[$question] = 0;
			}
		}
	}
	unset($val);
	
	foreach ($nodeList as $node) {
		$questionA = $node->getAttribute('name');
		foreach ($questionList[$questionA] as $questionB => $val) {
			$key = sprintf('%s-%s', $questionA, $questionB);
			$val = (int) $userXPath->evaluate(sprintf('string(//answer[@name = "%s"])', $key));
			if (isset($answerList[$key])) {
				$val = (int) $answerList[$key];
			}
			$childNode = $resDoc->createElement('answer');
			$childNode->setAttribute('key', $key);
			$childNode->setAttribute('name', $questionB);
			$childNode->textContent = $val;
			$node->appendChild($childNode);
		}
	}
	
	$resDoc->documentElement->appendChild($resDoc->importNode($userDoc->documentElement, true));
	
	return $resDoc;
	
	
	if ($answerList = $this->httpRequest->getInputValue('answer')) {
		$questionList = $this->httpRequest->getInputValue('question');
		foreach ($answerList as $key => $val) {
			$node = $userXPath->evaluate(sprintf('//answer[@name = "%s"]', $key))->item(0);
			if (!$node) {
				$node = $userDoc->createElement('answer');
				$node->setAttribute('name', $key);
				$userDoc->documentElement->appendChild($node);
			}
			$node->textContent = $val;
			if (isset($questionList[$key])) {
				$node->setAttribute('question', '');
			} else {
				$node->removeAttribute('question');
			}
		}
		$userDoc->save($saveFile);
	}
	
	return $userDoc;
}