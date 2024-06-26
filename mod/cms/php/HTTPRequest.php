<?php
/***********************************************************************
 * \CMS\HTTPRequest v1.00 19.10.2012 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.00 19.10.2012
 *			initial release
 ***********************************************************************/
namespace Slothsoft\CMS;

use Slothsoft\Lang\Dictionary;
use DOMDocument;

declare(ticks = 1000);

class HTTPRequest
{

    public static function prepareEnvironment(array &$env)
    {
        $lang = null;
        if (isset($env['HTTP_ACCEPT_LANGUAGE'])) {
            if ($matchList = http_parse_language($env['HTTP_ACCEPT_LANGUAGE'])) {
                foreach ($matchList as $i => $match) {
                    if ($i === 0) {
                        $lang = $match;
                    }
                    if (isset($match['region']) and strlen($match['region'])) {
                        $lang = $match;
                        break;
                    }
                }
                if ($lang) {
                    $lang = (isset($lang['region']) and strlen($lang['region'])) ? sprintf('%s-%s', $lang['language'], $lang['region']) : $lang['language'];
                }
            }
        }
        $env['REQUEST_LANGUAGE'] = $lang;
        $env['REQUEST_TIME_DATE'] = date(DATE_DATETIME, $env['REQUEST_TIME']);
        
        if (!isset($env['SERVER_NAME']) or $env['SERVER_NAME'] === 'localhost') {
            $env['SERVER_NAME'] = SERVER_NAME;
        }
        
        $turing = 'human';
        if (isset($env['HTTP_USER_AGENT'])) {
            $botList = [];
            $botList[] = 'bot';
            $botList[] = 'crawler';
            $botList[] = 'spider';
            $botList[] = 'slurp';
            $botList[] = 'analyzer';
            foreach ($botList as $bot) {
                if (stripos($env['HTTP_USER_AGENT'], $bot) !== false) {
                    $turing = 'bot';
                    break;
                }
            }
        } else {
            $turing = 'bot';
        }
        if (isset($env['HTTP_HOST'], $env['SERVER_NAME'])) {
            if ($env['HTTP_HOST'] !== $env['SERVER_NAME']) {
                $turing = 'bot';
            }
        } else {
            $turing = 'bot';
        }
        if ($env['REMOTE_ADDR'] === '::1') {
            $turing = 'shell';
        }
        $env['REQUEST_TURING'] = $turing;
    }

    const METHOD_GET = 'GET';

    const METHOD_POST = 'POST';

    const METHOD_HEAD = 'HEAD';

    const METHOD_OPTIONS = 'OPTIONS';

    const PROTOCOL_HTTP = 'HTTP';

    public $dict;

    public $method;

    public $schema;

    public $protocolName;

    public $protocolRecognised;

    public $protocolMajorVersion;

    public $protocolMinorVersion;

    public $time;

    public $clientIp;

    public $clientAgent;

    public $clientHost;

    public $input = null;

    public $mode;

    public $path = '/';

    protected $headerList;

    public function __construct()
    {
        $this->headerList = [];
        $this->protocolRecognised = true;
        $this->protocolName = self::PROTOCOL_HTTP;
        $this->protocolMajorVersion = 1;
        $this->protocolMinorVersion = 0;
    }

    public function init(array $env)
    {
        $this->method = isset($env['REQUEST_METHOD']) ? $env['REQUEST_METHOD'] : self::METHOD_GET;
        $this->schema = isset($env['REQUEST_SCHEME']) ? $env['REQUEST_SCHEME'] : self::PROTOCOL_HTTP;
        $this->schema = strtolower($this->schema);
        $protocol = isset($env['SERVER_PROTOCOL']) ? $env['SERVER_PROTOCOL'] : 'HTTP/1.1';
        $protocol = trim($protocol);
        $this->protocolRecognised = preg_match('/^(\w+)\/(\d+)\.(\d+)$/', $protocol, $match);
        if ($this->protocolRecognised) {
            $this->protocolName = $match[1];
            $this->protocolMajorVersion = (int) $match[2];
            $this->protocolMinorVersion = (int) $match[3];
        }
        $this->time = isset($env['REQUEST_TIME']) ? $env['REQUEST_TIME'] : time();
        $this->timeFloat = isset($env['REQUEST_TIME_FLOAT']) ? $env['REQUEST_TIME_FLOAT'] : (float) time();
        $this->clientIp = isset($env['REMOTE_ADDR']) ? $env['REMOTE_ADDR'] : '127.0.0.1';
        $this->clientAgent = isset($env['HTTP_USER_AGENT']) ? $env['HTTP_USER_AGENT'] : '';
        $this->clientHost = isset($env['HTTP_HOST']) ? $env['HTTP_HOST'] : SERVER_NAME;
        $this->clientHost = strtolower($this->clientHost);
        $this->dict = Dictionary::getInstance();
    }

    public function hasInputValue($key)
    {
        return isset($this->input[$key]);
    }

    public function getInputValue($key, $val = null)
    {
        return isset($this->input[$key]) ? $this->input[$key] : $val;
    }

    public function setInputValue($key, $val = null)
    {
        $this->input[$key] = $val;
    }

    public function setInput(array $input)
    {
        $this->input = $input;
    }

    // deprecated, use getBody(), should return $this->input
    public function getInput()
    {
        return file_get_contents('php://input');
    }

    // deprecated, use getBodyJSON()
    public function getInputJSON()
    {
        return json_decode($this->getInput(), true);
    }

    public function getBody()
    {
        return file_get_contents('php://input');
    }

    public function getBodyJSON()
    {
        return json_decode($this->getBody(), true);
    }

    public function setAllHeaders(array $headerList)
    {
        foreach ($headerList as $key => $val) {
            $this->headerList[strtolower($key)] = $val;
        }
    }

    public function getHeader($key)
    {
        $key = strtolower($key);
        return isset($this->headerList[$key]) ? $this->headerList[$key] : null;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getURL()
    {
        return sprintf('%s://%s%s', $this->schema, $this->clientHost, $this->path);
    }

    public function getQuery()
    {
        return http_build_query($this->input);
    }

    public function asNode(DOMDocument $doc)
    {
        $retNode = $doc->createElement('request');
        $retNode->setAttribute('url', $this->getURL());
        $retNode->setAttribute('query', $this->getQuery());
        $retNode->setAttribute('lang', $this->dict->getLang());
        $retNode->setAttribute('stamp', $this->time);
        $retNode->setAttribute('datetime', date(DATE_DATETIME, $this->time));
        $retNode->setAttribute('utc', date(DATE_UTC, $this->time));
        foreach ($this->input as $key => $val) {
            if (is_string($val)) {
                $node = $doc->createElement('param');
                $node->setAttribute('name', $key);
                $node->appendChild($doc->createTextNode($val));
                $retNode->appendChild($node);
            }
        }
        return $retNode;
    }
}