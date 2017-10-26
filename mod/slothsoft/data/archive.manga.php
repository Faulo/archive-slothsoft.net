<?php
namespace Slothsoft\CMS;

$downloadDirsList = [
    0 => [],
    'OnePiece' => [
        'C:/NetzwerkDaten/Manga/One Piece/'
    ]
];

if ($archive = $this->httpRequest->getInputValue('load-archive') and isset($downloadDirsList[$archive])) {} else {
    $archive = 0;
}
$downloadDirs = $downloadDirsList[$archive];

$tmpList = [];
foreach ($downloadDirs as $dir) {
    if ($dir = realpath($dir)) {
        $key = \FileSystem::generateStorageKey($dir);
        $tmpList[$key] = $dir;
    }
}
$downloadDirs = $tmpList;

$verifyTree = ! $this->httpRequest->getInputValue('id');
$verifyTree = false;

$retNode = $dataDoc->createDocumentFragment();
$dataRoot = $dataDoc->documentElement;
$dataPath = new \DOMXPath($dataDoc);
$storage = \FileSystem::getStorage();

$count = 0;
$limit = (int) $this->httpRequest->getInputValue('limit', 10);
$page = (int) $this->httpRequest->getInputValue('page', 1);
$page --;

foreach ($downloadDirs as $key => $dir) {
    $doc = null;
    if (! $verifyTree and $doc = $storage->retrieveDocument($key, 0)) {
        // $fragment = $storage->retrieveXML($key, 0, $dataDoc)
        // $newNode = $fragment->firstChild;
    } else {
        $doc = \FileSystem::asNode($dir);
    }
    if ($doc) {
        $xpath = new \DOMXPath($doc);
        if (isset($this->httpRequest->input['xml'])) {
            // $dataDoc->replaceChild($newNode, $dataDoc->documentElement);
            return \CMS\HTTPFile::createFromDocument($doc);
        }
        if ($id = $this->httpRequest->getInputValue('id')) {
            $query = sprintf('//file[@id = "%s"]', $id);
            // my_dump($query);
            $nodeList = $xpath->evaluate($query);
            $node = $nodeList->item(0);
            if (! $node) {
                // my_dump($query);
                // echo $doc->saveXML();
                $this->httpResponse->setStatus(\CMS\HTTPResponse::STATUS_NOT_FOUND);
                $this->progressStatus = self::STATUS_RESPONSE_SET;
                return;
            }
            
            $path = $node->getAttribute('path');
            $name = $node->getAttribute('name');
            $path = utf8_decode($path);
            $this->httpResponse->setDownload(false);
            return \CMS\HTTPFile::createFromPath($path, $name);
        }
        
        $nodeList = [];
        $tmpList = $xpath->evaluate('/folder/folder');
        foreach ($tmpList as $node) {
            $node->setAttribute('uri', str_replace('D:', '\\\\Dende\\NetzwerkDaten', $node->getAttribute('path')));
            // $avg = $xpath->evaluate('sum(file/@size-int) div count(file)', $node);
            // $avg = $xpath->evaluate('count(file)', $node);
            $nodeList[$node->getAttribute('name')] = $node;
        }
        unset($tmpList);
        ksort($nodeList);
        
        // my_dump($nodeList);
        
        $offset = $page * $limit;
        $count = count($nodeList);
        
        $nodeList = array_slice($nodeList, $offset, $limit);
        
        foreach ($nodeList as $node) {
            $retNode->appendChild($dataDoc->importNode($node, true));
        }
        unset($nodeList);
    }
}

$nodeList = $dataPath->evaluate('.//file', $retNode);
foreach ($nodeList as $node) {
    $node->setAttribute('href', self::createRequestURI('/slothsoft/archive.manga', self::LOOKUP_DATA, [
        'id' => $node->getAttribute('id'),
        'load-archive' => $archive
    ]));
}

for ($i = 0; $i < ceil($count / $limit); $i ++) {
    $node = $dataDoc->createElement('page');
    if ($i === $page) {
        $node->setAttribute('active', 'active');
    }
    $node->setAttribute('uri', sprintf('?page=%d', $i + 1));
    $retNode->appendChild($node);
}

return $retNode;