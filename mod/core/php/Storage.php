<?php
/***********************************************************************
 * \Storage v1.01 01.09.2015 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.01 01.09.2015
 *			$req->followRedirects = (int) (bool) $options['followRedirects'];
 *		v1.00 25.07.2014
 *			initial release
 ***********************************************************************/
namespace Slothsoft\Core;

use Slothsoft\DBMS\Manager;
use Exception;
use DOMDocument;
use DOMDocumentFragment;
use DOMNode;
use Slothsoft\DBMS\Table;

class Storage
{

    public static $touchOnExit = false;

    const LOG_PATH = SERVER_ROOT . DIR_LOG . 'storage/';

    protected static $storageList = [];

    /**
     *
     * @param string $name
     * @return Storage
     */
    public static function loadStorage(string $name)
    {
        if (! isset(self::$storageList[$name])) {
            self::$storageList[$name] = new Storage($name);
        }
        return self::$storageList[$name];
    }

    /**
     *
     * @param string $uri
     * @param int $cacheTime
     * @param mixed $data
     * @param mixed $options
     * @return null|DOMDocument
     */
    public static function loadExternalDocument(string $uri, int $cacheTime = null, $data = null, $options = null)
    {
        $cacheTime = (int) $cacheTime;
        self::_httpOptions($options);
        $storage = self::_getStorageByURI($uri);
        $name = self::_name($options, $uri, $data);
        $nowTime = time();
        $storageTime = $nowTime - $cacheTime;
        if (self::_randomCheck() or ! $storage->exists($name, $storageTime)) {
            $req = self::_httpRequest($options, $uri, $data);
            if ($req->responseXML) {
                $storage->storeDocument($name, $req->responseXML, $nowTime);
            }
        }
        return $storage->retrieveDocument($name, $storageTime);
    }

    /**
     *
     * @param string $uri
     * @param int $cacheTime
     * @param mixed $data
     * @param mixed $options
     * @return boolean
     */
    public static function clearExternalDocument(string $uri, int $cacheTime = null, $data = null, $options = null)
    {
        $cacheTime = (int) $cacheTime;
        self::_httpOptions($options);
        $storage = self::_getStorageByURI($uri);
        $name = self::_name($options, $uri, $data);
        return $storage->delete($name);
    }

    /**
     *
     * @param string $uri
     * @param int $cacheTime
     * @param mixed $data
     * @param mixed $options
     * @return NULL|\DOMXPath
     */
    public static function loadExternalXPath(string $uri, int $cacheTime = null, $data = null, $options = null)
    {
        $ret = null;
        if ($doc = self::loadExternalDocument($uri, $cacheTime, $data, $options)) {
            $ret = DOMHelper::loadXPath($doc);
        }
        return $ret;
    }

    /**
     *
     * @param string $uri
     * @param int $cacheTime
     * @param mixed $data
     * @param mixed $options
     * @return NULL|mixed
     */
    public static function loadExternalJSON(string $uri, int $cacheTime = null, $data = null, $options = null)
    {
        $cacheTime = (int) $cacheTime;
        self::_httpOptions($options);
        $storage = self::_getStorageByURI($uri);
        $name = self::_name($options, $uri, $data);
        $nowTime = time();
        $storageTime = $nowTime - $cacheTime;
        if (! $storage->exists($name, $storageTime)) {
            $req = self::_httpRequest($options, $uri, $data);
            if ($req->responseText) {
                $storage->store($name, $req->responseText, $nowTime);
            }
        }
        return $storage->retrieveJSON($name, $storageTime);
    }

    /**
     *
     * @param string $uri
     * @param int $cacheTime
     * @param mixed $data
     * @param mixed $options
     * @return NULL|string
     */
    public static function loadExternalFile(string $uri, int $cacheTime = null, $data = null, $options = null)
    {
        $cacheTime = (int) $cacheTime;
        self::_httpOptions($options);
        
        if ($options['nocache']) {
            return self::_httpRequest($options, $uri, $data)->responseText;
        }
        
        $storage = self::_getStorageByURI($uri);
        $name = self::_name($options, $uri, $data);
        $nowTime = time();
        $storageTime = $nowTime - $cacheTime;
        
        $ret = $storage->retrieve($name, $storageTime);
        if ($ret === null) {
            $req = self::_httpRequest($options, $uri, $data);
            if ($req->responseText) {
                $storage->store($name, $req->responseText, $nowTime);
                $ret = $req->responseText;
            }
        }
        
        return $ret;
    }

    /**
     *
     * @param string $uri
     * @param int $cacheTime
     * @param mixed $data
     * @param mixed $options
     * @return NULL|array
     */
    public static function loadExternalHeader(string $uri, int $cacheTime = null, $data = null, $options = null)
    {
        $cacheTime = (int) $cacheTime;
        self::_httpOptions($options);
        $options['method'] = 'HEAD';
        $storage = self::_getStorageByURI($uri);
        $name = self::_name($options, $uri, $data);
        $nowTime = time();
        $storageTime = $nowTime - $cacheTime;
        
        $ret = self::_randomCheck() ? null : $storage->retrieveJSON($name, $storageTime);
        if ($ret === null) {
            $req = self::_httpRequest($options, $uri, $data);
            $ret = XMLHttpRequest::parseHeaderList($req->getAllResponseHeaders());
            $ret['status'] = $req->status;
            $storage->storeJSON($name, $ret, $nowTime);
        }
        
        return $ret;
    }

    /**
     * randomly force-download an already existing resource
     *
     * @return boolean
     */
    protected static function _randomCheck()
    {
        return ! rand(0, 999);
    }

    protected static function _httpRequest(array $options, $uri, $data)
    {
        // echo sprintf('XMLHttpRequest %s "%s"...%s', $options['method'], $uri, PHP_EOL);
        $req = new XMLHttpRequest();
        $req->open($options['method'], $uri);
        
        if (isset($options['followRedirects'])) {
            $req->followRedirects = (int) (bool) $options['followRedirects'];
        }
        
        if (isset($options['oauth'])) {
            $options['header']['authorization'] = self::_httpOAuth($options, $uri);
        }
        
        if (isset($options['cookieFile'])) {
            $req->setCookieFile($options['cookieFile']);
        }
        
        if (! isset($options['header']['referer'])) {
            $refererURI = $uri;
            $refererParam = parse_url($refererURI);
            if (! isset($refererParam['scheme'])) {
                $refererParam['scheme'] = 'http';
            }
            if (! isset($refererParam['host'])) {
                $refererParam['host'] = 'slothsoft.net';
            }
            if (! isset($refererParam['path'])) {
                $refererParam['path'] = '';
            }
            $refererURI = sprintf('%s://%s%s', $refererParam['scheme'], $refererParam['host'], $refererParam['path']);
            $options['header']['referer'] = $refererURI;
        }
        if ($options['header']['referer'] === false) {
            unset($options['header']['referer']);
        }
        
        foreach ($options['header'] as $key => $val) {
            $req->setRequestHeader($key, $val);
        }
        
        $req->send($data);
        return $req;
    }

    /**
     *
     * @param mixed $options
     */
    protected static function _httpOptions(&$options)
    {
        if (! is_array($options)) {
            $options = [
                'method' => $options
            ];
        }
        if (! isset($options['method'])) {
            $options['method'] = 'GET';
        }
        if (! isset($options['header'])) {
            $options['header'] = [];
        }
        if (! isset($options['cache'])) {
            $options['cache'] = 0;
        }
        if (! isset($options['nocache'])) {
            $options['nocache'] = false;
        }
    }

    protected static function _httpOAuth(array $options, string $uri)
    {
        $params = [];
        $params['realm'] = $uri;
        $params['oauth_consumer_key'] = $options['oauth']['appToken'];
        $params['oauth_token'] = $options['oauth']['accessToken'];
        $params['oauth_nonce'] = time();
        $params['oauth_timestamp'] = time();
        $params['oauth_signature_method'] = 'HMAC-SHA1';
        $params['oauth_version'] = '1.0';
        
        $values = [];
        foreach ($params as $key => $val) {
            if ($key !== 'realm') {
                $key = rawurlencode($key);
                $val = rawurlencode($val);
                $values[$key] = $key . '=' . $val;
            }
        }
        ksort($values);
        
        $baseString = [];
        $baseString[] = $options['method'];
        $baseString[] = $uri;
        $baseString[] = implode('&', $values);
        
        foreach ($baseString as &$val) {
            $val = rawurlencode($val);
        }
        unset($val);
        $baseString = implode('&', $baseString);
        
        $signatureKey = rawurlencode($options['oauth']['appSecret']) . '&' . rawurlencode($options['oauth']['accessSecret']);
        $rawSignature = hash_hmac('sha1', $baseString, $signatureKey, true);
        $oAuthSignature = base64_encode($rawSignature);
        
        $params['oauth_signature'] = $oAuthSignature;
        
        $arr = [];
        foreach ($params as $key => $val) {
            $arr[] = sprintf('%s="%s"', $key, $val);
        }
        
        return sprintf('OAuth %s', implode(', ', $arr));
    }

    /**
     *
     * @param string $uri
     * @throws Exception
     * @return Storage
     */
    protected static function _getStorageByURI(string $uri)
    {
        $scheme = self::_getSchemeFromURI($uri);
        $host = self::_getHostFromURI($uri);
        $storageName = sprintf('%s-%s', $scheme, $host);
        if ($storageName === '-') {
            throw new Exception(sprintf('Cannot determine storage for uri "%s"!', $uri));
        }
        return self::loadStorage($storageName);
    }

    public static function _getStorageNameFromURI($uri)
    {
        $arr = parse_url(strtolower($uri));
        if (! isset($arr['scheme'])) {
            $arr['scheme'] = 'http';
        }
        if (! isset($arr['host'])) {
            throw new Exception(sprintf('Cannot determine host for uri "%s"!', $uri));
        }
        $host = explode('.', $arr['host']);
        $host = array_reverse($host);
        if ($host[1] === 'twitter') { // HUARGH
            $length = 3;
        } else {
            $length = 2;
        }
        while (count($host) > $length) {
            array_pop($host);
        }
        $host[] = $arr['scheme'];
        return implode('.', $host);
    }

    protected static function _getHostFromURI($uri)
    {
        $host = parse_url($uri, PHP_URL_HOST);
        $host = strtolower($host);
        $host = explode('.', $host);
        while (count($host) > 2) {
            $last = array_shift($host);
        }
        $host = implode('.', $host);
        if ($host === 'co.uk') { // HUARGH
            $host = $last . '.' . $host;
        }
        if ($host === 'twitter.com') { // this is why you don't set precedences
            if (preg_match('~/i/([a-z]+)/~', $uri, $match)) {
                $host = $match[1] . '.' . $host;
            }
        }
        return $host;
    }

    protected static function _getSchemeFromURI($uri)
    {
        return parse_url($uri, PHP_URL_SCHEME);
    }

    protected static $hashList = [];

    protected static function _hash($name)
    {
        if (! isset(self::$hashList[$name])) {
            self::$hashList[$name] = sha1($name);
        }
        return self::$hashList[$name];
    }

    protected static function _name(array $options, $uri, $data)
    {
        return sprintf('%s %s?%s', $options['method'], $uri, serialize($data));
    }

    protected static $dom;

    protected static function _DOMHelper()
    {
        if (! self::$dom) {
            self::$dom = new DOMHelper();
        }
        return self::$dom;
    }

    protected $dbName = 'storage';

    protected $tableName = 'default';

    protected $logFile = null;

    protected $dbmsTable;

    protected $now;

    protected $touchList;

    protected $cleanseTime = TIME_MONTH;

    public function __construct($storageName = null)
    {
        if ($storageName) {
            $this->tableName = $storageName;
        }
        $this->logFile = sprintf('%s%s.log', self::LOG_PATH, FileSystem::filenameSanitize($this->tableName));
        $this->dbmsTable = $this->getDBMSTable();
        if (! $this->dbmsTable->tableExists()) {
            $this->install();
        }
        $this->now = time();
        $this->touchList = [];
    }

    /**
     *
     * @return null|Table
     */
    protected function getDBMSTable()
    {
        return Manager::getTable($this->dbName, $this->tableName);
    }

    public function install()
    {
        $sqlCols = [
            // 'id' => 'int NOT NULL AUTO_INCREMENT',
            // 'name' => 'CHAR(40) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'id' => 'CHAR(40) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'payload' => 'longtext NOT NULL',
            'create-time' => 'int NOT NULL DEFAULT "0"',
            'modify-time' => 'int NOT NULL DEFAULT "0"',
            'access-time' => 'int NOT NULL DEFAULT "0"'
        ];
        $sqlKeys = [
            'id',
            // ['type' => 'UNIQUE KEY', 'columns' => ['name']],
            'create-time',
            'modify-time',
            'access-time'
        ];
        $options = [ // 'engine' => 'MyISAM', //http://www.sitepoint.com/mysql-mistakes-php-developers/
];
        $this->dbmsTable->createTable($sqlCols, $sqlKeys, $options);
    }

    /**
     *
     * @param string $name
     * @param int $modifyTime
     * @return boolean
     */
    public function exists(string $name, int $modifyTime)
    {
        $sql = sprintf('`id` = "%s" AND `modify-time` >= %d', $this->dbmsTable->escape(self::_hash($name)), $modifyTime);
        $ret = (bool) count($this->dbmsTable->select('id', $sql));
        
        $this->_createLog('exists', $name, $ret);
        
        return $ret;
    }

    /**
     *
     * @param string $name
     * @param int $modifyTime
     * @return NULL|mixed
     */
    public function retrieve(string $name, int $modifyTime)
    {
        $ret = null;
        $sql = sprintf('`id` = "%s" AND `modify-time` >= %d', $this->dbmsTable->escape(self::_hash($name)), $modifyTime);
        if ($res = $this->dbmsTable->select('payload', $sql)) {
            $ret = current($res);
        }
        $this->_createLog('retrieve', $name, $ret);
        
        return $ret;
    }

    /**
     *
     * @param string $name
     * @param int $modifyTime
     * @param DOMDocument $targetDoc
     * @return NULL|DOMDocumentFragment
     */
    public function retrieveXML(string $name, int $modifyTime, DOMDocument $targetDoc = null)
    {
        $ret = null;
        if ($data = $this->retrieve($name, $modifyTime)) {
            $dom = self::_DOMHelper();
            $ret = $dom->parse($data, $targetDoc);
        }
        return $ret;
    }

    /**
     *
     * @param string $name
     * @param int $modifyTime
     * @return NULL|DOMDocument
     */
    public function retrieveDocument(string $name, int $modifyTime)
    {
        $retDoc = null;
        $data = $this->retrieve($name, $modifyTime);
        if ($data !== null) {
            $retDoc = new DOMDocument('1.0', 'UTF-8');
            @$retDoc->loadXML($data, LIBXML_PARSEHUGE);
            if (! $retDoc->documentElement) {
                $retDoc = null;
                
                $this->_createLog('retrieveDocument', $name, false);
                
                $this->delete($name);
                // echo sprintf('"%s" is not a valid Document!', $name) . PHP_EOL;
                // $retDoc->loadXML($data);
                // echo PHP_EOL . $data . PHP_EOL;
            }
        }
        return $retDoc;
    }

    /**
     *
     * @param string $name
     * @param int $modifyTime
     * @throws Exception
     * @return mixed
     */
    public function retrieveJSON(string $name, int $modifyTime)
    {
        $retObject = null;
        $data = $this->retrieve($name, $modifyTime);
        if ($data !== null) {
            @$retObject = json_decode($data, true);
            if ($retObject === null) {
                $this->delete($name);
            }
        }
        return $retObject;
    }

    /**
     *
     * @param string $name
     * @return boolean
     */
    public function delete(string $name)
    {
        $ret = $this->dbmsTable->delete(self::_hash($name));
        $this->_createLog('delete', $name, $ret);
        
        return $ret;
    }

    /**
     *
     * @param string $name
     * @param string $payload
     * @param int $modifyTime
     * @return boolean
     */
    public function store(string $name, string $payload, int $modifyTime)
    {
        $ret = null;
        
        $update = [];
        $update['payload'] = (string) $payload;
        $update['modify-time'] = (int) $modifyTime;
        $update['access-time'] = $this->now;
        
        $insert = $update;
        $insert['id'] = self::_hash($name);
        $insert['create-time'] = $this->now;
        
        try {
            $ret = (bool) $this->dbmsTable->insert($insert, $update);
        } catch (Exception $e) {
            $ret = false;
        }
        $this->_createLog('store', $name, $ret);
        
        return $ret;
    }

    /**
     *
     * @param string $name
     * @param DOMNode $dataNode
     * @param int $modifyTime
     * @return boolean
     */
    public function storeXML(string $name, DOMNode $dataNode, int $modifyTime)
    {
        $dom = self::_DOMHelper();
        return $this->store($name, $dom->stringify($dataNode), $modifyTime);
    }

    /**
     *
     * @param string $name
     * @param DOMDocument $dataDoc
     * @param int $modifyTime
     * @return boolean
     */
    public function storeDocument(string $name, DOMDocument $dataDoc, int $modifyTime)
    {
        return $dataDoc->documentElement ? $this->store($name, $dataDoc->saveXML(), $modifyTime) : false;
    }

    /**
     *
     * @param string $name
     * @param mixed $dataObject
     * @param int $modifyTime
     * @return boolean
     */
    public function storeJSON(string $name, $dataObject, int $modifyTime)
    {
        return $this->store($name, json_encode($dataObject), $modifyTime);
    }

    protected function touch(int $id)
    {
        if ($id = (int) $id) {
            $this->touchList[$id] = $id;
            // $arr = [];
            // $arr['access-time'] = $this->now;
            // $this->dbmsTable->update($arr, $id);
        }
    }

    public function sendTouch()
    {
        if ($this->touchList) {
            $arr = [];
            $arr['access-time'] = $this->now;
            $dbmsTable = $this->getDBMSTable();
            $dbmsTable->update($arr, $this->touchList);
            $this->touchList = [];
        }
    }

    public function cleanse()
    {
        $cutoffTime = $this->now - $this->cleanseTime;
        $sql = sprintf('`access-time` < %d', $cutoffTime);
        /*
         * if ($idList = $this->dbmsTable->select('id', $sql)) {
         * $this->dbmsTable->delete($idList);
         * }
         * //
         */
        $this->dbmsTable->optimize();
    }

    public function cron()
    {
        $this->cleanse();
        return true;
    }

    public function __destruct()
    {
        if (self::$touchOnExit) {
            $this->sendTouch();
        }
    }

    protected function _createLog($method, $name, $ret)
    {
        if (CORE_STORAGE_LOG_ENABLED) {
            $ret = $ret ? 'OK' : 'FAIL';
            $log = sprintf('[%s] %s: %s %s (%s)%s', date(DATE_DATETIME), $ret, $method, self::_hash($name), $name, PHP_EOL);
            if ($handle = fopen($this->logFile, 'ab')) {
                fwrite($handle, $log);
                fclose($handle);
            }
        }
    }
}