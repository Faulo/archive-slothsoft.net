<?php
namespace Slothsoft\CMS;

use Slothsoft\Lang\Vocabulary;

$retFragment = $dataDoc->createDocumentFragment();
$dataPath = self::loadXPath($dataDoc);
$vocab = new Vocabulary($dataPath);
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

$tmpDoc = null;


if ($userKey = $this->httpRequest->getInputValue('vocab-resource')) {
	$resourceURI = self::createRequestURI('/slothsoft/kana.vocab', self::LOOKUP_DATA, ['vocab-resource' => $userKey]);
	$logURI = self::createRequestURI('/slothsoft/vocab-words-log', self::LOOKUP_FRAGMENT, ['vocab-resource' => $userKey]);
	$attr = 'isPersonal';
	$attrVal = $userKey;
	$userListKey = $userKey . '/list';
	$userTestsKey = $userKey . '/times';
	$tmpDoc = $this->getResourceDoc($userKey, 'xml');
} elseif ($level = $this->httpRequest->getInputValue('jlpt-level', 5)) {
	$resourceURI = self::createRequestURI('/slothsoft/kana.vocab', self::LOOKUP_DATA, ['jlpt-level' => $level]);
	$attr = 'isJLPT';
	$attrVal = $level;
	$sourceURI = 'http://www.tanos.co.uk/jlpt/jlpt%d/vocab/';
	$resourceKey = 'slothsoft/vocab-jlpt';
	$key = sprintf('%d.vocab', $level);
	$tmpDocList = $this->getResourceDir($resourceKey, 'xml');
	if (isset($tmpDocList[$key])) {
		$tmpDoc = $tmpDocList[$key];
	}
	//$uri = sprintf($sourceURI, $level);
	//$tmpDoc = self::loadExternalDocument($uri, 'html', TIME_MONTH);
	//my_dump($tmpDoc);
}
if ($userListKey) {
	$vocab->setProgressList($this->session->getGlobalValue($userListKey, []));
}
if ($userListKey) {
	$vocab->setDateList($this->session->getGlobalValue($userTestsKey, []));
}

if ($tmpDoc) {
	$tmpPath = self::loadXPath($tmpDoc);
	
	$expr = '//html:table[@border="1"][.//html:tr[count(html:td) >= 3 and string-length(html:td[3]) > 1]]';
	$tmpNodeList = $tmpPath->evaluate($expr);

	foreach ($tmpNodeList as $tmpNode) {
		switch ($attr) {
			case 'isJLPT':
				$tmpNode->getElementsByTagName('th')->item(0)->setAttribute('xml:lang', 'ja-jp');
				$tmpNode->getElementsByTagName('th')->item(1)->setAttribute('xml:lang', 'en-us');
				break;
		}
		$node = $vocab->loadTable($tmpPath, $tmpNode);
		$node->setAttribute($attr, $attrVal);
		if ($userSaveable) {
			$node->setAttribute('saveable', '');
		}
		$retFragment->appendChild($node);
	}

	if ($userSaveable) {
		if ($groupName = $this->httpRequest->getInputValue('vocab-new-submit') and $newList = $this->httpRequest->getInputValue('vocab-new')) {
			foreach ($newList as $arr) {
				$expr = sprintf('//html:tbody[@name = "%s"]', $groupName);
				$nodeList = $tmpPath->evaluate($expr);
				foreach ($nodeList as $parentNode) {
					$vocab->addVocable($parentNode, $arr);
				}
			}
			$this->setResourceDoc($userKey, $tmpDoc);
			//output($tmpDoc);
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
		$this->httpResponse->setStatus(HTTPResponse::STATUS_NO_CONTENT);
		$this->progressStatus = self::STATUS_RESPONSE_SET;
	}
	if ($testList = $this->httpRequest->getInputValue('vocab-test')) {
		$resultNode = $vocab->generateTestResult(
			$lang,
			$testList
		);
		$retFragment->appendChild($resultNode);
	}
	if (!$resultNode) {
		$testNode = $vocab->generateTest(
			$lang,
			$this->httpRequest->getInputValue('test-count'),
			$this->httpRequest->getInputValue('test-type')
		);
		$testNode->setAttribute('mode', 'words');
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