<?php
namespace w3c;

interface XMLHttpRequest extends XMLHttpRequestEventTarget
{

    // states
    const UNSENT = 0;

    const OPENED = 1;

    const HEADERS_RECEIVED = 2;

    const LOADING = 3;

    const DONE = 4;

    /*
     * public $onreadystatechange;
     * public $readyState = self::UNSENT;
     * public $timeout;
     * public $withCredentials;
     * public $upload;
     * public $status = 0;
     * public $statusText = '';
     * public $responseType;
     * public $response;
     * public $responseText;
     * public $responseXML;
     */
    public function open($method, $url, $async = true, $user = null, $password = null);

    public function setRequestHeader($header, $value);

    public function send($data = null);

    public function abort();

    public function overrideMimeType($mime);

    public function getResponseHeader($header);

    public function getAllResponseHeaders();
}
;
?>