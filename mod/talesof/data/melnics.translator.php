<?php
// require_once '../../constants.php';

// $dataDoc = new DOMDocument();
// $dataRoot = $dataDoc->createElement('data');
// $dataDoc->appendChild($dataRoot);
$dataRoot = $dataDoc->createDocumentFragment();

$nlNode = $dataDoc->createTextNode('
');

$textFile = dirname(__FILE__) . '/melnics.translator.txt';

$rowList = file($textFile);

$charList = [];

for ($i = 0; $i < count($rowList); $i += 2) {
    $enList = explode(' ', $rowList[$i]);
    $meList = explode(' ', $rowList[$i + 1]);
    
    if (count($enList) !== count($meList)) {
        my_dump($enList);
        my_dump($meList);
        die();
    }
    foreach ($enList as $j => $en) {
        $en = trim($en);
        $me = trim($meList[$j]);
        
        if (! isset($charList[$en])) {
            $charList[$en] = [];
        }
        $charList[$en][] = $me;
    }
}
// my_dump($charList);
foreach ($charList as $en => $arr) {
    $node = $dataDoc->createElement('english');
    $node->setAttribute('name', $en);
    $node->appendChild($nlNode->cloneNode(true));
    $dataRoot->appendChild($node);
    foreach ($arr as $me) {
        $child = $dataDoc->createElement('melnics');
        $child->setAttribute('name', $me);
        $node->appendChild($nlNode->cloneNode(true));
        $node->appendChild($child);
    }
}
// $dataDoc->save('php://output');
// die();

// output($dataDoc);

return $dataRoot;