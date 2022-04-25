<?php
namespace Slothsoft\CMS;

return new HTTPClosure([
    'isThreaded' => true
], function () use ($dataDoc) {    
    $resourcePath = '/mtg/sites.prerelease';
    
    $retFragment = $dataDoc->createDocumentFragment();
    
    $resDoc = $this->getResourceDoc('/mtg/prerelease', 'xml');
    $templateDoc = $this->getTemplateDoc('/mtg/sites.prerelease');
    $dom = new \Slothsoft\Core\DOMHelper();
    
	$sitesNode = $dom->transformToFragment($resDoc, $templateDoc, $dataDoc);
	$retFragment->appendChild($sitesNode);
    
    $dataDoc->documentElement->appendChild($retFragment);
    
    $res = $this->setResourceDoc($resourcePath, $dataDoc->documentElement);
    
    $ret = sprintf('Saved %d bytes to %s!', $res, $this->getResourcePath($resourcePath));
    
    return \Slothsoft\CMS\HTTPFile::createFromString($ret);
});