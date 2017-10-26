<?php
$retFragment = $dataDoc->createDocumentFragment();
$dataPath = self::loadXPath($dataDoc);
$vocab = new \Lang\Vocabulary($dataPath);
$vocab->setTime($this->httpRequest->time);

$userKey = null;
$resourceURI = null;
$logURI = null;
$userList = [];
$userTests = [];
$userListKey = null;
$userTestsKey = null;
$userSaveable = $this->httpRequest->getInputValue('test-saveable', 0);

$attr = 'isUnknown';
$attrVal = '';

$filterText = null;

if ($userKey = $this->httpRequest->getInputValue('vocab-resource')) {
	$resourceURI = self::createRequestURI('/slothsoft/vocab.kanji', self::LOOKUP_DATA, ['vocab-resource' => $userKey]);
	$logURI = self::createRequestURI('/slothsoft/vocab-kanji-log', self::LOOKUP_FRAGMENT, ['vocab-resource' => $userKey]);
	$attr = 'isPersonal';
	$attrVal = $userKey;
	$userListKey = $userKey . '/kanji-list';
	$userTestsKey = $userKey . '/kanji-times';
	$tmpDoc = $this->getResourceDoc($userKey, 'xml');
	$filterText = $tmpDoc->textContent;
}
if ($userListKey) {
	$vocab->setProgressList($this->session->getGlobalValue($userListKey, []));
}
if ($userListKey) {
	$vocab->setDateList($this->session->getGlobalValue($userTestsKey, []));
}
if ($filterText) {
	//$vocab->setWordFilter($filterText);
	$resNode = $vocab->loadKanjiText($filterText);
	$retFragment->appendChild($resNode);
} else {
	//JLPT
	if ($level = $this->httpRequest->getInputValue('jlpt-level', 0)) {
		$resourceURI = self::createRequestURI('/slothsoft/vocab.kanji', self::LOOKUP_DATA, ['jlpt-level' => $level]);
		if (!is_array($level)) {
			$level = [$level];
		}
		$attr = 'isJLPT';
		$attrVal = $level;
		$sourceURI = 'http://www.tanos.co.uk/jlpt/jlpt%d/kanji/';
		$resourceKey = 'slothsoft/vocab-jlpt';
		$sourceKey = '%d.kanji';
		
		$tmpDocList = $this->getResourceDir($resourceKey, 'xml');
		$tmpDoc = null;
		$tmpPath = null;
		
		foreach ($level as $l) {
			$key = sprintf($sourceKey, $l);
			if (isset($tmpDocList[$key])) {
				if ($tmpDoc) {
					$tmpDoc->documentElement->appendChild($tmpDoc->importNode($tmpDocList[$key]->documentElement, true));
				} else {
					$kanjiUri = sprintf($sourceURI, $l);
					$tmpDoc = $tmpDocList[$key];
					$tmpPath = self::loadXPath($tmpDoc);
				}
			}
		}
		/*
		if (is_array($level)) {
			$lvl = array_shift($level);
			$kanjiUri = sprintf($sourceURI, $lvl);
			if ($tmpDoc = self::loadExternalDocument($kanjiUri, null, TIME_MONTH)) {
				$tmpPath = self::loadXPath($tmpDoc);
				
				foreach ($level as $lvl) {
					if ($tmp = self::loadExternalDocument(sprintf($sourceURI, $lvl), null, TIME_MONTH)) {
						$tmpDoc->documentElement->appendChild($tmpDoc->importNode($tmp->documentElement, true));
					}
				}
			}
		} else {
			$kanjiUri = sprintf($sourceURI, $level);
			if ($tmpDoc = self::loadExternalDocument($kanjiUri, null, TIME_MONTH)) {
				$tmpPath = self::loadXPath($tmpDoc);
			}
		}
		//*/
		if ($tmpPath) {
			$resNode = $vocab->loadKanjiDocument($tmpPath);
			$resNode->setAttribute('kanji-uri', $kanjiUri);
			$retFragment->appendChild($resNode);
		}
	}
}
if ($lang = $this->httpRequest->getInputValue('test-language')) {
	$resultNode = null;
	if ((int) $this->httpRequest->getInputValue('vocab-test-json')) {
		$testList = $this->httpRequest->getInputJSON();
		$resultNode = $vocab->generateTestResult(
			$lang,
			$testList
		);
		$this->httpResponse->styleFiles = [];
		$this->httpResponse->scriptFiles = [];
		$this->httpResponse->setBody('');
		$this->httpResponse->setStatus(\CMS\HTTPResponse::STATUS_NO_CONTENT);
		$this->progressStatus = self::STATUS_RESPONSE_SET;
	}
	if ($testList = $this->httpRequest->getInputValue('vocab-test')) {
		$resultNode = $vocab->generateTestResult(
			$lang,
			$testList
		);
		$retFragment->appendChild($resultNode);
	}
	if ((int) $this->httpRequest->getInputValue('vocab-brutefoce')) {
		$nodeList = [];
		for ($i = 0; $i < 10000; $i++) {
			set_time_limit(60);
			$testNode = $vocab->generateTest(
				$lang,
				$this->httpRequest->getInputValue('test-count'),
				$this->httpRequest->getInputValue('test-type')
			);
			$nodeList[] = $testNode;
			printf('%03d: %s%s', $i, $testNode->ownerDocument->saveXML($testNode->cloneNode()), PHP_EOL);
		}
		die();
	}
	if (!$resultNode) {
		/*
		$titleNode = $this->requestElement->ownerDocument->createElement('page');
		$titleNode->setAttribute('title', '?');
		$this->requestElement->appendChild($titleNode);
		//*/
		$testNode = $vocab->generateTest(
			$lang,
			$this->httpRequest->getInputValue('test-count'),
			$this->httpRequest->getInputValue('test-type')
		);
		$testNode->setAttribute('mode', 'kanji');
		//$testNode->setAttribute('resource', $userKey);
		$testNode->setAttribute('resource-uri', $resourceURI);
		$testNode->setAttribute('log-uri', $logURI);
		$retFragment->appendChild($testNode);
	}
}

if ($userKey) {
	if ($dateNode = $vocab->generateTestDates()) {
		$retFragment->appendChild($dateNode);
	}
}
if ($userSaveable and $userListKey) {
	$this->session->setGlobalValue($userListKey, $vocab->getProgressList());
}
if ($userSaveable and $userListKey) {
	$this->session->setGlobalValue($userTestsKey, $vocab->getDateList());
}

return $retFragment;