<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\DOMHelper;
use DOMDocument;

$pollName = $this->httpRequest->getInputValue('poll');
$userName = $this->httpRequest->getInputValue('user');

if ($pollName and $userName) {
    $saveDir = sprintf('%sdev/res/poll-%s/', $this->includeDir, $pollName);
    $pollFile = sprintf('%s_index.xml', $saveDir);
    $saveFile = sprintf('%s%s.xml', $saveDir, $userName);
    
    $pollDoc = DOMHelper::loadDocument($pollFile);
    
    $userDoc = new DOMDocument();
    if (file_exists($saveFile)) {
        $userDoc->load($saveFile);
    } else {
        $node = $userDoc->createElement('user');
        $node->setAttribute('name', $userName);
        $userDoc->appendChild($node);
    }
    
    $userXPath = $this->loadXPath($userDoc);
    if ($answerList = $this->httpRequest->getInputValue('answer')) {
        $questionList = $this->httpRequest->getInputValue('question');
        foreach ($answerList as $key => $val) {
            $node = $userXPath->evaluate(sprintf('//answer[@name = "%s"]', $key))->item(0);
            if (! $node) {
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
    
    $pollDoc->documentElement->appendChild($pollDoc->importNode($userDoc->documentElement, true));
    
    return $pollDoc;
}