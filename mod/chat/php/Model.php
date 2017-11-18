<?php
/***********************************************************************
 * Slothsoft\Chat\Model v1.01 26.10.2017 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.01 26.10.2017
 *			refactoring
 *		v1.00 04.11.2012
 *			initial release
 ***********************************************************************/
namespace Slothsoft\Chat;

use Slothsoft\DBMS\Manager;
use Slothsoft\Core\RCon;
use Slothsoft\Minecraft\Log;
use DOMDocument;
use Exception;

class Model
{

    const NS_HTML = 'http://www.w3.org/1999/xhtml';

    public $waitStep = 1;

    public $waitTime = 300;

    public $sendMax = 1024;

    protected $dbmsTable;

    protected $dbName;

    protected $dbTable;

    protected $dateDisplay = DATE_DATETIME;

    protected $dateSystem = DATE_ATOM;

    protected $colorCache;

    protected $isMinecraft;

    public function __construct()
    {
        $this->colorCache = [];
    }

    public function init($dbName, $dbTable)
    {
        try {
            $this->dbmsTable = Manager::getTable($dbName, $dbTable);
            $this->dbName = $dbName;
            $this->dbTable = $dbTable;
            $this->isMinecraft = $this->dbTable === 'minecraft_log';
            if (! $this->dbmsTable->tableExists()) {
                $this->install();
            }
        } finally {
            $this->dbmsTable = null;
        }
    }

    public function insert($message, $time, $ip)
    {
        $message = (string) $message;
        $time = (int) $time;
        $ip = (string) $ip;
        if ($this->dbmsTable) {
            if (strlen($message) and $time and strlen($ip)) {
                if ($this->isMinecraft) {
                    try {
                        $rcon = new RCon(MINECRAFT_RCON_ADDRESS, MINECRAFT_RCON_PORT, MINECRAFT_RCON_PASSWORD);
                        $rcon->execute('say ' . $message);
                    } catch (Exception $e) {}
                }
                $this->dbmsTable->insert(array(
                    'time' => $time,
                    'message' => $message,
                    'ip' => $ip,
                    'type' => Log::$messageTypes['rcon']
                ));
            }
        }
    }

    public function getMessageList($lastId)
    {
        static $messageTypes = null;
        if (! $messageTypes) {
            $messageTypes['chat'] = Log::$messageTypes['chat'];
            $messageTypes['god'] = Log::$messageTypes['god'];
            $messageTypes['rcon'] = Log::$messageTypes['rcon'];
        }
        if (isset($_REQUEST['chat-all'])) {
            $messageTypes = Log::$messageTypes;
        }
        return $this->dbmsTable->select(true, sprintf('type IN (%s) AND id > %d', implode(',', $messageTypes), $lastId), 'ORDER BY id');
    }

    public function wait($start)
    {
        if ($this->dbmsTable) {
            $messageTypes = [
                'chat' => Log::$messageTypes['chat'],
                'god' => Log::$messageTypes['god'],
                'rcon' => Log::$messageTypes['rcon']
            ];
            if (isset($_REQUEST['chat-all'])) {
                $messageTypes = Log::$messageTypes;
            }
            for ($i = 0; $i < $this->waitTime; $i += $this->waitStep) {
                if (connection_aborted()) {
                    break;
                }
                $res = $this->dbmsTable->select('id', sprintf('type IN (%s) AND time > %d', implode(',', $messageTypes), $start));
                if (count($res)) {
                    return true;
                }
                sleep($this->waitStep);
            }
        }
        return false;
    }

    public function getFirstTime()
    {
        $ret = null;
        if ($this->dbmsTable) {
            $res = $this->dbmsTable->select('time', 'time > 0 ORDER BY time ASC LIMIT 1');
            $ret = count($res) ? reset($res) : 0;
        }
        return $ret;
    }

    public function getLastTime()
    {
        $ret = null;
        if ($this->dbmsTable) {
            $res = $this->dbmsTable->select('time', '1 ORDER BY time DESC LIMIT 1');
            $ret = count($res) ? reset($res) : 0;
        }
        return $ret;
    }

    public function createRangeDocument(array $messageList)
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->appendChild($this->createRangeNode($doc, $messageList));
        return $doc;
    }

    public function createRangeNode(DOMDocument $dataDoc, array $messageList)
    {
        $retNode = $dataDoc->createElement('range');
        $retNode->setAttribute('db-name', $this->dbName);
        $retNode->setAttribute('db-table', $this->dbTable);
        foreach ($messageList as $arr) {
            $msg = $arr['message'];
            if ($arr['speaker']) {
                $msg = '<' . $arr['speaker'] . '> ' . $msg;
            }
            $color = $arr['ip'] ? $arr['ip'] : $arr['speaker'];
            $send = [
                'time-string' => date($this->dateDisplay, $arr['time']),
                'time-utc' => date($this->dateSystem, $arr['time']),
                'color' => $this->calcColor($color),
                'client' => $arr['ip']
            ];
            
            $node = $dataDoc->createElement('p');
            foreach ($send as $key => $val) {
                $node->setAttribute($key, $val);
            }
            $node->appendChild($this->parseText($msg, $dataDoc));
            $retNode->appendChild($node);
            $retNode->setAttribute('last-time', $arr['time']);
            $retNode->setAttribute('last-id', $arr['id']);
        }
        return $retNode;
    }

    public function getRangeNode($start, $end, DOMDocument $dataDoc)
    {
        $messageList = [];
        if ($this->dbmsTable) {
            $messageTypes = [
                'chat' => Log::$messageTypes['chat'],
                'god' => Log::$messageTypes['god'],
                'rcon' => Log::$messageTypes['rcon']
            ];
            if (isset($_REQUEST['chat-all'])) {
                $messageTypes = Log::$messageTypes;
            }
            $messageList = $this->dbmsTable->select(true, sprintf('type IN (%s) AND time > %d AND time <= %d ORDER BY time ASC', implode(',', $messageTypes), $start, $end));
            // $res = array_reverse($res);
        }
        return $this->createRangeNode($dataDoc, $messageList);
    }

    protected function parseText($text, DOMDocument $doc)
    {
        $retFragment = $doc->createDocumentFragment();
        while (preg_match('/(^.*?)(https?:\/\/[^ ]+)(.*$)/i', $text, $match)) {
            if (strlen($match[1])) {
                $retFragment->appendChild($this->createText($match[1], $doc));
            }
            $aNode = $doc->createElementNS(self::NS_HTML, 'a');
            $aNode->setAttribute('href', $match[2]);
            $aNode->setAttribute('rel', 'external nofollow');
            $tmp = explode('/', $match[2]);
            do {
                $linkName = array_pop($tmp);
            } while (! strlen($linkName) and count($tmp));
            $aNode->appendChild($this->createText($linkName, $doc));
            $retFragment->appendChild($aNode);
            $text = $match[3];
        }
        $retFragment->appendChild($this->createText($text, $doc));
        return $retFragment;
    }

    protected $htmlTags = [
        'u',
        'i',
        'b'
    ];

    protected function createText($text, DOMDocument $doc)
    {
        $retFragment = $doc->createDocumentFragment();
        foreach ($this->htmlTags as $htmlTag) {
            $expr = sprintf('/(^.*?)\<%1$s\>(.+?)\<\/%1$s\>(.*$)/i', $htmlTag);
            while (preg_match($expr, $text, $match)) {
                if (strlen($match[1])) {
                    $retFragment->appendChild($this->createText($match[1], $doc));
                }
                $node = $doc->createElementNS(self::NS_HTML, $htmlTag);
                $node->appendChild($this->createText($match[2], $doc));
                $retFragment->appendChild($node);
                $text = $match[3];
            }
        }
        $retFragment->appendChild($doc->createTextNode($text));
        return $retFragment;
    }

    protected function calcColor($color)
    {
        if (! isset($this->colorCache[$color])) {
            $this->colorCache[$color] = [
                255,
                255,
                255
            ];
            $md5 = md5($color);
            for ($i = 0; $i < 12; $i += 2) {
                $key = (int) ($i / 4);
                $this->colorCache[$color][$key] = (int) min($this->colorCache[$color][$key], 2 / 3 * hexdec(substr($md5, $i, 2)));
            }
            $this->colorCache[$color] = implode(',', $this->colorCache[$color]);
        }
        return $this->colorCache[$color];
    }

    protected function install()
    {
        $sqlCols = [
            'id' => 'int(11) NOT NULL AUTO_INCREMENT',
            'time' => 'int(11) NOT NULL',
            'message' => 'text NOT NULL',
            'speaker' => 'tinytext NOT NULL',
            'type' => 'int(11) NOT NULL DEFAULT "0"',
            'ip' => 'tinytext NOT NULL',
            'raw' => 'text NOT NULL'
        ];
        $sqlKeys = [
            'id',
            'time',
            'type'
        ];
        $this->dbmsTable->createTable($sqlCols, $sqlKeys);
    }
}