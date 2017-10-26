<?php
namespace Slothsoft\CMS;

use Slothsoft\Game\Name;

// my_dump($this->httpRequest->getInputValue('vct-create'));
$retNode = null;

if ($name = $this->httpRequest->getInputValue('name')) {
    $retNode = $dataDoc->createElement('vct');
    $retNode->setAttribute('room', $name);
    
    $this->httpRequest->setInputValue('chat-database', 'vct.' . $name);
} else {
    $name = md5($_SERVER['REQUEST_TIME_FLOAT'] . '-' . $_SERVER['REMOTE_ADDR']);
    $config = [];
    $config[Name::GENERATE_CONFIG_FIRSTNAMEONLY] = 0;
    $config[Name::GENERATE_CONFIG_ALLITERATION] = 0;
    if ($list = Name::generate($config)) {
        $tmp = reset($list);
        $tmp = strstr($tmp, ' ');
        $tmp = trim($tmp);
        if ($tmp) {
            $name = $tmp;
        }
    }
    $url = $this->requestedPage->getAttribute('url');
    $href = sprintf('%s?name=%s', $url, $name);
    
    $this->httpResponse->setRedirect($href);
    $this->progressStatus = self::STATUS_RESPONSE_SET;
}

return $retNode;