<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\FileSystem;
use DOMXPath;
$downloadDirsList = [
    0 => [],
	'hentai.ms' => [
		'C:/NetzwerkDaten/pr0n/manga/hentai.ms/',
	],
    'pururin.us' => [
        'C:/NetzwerkDaten/pr0n/manga/pururin.us/'
    ],
	'nhentai.net' => [
		'C:/NetzwerkDaten/pr0n/manga/nhentai.net/',
	],
	//*/
    /*
 * 'hentai.ms' => [
 * 'C:/NetzwerkDaten/pr0n/manga/hentai.ms/',
 * ],
 * 'nhentai.net' => [
 * 'C:/NetzwerkDaten/pr0n/manga/nhentai.net/',
 * ],
 * 'ryuutama.com' => [
 * 'C:/NetzwerkDaten/pr0n/manga/ryuutama.com/',
 * ],
 * 'readhentaimanga.com' => [
 * 'C:/NetzwerkDaten/pr0n/manga/readhentaimanga.com/',
 * ],
 * //
 */
];

if ($this->httpRequest->hasInputValue('refresh-all')) {
    $ret = '';
    array_walk_recursive($downloadDirsList, function ($val, $key) use (&$ret) {
        $ret .= $val . PHP_EOL;
        FileSystem::asNode($val);
    });
    return HTTPFile::createFromString($ret);
}

if ($archive = $this->httpRequest->getInputValue('load-archive') and isset($downloadDirsList[$archive])) {} else {
    $archive = 0;
}
$downloadDirs = $downloadDirsList[$archive];

$tmpList = [];
foreach ($downloadDirs as $dir) {
    if ($dir = realpath($dir)) {
        $key = FileSystem::generateStorageKey($dir);
        $tmpList[$key] = $dir;
    }
}
$downloadDirs = $tmpList;

$requestedId = $this->httpRequest->getInputValue('id');
$verifyTree = $this->httpRequest->hasInputValue('refresh');

$retNode = $dataDoc->createDocumentFragment();
$dataRoot = $dataDoc->documentElement;
$dataPath = new \DOMXPath($dataDoc);
$storage = FileSystem::getStorage();

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
        $doc = FileSystem::asNode($dir);
    }
    if ($doc) {
        $xpath = new DOMXPath($doc);
        if (isset($this->httpRequest->input['xml'])) {
            // $dataDoc->replaceChild($newNode, $dataDoc->documentElement);
            return HTTPFile::createFromDocument($doc);
        }
        if ($requestedId) {
            $query = sprintf('//file[@id = "%s"]', $requestedId);
            // my_dump($query);
            $node = $xpath->evaluate($query)->item(0);
            if (! $node) {
                // my_dump($query);
                // echo $doc->saveXML();
                $this->httpResponse->setStatus(HTTPResponse::STATUS_NOT_FOUND);
                $this->progressStatus = self::STATUS_RESPONSE_SET;
                return;
            }
            
            $path = $node->getAttribute('path');
            $name = $node->getAttribute('name');
            $path = utf8_decode($path);
            $this->httpResponse->setDownload(false);
            return HTTPFile::createFromPath($path, $name);
        }
        
        $sort = false;
        if ($sort) {
            $nodeList = [];
            $tmpList = $xpath->evaluate('/folder/folder');
            foreach ($tmpList as $node) {
                $node->setAttribute('uri', str_replace('C:', '\\\\Dende', $node->getAttribute('path')));
                
                $avg = $xpath->evaluate('sum(file/@size-int) div count(file)', $node);
                // $avg = $xpath->evaluate('count(file)', $node);
                $nodeList[sprintf('%08d-%s', $avg, $node->getAttribute('id'))] = $node;
            }
            unset($tmpList);
            krsort($nodeList);
            // my_dump($nodeList);
        } else {
            $nodeList = [];
            $tmpList = $xpath->evaluate('/folder/folder');
            foreach ($tmpList as $node) {
                $node->setAttribute('uri', str_replace('C:', '\\\\Dende', $node->getAttribute('path')));
                
                $nodeList[$node->getAttribute('make-utc')] = $node;
            }
            ksort($nodeList);
        }
        
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
    $node->setAttribute('href', self::createRequestURI('/slothsoft/archive.hentai', self::LOOKUP_DATA, [
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