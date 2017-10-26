<?php
namespace Slothsoft\CMS;

$explodeArray = function (array $explodeList, $str) {
    $retList = [
        $str
    ];
    foreach ($explodeList as $ex) {
        $tmpList = [];
        foreach ($retList as $kana) {
            $tmpList = array_merge($tmpList, explode($ex, $kana));
        }
        $retList = $tmpList;
    }
    return $retList;
};
$randArray = function (array $randomArr, array $excludeList = []) {
    $ret = null;
    $randomCount = count($randomArr);
    if ($randomCount > count($excludeList)) {
        $randomCount --;
        do {
            $ret = $randomArr[rand(0, $randomCount)];
        } while (in_array($ret, $excludeList, true));
    }
    return $ret;
};

// my_dump(isset($this->httpRequest->input['jlpt-level']));
if (isset($this->httpRequest->input['jlpt-level'])) {
    $attr = 'isJLPT';
    $level = isset($this->httpRequest->input['jlpt-level']) ? $this->httpRequest->input['jlpt-level'] : 5;
    $sourceURI = 'http://www.tanos.co.uk/jlpt/jlpt%d/vocab/';
    $uri = sprintf($sourceURI, $level);
    
    // $tmpDoc = new \DOMDocument();
    // @$tmpDoc->loadHTMLFile($uri);
    $tmpDoc = $this->getExternalDocument($uri, 'html');
}

if (isset($this->httpRequest->input['vocab-resource'])) {
    $attr = 'isPersonal';
    $doc = $this->httpRequest->input['vocab-resource'];
    $tmpDoc = $this->getResourceDoc($doc, 'xml');
}

$tmpPath = self::loadXPath($tmpDoc);

$dataRoot = $dataDoc->createElement('vocabulary');
$dataRoot->setAttribute($attr, '');

$translator = new \Lang\TranslatorJaEn();

$expr = '//html:table[@border="1"]//html:tr[count(html:td) = 3 and string-length(html:td[3]) > 1]';
$tmpNodeList = $tmpPath->evaluate($expr);

$explodeList = [
    '/',
    '、'
];
foreach ($tmpNodeList as $tmpNode) {
    $found = [
        'kana' => 2,
        'kanji' => 1,
        'english' => 3
    ];
    foreach ($found as &$val) {
        $val = trim($tmpPath->evaluate(sprintf('normalize-space(html:td[%d])', $val), $tmpNode));
    }
    unset($val);
    
    $kanjiList = $explodeArray($explodeList, $found['kanji']);
    $kanaList = $explodeArray($explodeList, $found['kana']);
    foreach ($kanjiList as $kanji) {
        $found['kanji'] = trim($kanji);
        foreach ($kanaList as $kana) {
            $found['kana'] = trim($kana);
            $node = $translator->addWord([
                $found
            ]);
            $node->firstChild->firstChild->setAttribute('player-uri', $tmpPath->evaluate('normalize-space(@audio)', $tmpNode));
            if (preg_match('/[\w\s]+/', $kana, $match)) {
                $node->firstChild->firstChild->setAttribute('dict-uri', 'http://www.dict.cc/?' . http_build_query([
                    's' => trim($match[0])
                ]));
            }
            
            $dataRoot->appendChild($dataDoc->importNode($node, true));
        }
    }
    // my_dump($found);
}

if (isset($this->httpRequest->input['test-count'])) {
    
    $newFragment = $dataDoc->createDocumentFragment();
    
    $maxCount = (int) $this->httpRequest->input['test-count'];
    $dataPath = self::loadXPath($dataDoc);
    $nodeList = $dataPath->evaluate('.//english', $dataRoot);
    $wordList = [];
    foreach ($nodeList as $node) {
        $wordList[] = $node;
    }
    $wordCount = count($wordList);
    
    if (isset($this->httpRequest->input['vocab-test'])) {
        $newRoot = $dataDoc->createElement('testResult');
        $testList = $this->httpRequest->input['vocab-test'];
        foreach ($testList as $id => $answer) {
            if (isset($wordList[$id])) {
                $childNode = $wordList[$id]->parentNode->parentNode->parentNode->cloneNode(true);
                $childNode->setAttribute('input', $answer);
                $newRoot->appendChild($childNode);
            }
        }
        $newFragment->appendChild($newRoot);
    }
    
    $maxCount = min($maxCount, count($wordList));
    $randomList = [];
    while (count($randomList) < $maxCount) {
        $word = rand(0, $wordCount);
        if (isset($wordList[$word]) and ! isset($randomList[$word])) {
            $wordNode = $wordList[$word];
            $wordNode->setAttribute('correct', '');
            $parentNode = $wordNode->parentNode;
            $selectList = [
                $wordNode
            ];
            for ($i = 0; $i < 4; $i ++) {
                $selectList[] = $randArray($wordList, $selectList);
            }
            shuffle($selectList);
            foreach ($selectList as $node) {
                if ($node) {
                    $parentNode->appendChild($node->cloneNode(true));
                }
            }
            $parentNode->removeChild($wordNode);
            $randomList[$word] = $parentNode->parentNode->parentNode;
            $randomList[$word]->setAttribute('id', $word);
            // $randomList[$word] = $dataDoc->saveXML($randomList[$word]);
        }
    }
    $newRoot = $dataDoc->createElement('test');
    foreach ($randomList as $node) {
        $newRoot->appendChild($node);
    }
    $newFragment->appendChild($newRoot);
    $dataRoot = $newFragment;
}

// return $translator->getDocument();

return $dataRoot;