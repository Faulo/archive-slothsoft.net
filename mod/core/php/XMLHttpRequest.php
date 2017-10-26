<?php
/***********************************************************************
 * \XMLHttpRequest v1.01 25.07.2014 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.01 25.07.2014
 *			requires PHP 5.5
 *			public $followRedirects = true;
 *			public $maxRedirects = 10;
 *			public $connectTimeout = 30;
 *			public $transferTimeout = 60;
 *			public $globalDNS = false;
 *			public $keepAliveActive = true;
 *			public $keepAliveTimeout = 60;
 *			public $keepAliveInterval = 30;
 *		v1.00 19.10.2012
 *			initial release
 $req = new \XMLHttpRequest();
 $req->open($method, $uri);
 $req->send($data);
 $req->responseText;
 ***********************************************************************/
namespace Slothsoft\Core;

use DOMDocument;
use Exception;

class XMLHttpRequest implements \w3c\XMLHttpRequest
{

    const NEWLINE = "
";

    /*
     * public $onloadstart;
     * public $onprogress;
     * public $onabort;
     * public $onerror;
     * public $onload;
     * public $ontimeout;
     * public $onloadend;
     * public $onreadystatechange;
     */
    public $readyState = self::UNSENT;

    public $timeout = 0;

    public $withCredentials;

    public $upload;

    public $status = 0;

    public $statusText = '';

    public $responseType;

    public $response;

    public $responseText;

    public $responseXML;

    public $httpVersion = CURL_HTTP_VERSION_1_1;

    public $followRedirects = 1;

    public $maxRedirects = 10;

    public $connectTimeout = 300;

    public $transferTimeout = TIME_HOUR;

    public $globalDNS = 0;

    public $keepAliveActive = 1;

    public $keepAliveTimeout = 10;

    public $keepAliveInterval = 10;

    protected $method;

    protected $ssl;

    protected $url;

    protected $urlParam;

    protected $async;

    protected $user;

    protected $password;

    protected $requestHeaders;

    protected $responseHeaders;

    protected $eventListeners;

    protected $responseHead;

    protected $_responseType = null;

    protected $_responseCharset = null;

    protected $utf8BOM;

    protected $cookieFile;

    public static $cookies = array();

    public static $useCookies = false;

    protected $_env = [
        'SERVER_NAME' => 'Dende',
        'SERVER_SOFTWARE' => 'PHP'
    ];

    public function __construct()
    {
        if (isset($_SERVER)) {
            foreach ($this->_env as $key => &$val) {
                if (isset($_SERVER[$key])) {
                    $val = $_SERVER[$key];
                }
            }
            unset($val);
        }
    }

    // request
    public function open($method, $url, $async = true, $user = null, $password = null)
    {
        $this->method = $method;
        $this->url = $url;
        $this->async = $async;
        $this->user = $user;
        $this->password = $password;
        $this->requestHeaders = array();
        $this->responseHeaders = array();
        $this->eventListeners = array();
        
        $this->utf8BOM = pack('CCC', 239, 187, 191);
        
        $host = null;
        $this->urlParam = array(
            'scheme' => 'http',
            'host' => $host,
            'port' => null,
            'user' => null,
            'pass' => null,
            'path' => '/',
            'query' => null,
            'fragment' => null
        );
        $arr = parse_url($url);
        foreach ($this->urlParam as $key => $val) {
            if (isset($arr[$key])) {
                $this->urlParam[$key] = $arr[$key];
            }
        }
        
        $this->ssl = $this->urlParam['scheme'] === 'https';
        
        if (! $this->urlParam['port']) {
            $this->urlParam['port'] = $this->ssl ? 443 : 80;
        }
        
        /*
         * if (preg_match('/^(.+):\/\/(.+)$/', $url, $match)) {
         * $this->ssl = ($match[1] === 'https');
         * $url = $match[2];
         * if (preg_match('/^([^\/]+)(.*)$/', $url, $match)) {
         * $this->host = $match[1];
         * $this->url = $match[2];
         * } else {
         * $this->host = $url;
         * $this->url = '';
         * }
         * if (preg_match('/^(.+):(\d+)$/', $this->host, $match)) {
         * $this->host = $match[1];
         * $this->port = (int) $match[2];
         * }
         * } else {
         * $this->ssl = false;
         * $this->host = $_SERVER['HTTP_HOST'];
         * $this->url = $url;
         * }
         */
        $this->readyState = self::OPENED;
    }

    public function setRequestHeader($header, $value)
    {
        if ($this->readyState !== self::OPENED) {
            throw new Exception('InvalidStateError');
        }
        $this->requestHeaders[strtolower($header)] = $value;
    }

    public function getRequestHeader($header)
    {
        $header = strtolower($header);
        return isset($this->requestHeaders[$header]) ? $this->requestHeaders[$header] : null;
    }

    public function send($data = null)
    {
        $type = null;
        $originalData = $data;
        if ($data !== null) {
            if (is_string($data)) {
                $type = 'application/x-www-form-urlencoded';
            }
            if ($data instanceof DOMDocument) {
                $data = $data->saveXML();
                $type = 'application/xml';
            }
            if (is_array($data)) {
                /*
                 * foreach ($data as $key => &$val) {
                 * //$val = urlencode($key) . '=' . urlencode($val);
                 * $val = $key . '=' . $val;
                 * }
                 * $data = implode('&', $data);
                 * //
                 */
                $data = http_build_query($data);
                $type = 'application/x-www-form-urlencoded';
            }
        }
        $data = (string) $data;
        if ($tmp = $this->getRequestHeader('Content-Type')) {
            $type = $tmp;
        }
        
        $this->setRequestHeader('User-Agent', sprintf('Mozilla/5.0 (%s) %s (KHTML, like Gecko) %s', $this->_env['SERVER_NAME'], $this->_env['SERVER_SOFTWARE'], 'XMLHttpRequest/2.0'));
        $this->setRequestHeader('Connection', 'close');
        if ($type) {
            $this->setRequestHeader('Content-Type', $type);
            $this->setRequestHeader('Content-length', strlen($data));
        }
        if (self::$useCookies and self::$cookies) {
            $cookies = array();
            foreach (self::$cookies as $key => $val) {
                $cookies[] = $key . '=' . $val;
            }
            $this->setRequestHeader('Cookie', implode('; ', $cookies));
        }
        
        $header = array();
        foreach ($this->requestHeaders as $key => $value) {
            $header[] .= $key . ': ' . $value;
        }
        // my_dump($this->requestHeaders);
        // my_dump($data);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        
        // curl_setopt($ch, CURLOPT_HTTP_VERSION, $this->httpVersion);
        
        switch ($this->method) {
            case 'HEAD':
                curl_setopt($ch, CURLOPT_NOBODY, true);
                // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
                break;
            case 'GET':
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
                break;
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->transferTimeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->followRedirects);
        curl_setopt($ch, CURLOPT_MAXREDIRS, $this->maxRedirects);
        if ($this->globalDNS) {
            curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, 1);
        }
        curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, $this->keepAliveActive);
        curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, $this->keepAliveTimeout);
        curl_setopt($ch, CURLOPT_TCP_KEEPINTVL, $this->keepAliveInterval);
        
        if ($this->cookieFile) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        }
        
        // Get the response and close the channel.
        $res = curl_exec($ch);
        if ($err = curl_error($ch)) {
            throw new Exception(__CLASS__ . 'Exception:' . PHP_EOL . $err . PHP_EOL . print_r(curl_getinfo($ch), true));
        }
        $bodyPos = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        
        if ($res !== false) {
            // $bodyPos = strpos($res, self::NEWLINE . self::NEWLINE);
            $this->responseHead = (string) substr($res, 0, $bodyPos - strlen(self::NEWLINE . self::NEWLINE));
            $this->responseText = (string) substr($res, $bodyPos);
            // my_dump($req);
            // my_dump($res);
            // my_dump($this->responseHead);
            // my_dump($this->responseText);
            while ($headPos = (int) strpos($this->responseHead, self::NEWLINE . self::NEWLINE)) {
                $this->responseHead = substr($this->responseHead, $headPos + strlen(self::NEWLINE . self::NEWLINE));
            }
            if (preg_match('/^HTTP\/1\.1 (\d+) ([\w ]+)/', $this->responseHead, $match)) {
                $this->status = (int) $match[1];
                $this->statusText = $match[2];
            }
            $head = self::parseHeaderList($this->responseHead);
            foreach ($head as $key => $val) {
                if (self::$useCookies and $key === 'set-cookie') {
                    self::addCookie($val);
                } else {
                    $this->responseHeaders[$key] = $val;
                }
            }
            
            if (isset($this->responseHeaders['location'])) {
                /*
                 * //implemented by curl, followRedirects
                 * $url = $this->responseHeaders['location'];
                 * if (strpos($url, 'http://') !== 0) {
                 * $url = sprintf('%s://%s%s', $this->urlParam['scheme'], $this->urlParam['host'], $url);
                 * }
                 * //echo sprintf('Redirecting from "%s" to "%s" ...%s', $this->url, $url, PHP_EOL);
                 * if ($this->url !== $url) {
                 * $this->abort();
                 * $this->url = $url;
                 * $this->open($this->method, $this->url, $this->async, $this->user, $this->password);
                 * $this->send($originalData);
                 * return;
                 * }
                 * //
                 */
            }
            
            // http://tools.ietf.org/html/rfc2616
            if (isset($this->responseHeaders['transfer-encoding'])) {
                /*
                 * switch ($this->responseHeaders['transfer-encoding']) {
                 * case 'chunked':
                 * $text = '';
                 * $oldText = $this->responseText;
                 * do {
                 * $endLength = strpos($this->responseText, self::NEWLINE);
                 * $chunkLength = substr($this->responseText, 0, $endLength);
                 * $chunkLength = hexdec($chunkLength);
                 * $text .= substr($this->responseText, $endLength + strlen(self::NEWLINE), $chunkLength);
                 * $this->responseText = substr($this->responseText, $endLength + strlen(self::NEWLINE) + $chunkLength + strlen(self::NEWLINE));
                 * } while ($chunkLength and strpos($this->responseText, self::NEWLINE) !== false);
                 * $this->responseText = strlen($text)
                 * ? $text
                 * : $oldText;
                 * break;
                 * }
                 * //
                 */
            }
            if (isset($this->responseHeaders['content-type'])) {
                $type = explode(';', $this->responseHeaders['content-type'], 2);
                $this->_responseType = trim(strtolower($type[0]));
                switch ($this->_responseType) {
                    case 'application/json':
                        $this->_responseCharset = 'UTF-8';
                        break;
                }
                if (isset($type[1])) {
                    if (preg_match('/charset\s*=([^;]+)/', $type[1], $match)) {
                        $this->_responseCharset = strtoupper(trim($match[1]));
                    }
                }
            }
            
            $toCharset = 'UTF-8';
            $fromCharset = $this->_responseCharset ? $this->_responseCharset : strtoupper(mb_detect_encoding($this->responseText)); // 'ISO-8859-1'
            
            if (strlen($fromCharset) and $toCharset !== $fromCharset) {
                $this->responseText = mb_convert_encoding($this->responseText, $toCharset, $fromCharset);
            }
            
            if (substr($this->responseText, 0, 3) === $this->utf8BOM) {
                $this->responseText = substr($this->responseText, 3);
            }
            
            switch ($this->status) {
                /*
                 * case 302:
                 * if (isset($this->responseHeaders['location']) and $this->responseHeaders['location'] !== $this->url) {
                 * //$this->open('GET', $this->responseHeaders['location']);
                 * //return $this->send();
                 * }
                 * break;
                 * //
                 */
                case 503:
                case 206:
                case 204:
                case 200:
                    try {
                        $isXML = false;
                        if (strpos($this->_responseType, 'html') !== false) {
                            $isXML = true;
                        }
                        if (strpos($this->_responseType, 'xml') !== false) {
                            $isXML = true;
                        }
                        if ($isXML) {
                            $xml = $this->responseText;
                            $xml = str_replace('&nbsp;', '&#160;', $xml);
                            $xml = str_replace('&', '&amp;', $xml);
                            $xml = preg_replace('/&amp;(#?[\w\d]+;)/i', '&${1}', $xml);
                            if (strlen($xml)) {
                                $this->responseXML = new DOMDocument('1.0', $toCharset);
                                $this->responseXML->loadXML($xml, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_PARSEHUGE);
                                // my_dump($this->responseXML->saveXML());
                                if (! $this->responseXML->documentElement) {
                                    $html = mb_convert_encoding($this->responseText, $fromCharset, $toCharset);
                                    // $html = preg_replace('/\<!--+.*?-+-\>/s', '', $html); //kommentare rausnehmen, wer braucht die schon
                                    // nicht-konforme "--" aus kommentaren entfernen
                                    if (preg_match_all('/\<!--+(.*?)-+-\>/s', $html, $matchList)) {
                                        foreach ($matchList[0] as $i => $key) {
                                            $html = str_replace($key, sprintf('<!--%s-->', str_replace('--', '', $matchList[1][$i])), $html);
                                        }
                                    }
                                    $this->responseXML->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_PARSEHUGE | LIBXML_HTML_NODEFDTD);
                                    // überflüssige <?xml>s rausnehmen... unschön...
                                    $delNodes = [];
                                    foreach ($this->responseXML->childNodes as $childNode) {
                                        if ($childNode !== $this->responseXML->documentElement) {
                                            $delNodes[] = $childNode;
                                        }
                                    }
                                    foreach ($delNodes as $delNode) {
                                        $delNode->parentNode->removeChild($delNode);
                                    }
                                }
                                $this->responseXML->encoding = $toCharset;
                            }
                        }
                    } catch (Exception $e) {
                        $this->responseXML = null;
                    }
                    break;
                default:
                    // my_dump($this->status);
                    break;
            }
        }
    }

    public function abort()
    {}

    public function overrideMimeType($mime)
    {
        if ($this->readyState === self::OPENED or $this->readyState === self::DONE) {
            throw new Exception('InvalidStateError');
        }
    }

    public function getResponseHeader($header)
    {
        $header = strtolower($header);
        return isset($this->responseHeaders[$header]) ? $this->responseHeaders[$header] : null;
    }

    public function getAllResponseHeaders()
    {
        return $this->responseHead;
    }

    // EventTarget
    public function addEventListener($type, $listener, $capture = false)
    {
        if (! isset($this->eventListeners[$type])) {
            $this->eventListeners[$type] = array();
        }
        if (! in_array($listener, $this->eventListeners[$type], true)) {
            $this->eventListeners[$type][] = $listener;
        }
    }

    public function removeEventListener($type, $listener, $capture = false)
    {
        if (! isset($this->eventListeners[$type])) {
            $this->eventListeners[$type] = array();
        }
        foreach ($this->eventListeners[$type] as $i => $tmp) {
            if ($tmp === $listener) {
                unset($this->eventListeners[$type][$i]);
                break;
            }
        }
    }

    public function dispatchEvent($event)
    {}

    // proprietary
    public function setCookieFile($file)
    {
        $this->cookieFile = $file;
    }

    public function getCookieFile()
    {
        return $this->cookieFile;
    }

    // protected
    protected function fireEventListener($type)
    {
        if (isset($this->eventListeners[$type])) {
            foreach ($this->eventListeners[$type] as $listener) {
                if (is_callable($listener)) {
                    call_user_func_array($listener, array());
                }
            }
        }
    }

    protected static function addCookie($line)
    {
        $cookie = explode(';', $line, 2);
        $cookie = explode('=', $cookie[0], 2);
        self::$cookies[trim($cookie[0])] = $cookie[1];
    }

    public static function setCookie($key, $val)
    {
        self::$cookies[$key] = $val;
    }

    public static function parseHeaderList($head)
    {
        $ret = [];
        $headList = explode("\n", str_replace("\r", "\n", $head));
        foreach ($headList as $line) {
            if (strlen($line)) {
                $line = explode(':', $line, 2);
                if (count($line) === 2) {
                    $key = trim(strtolower($line[0]));
                    $val = trim($line[1]);
                    if (strlen($key)) {
                        $ret[$key] = $val;
                    }
                }
            }
        }
        return $ret;
    }
}