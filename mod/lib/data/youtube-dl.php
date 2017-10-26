<?php
use Slothsoft\CMS\HTTPFile;

$url = $this->httpRequest->getInputValue('url');

$ret = 'null';

if ($url) {
    $cmd = sprintf('youtube-dl %s -J', escapeshellarg($url));
    
    $res = `$cmd`; // http://de2.php.net/manual/de/language.operators.execution.php
    if (@json_decode($res, true)) {
        $ret = $res;
    }
}

return HTTPFile::createFromString($ret, 'youtube-dl.json');