<?php
/*
$doc = $this->getResourceDoc('slothsoft/kana', 'xml');
$xpath = new \DOMXPath($doc);
$nodeList = $xpath->evaluate('//kana[not(@skip)]/hiragana');
$latinList = [];
$kanaList = [];
foreach ($nodeList as $node) {
	$latin = $node->parentNode->getAttribute('latin');
	$kana = $node->getAttribute('name');
	if (!isset($latinList[$latin])) {
		$latinList[$latin] = $kana;
	}
	if (!isset($kanaList[$kana])) {
		$kanaList[$kana] = $latin;
	}
}
foreach ($kanaList as $key => $val) {
	echo "'$key' => '$val', ";
}
//*/


$dataRoot = $dataDoc->documentElement;

$grammarDoc = $this->getResourceDoc('slothsoft/grammar-ja', 'xml');

$grammar = new \Lang\GrammarJa();
$grammar->init($grammarDoc);

$vocabDoc = $this->getDataDoc('slothsoft/kana.vocab');
$vocabPath = new \DOMXPath($vocabDoc);

$formList = $grammar->getConjugationFormList();
foreach ($formList as $form) {
	$node = $dataDoc->createElement('form');
	$node->setAttribute('name', $form);
	$dataRoot->appendChild($node);
}

$wordList = $vocabPath->evaluate('.//group[@name = "Verben"]//word[lang("ja")]');
$conjugationList = [];
$conjugationList['vs'] = [];
$conjugationList['v5u'] = [];
$conjugationList['v5k'] = [];
$conjugationList['v5g'] = [];
$conjugationList['v5s'] = [];
$conjugationList['v5t'] = [];
$conjugationList['v5n'] = [];
$conjugationList['v5b'] = [];
$conjugationList['v5m'] = [];
$conjugationList['v5r'] = [];
$conjugationList['v1'] = [];
foreach ($wordList as $word) {
	if ($conjugation = $grammar->conjugateWord($word)) {
		$conjugation = $dataDoc->importNode($conjugation, true);
		$type = $conjugation->getAttribute('type');
		if (!isset($conjugationList[$type])) {
			$conjugationList[$type] = [];
		}
		$conjugationList[$type][] = $conjugation;
	}
}
foreach ($conjugationList as $type => $childList) {
	$node = $dataDoc->createElement('conjugationGroup');
	$node->setAttribute('type', $type);
	foreach ($childList as $child) {
		$node->appendChild($child);
	}
	$dataRoot->appendChild($node);
}


//output($dataDoc);