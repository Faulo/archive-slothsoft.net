<?php
namespace Slothsoft\CMS;

$retNode = $dataDoc->createDocumentFragment();

$letterList = array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9), [
    '!',
    '?',
    '.',
    ',',
    '"',
    '\''
]);

$cleanseDoc = true;

$tempDoc = $this->getResourceDoc('core/unicode');
$path = $tempDoc->documentElement->getAttribute('realpath');
$rowList = \FileSystem::loadCSV($path, ';');

$ret = '';
$unicodeList = [];
foreach ($rowList as $row) {
    $id = $row[0];
    $name = $row[1];
    $alias = $row[10];
    if ($name === '<control>') {
        continue;
    }
    if (strlen($alias)) {
        // $name = $alias;
    }
    $value = html_entity_decode(sprintf('&#x%s;', $id), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $name = str_replace(' LETTER ', ' ', $name);
    $name = str_replace('LATIN ', '', $name);
    if (preg_match('/WHITE .+ ORNAMENT/', $name)) {
        // $ret .= sprintf('%s %s %s%s', $id, $value, $name, PHP_EOL);
    }
    // if (isset($unicode[$name])) {
    // $ret .= sprintf('%s %s %s%s', $id, $value, $name, PHP_EOL);
    // }
    $unicodeList[] = [
        'char' => $value,
        'name' => $name,
        'alias' => $alias
    ];
}
if (strlen($ret)) {
    return \CMS\HTTPFile::createFromString($ret);
}

foreach ($letterList as &$letter) {
    foreach ($unicodeList as $unicode) {
        if ($unicode['char'] === (string) $letter) {
            $letter = $unicode;
            continue 2;
        }
    }
    my_dump($letter);
    die();
}
unset($letter);

$unicodeDoc = $this->getResourceDoc('slothsoft/unicode-mapping', 'xml');
$fontNodeList = $unicodeDoc->getElementsByTagName('font');
foreach ($fontNodeList as $fontNode) {
    $fontName = $fontNode->getAttribute('name');
    $patternList = [];
    foreach ($fontNode->getElementsByTagName('pattern') as $node) {
        $patternList[] = $node->getAttribute('name');
    }
    $exceptionList = [];
    foreach ($fontNode->getElementsByTagName('exception') as $node) {
        $exceptionList[$node->getAttribute('source')] = $node->getAttribute('target');
    }
    
    foreach ($letterList as $sourceLetter) {
        $targetLetter = null;
        $sourceLetterParts = explode(' ', $sourceLetter['name']);
        $sourceLetterParts[] = 'UNKNOWN';
        $sourceLetterParts[] = 'UNKNOWN';
        
        if (isset($exceptionList[$sourceLetter['char']])) {
            $pattern = $exceptionList[$sourceLetter['char']];
            foreach ($unicodeList as $unicode) {
                if ($unicode['name'] === $pattern or $unicode['alias'] === $pattern) {
                    $targetLetter = $unicode;
                    break 1;
                }
            }
        } else {
            foreach ($patternList as $pattern) {
                $pattern = sprintf($pattern, $sourceLetter['name'], $sourceLetterParts[0], $sourceLetterParts[1]);
                foreach ($unicodeList as $unicode) {
                    if ($unicode['name'] === $pattern or $unicode['alias'] === $pattern) {
                        $targetLetter = $unicode;
                        break 2;
                    }
                }
            }
        }
        if ($targetLetter) {
            $letterNode = $unicodeDoc->createElement('letter');
            $letterNode->setAttribute('source', $sourceLetter['char']);
            $letterNode->setAttribute('target', $targetLetter['char']);
            $fontNode->appendChild($letterNode);
        } else {
            $letterNode = $unicodeDoc->createElement('letter');
            $letterNode->setAttribute('source', $sourceLetter['char']);
            $letterNode->setAttribute('target', $sourceLetter['char']);
            $fontNode->appendChild($letterNode);
            
            $letterNode = $unicodeDoc->createElement('missing');
            $letterNode->setAttribute('source', $sourceLetter['char']);
            $letterNode->setAttribute('target', $sourceLetter['name']);
            $fontNode->appendChild($letterNode);
            // my_dump($sourceLetter);
        }
    }
}

if ($cleanseDoc) {
    $deleteNodeList = [];
    foreach ($unicodeDoc->getElementsByTagName('font') as $fontNode) {
        foreach ($fontNode->childNodes as $childNode) {
            if (! ($childNode->nodeType === XML_ELEMENT_NODE and $childNode->tagName === 'letter')) {
                $deleteNodeList[] = $childNode;
            }
        }
    }
    foreach ($deleteNodeList as $deleteNode) {
        $deleteNode->parentNode->removeChild($deleteNode);
    }
}

$this->setResourceDoc('slothsoft/unicode-mapper', $unicodeDoc);

return $unicodeDoc;