<?php
namespace Slothsoft\CMS;

use Slothsoft\Core\XMLHttpRequest;

$uri = 'https://ko89.net';
$method = 'GET';
$data = null;

// $doc = self::loadExternalDocument($url);

$req = new XMLHttpRequest();
$req->open($method, $uri);
$req->send($data);

my_dump($req->getAllResponseHeaders());