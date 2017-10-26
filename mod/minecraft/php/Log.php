<?php
namespace Slothsoft\Minecraft;

use Slothsoft\DBMS\Manager;
use Exception;

/*
 * 2010-12-06 21:36:52 [INFO] Stopping server
 * 2010-12-06 21:36:52 [INFO] Saving chunks
 * 2010-12-06 21:37:22 [INFO] Starting minecraft server version 0.2.8
 * 2010-12-06 21:37:22 [INFO] Loading properties
 * 2010-12-06 21:37:22 [INFO] Starting Minecraft server on *:25565
 * 2010-12-06 21:37:22 [WARNING] **** SERVER IS RUNNING IN OFFLINE/INSECURE MODE!
 * 2012-12-18 12:27:25 [SEVERE] Encountered an unexpected exception t
 */
class Log
{

    const WATCH_START = '[%s] Starting Log watching for file "%s"~ /o/%s';

    const WATCH_LINE = '[%s] %s%s';

    const WATCH_ERROR = '[%s] ERROR! /o\\%s%s';

    public static $messageTypes = [
        'ignore' => - 5,
        'invalid' => - 4,
        'unknown' => - 3,
        'error' => - 2,
        'warning' => - 1,
        'raw' => 0,
        'system' => 1,
        'chat' => 2,
        'login' => 3,
        'logout' => 4,
        'god' => 5,
        'whisper' => 6,
        'boot' => 7,
        'shutdown' => 8,
        'rcon' => 9
    ];

    protected $dbName;

    protected $dbTable;

    protected $dbmsTable;

    public function __construct()
    {}

    public function init($dbName, $dbTable)
    {
        $this->dbName = $dbName;
        $this->dbTable = $dbTable;
        
        $this->dbmsTable = Manager::getTable($this->dbName, $this->dbTable);
    }

    public function watch($logFile, $logInterval)
    {
        echo sprintf(self::WATCH_START, date(DATE_DATETIME), $logFile, PHP_EOL);
        try {
            while (sleep($logInterval) === 0) {
                if (file_exists($logFile) and $rawData = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) {
                    file_put_contents($logFile, '');
                    foreach ($rawData as $line) {
                        $line = trim($line);
                        echo sprintf(self::WATCH_LINE, date(DATE_DATETIME), $line, PHP_EOL);
                        $this->dbmsTable->insert([
                            'raw' => utf8_encode($line)
                        ]);
                    }
                    $this->parse($this->dbmsTable);
                    clearstatcache();
                }
            }
        } catch (Exception $e) {
            echo sprintf(self::WATCH_ERROR, date(DATE_DATETIME), PHP_EOL, $e->getMessage());
        }
    }

    public function reset()
    {
        return $this->dbmsTable->update([
            'type' => self::$messageTypes['raw']
        ]);
    }

    public function parse()
    {
        $ret = [];
        if ($unparsed = $this->dbmsTable->select(true, 'type = ' . self::$messageTypes['raw'])) {
            foreach ($unparsed as &$line) {
                $line['type'] = self::$messageTypes['invalid'];
                $found = false;
                if (preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2}) \[(\w+?)\] (.+)/', $line['raw'], $match)) {
                    $found = true;
                    $hour = $match[4];
                    $minute = $match[5];
                    $second = $match[6];
                    $month = $match[2];
                    $day = $match[3];
                    $year = $match[1];
                    $msgType = $match[7];
                    $msgBody = $match[8];
                }
                if (preg_match('/\[(\d{2}):(\d{2}):(\d{2})\] \[[^\\/]+\\/(\w+?)\]\: (.+)/', $line['raw'], $match)) {
                    $found = true;
                    $hour = $match[1];
                    $minute = $match[2];
                    $second = $match[3];
                    $month = date('n');
                    $day = date('j');
                    $year = date('Y');
                    $msgType = $match[4];
                    $msgBody = $match[5];
                }
                if ($found) {
                    $time = mktime($hour, $minute, $second, $month, $day, $year);
                    $msg = $msgBody;
                    $player = null;
                    $type = self::$messageTypes['unknown'];
                    switch ($msgType) {
                        case 'INFO':
                            // or preg_match('/^\[(.+?)\] (.+)/u', $msg, $match)
                            if (preg_match('/^\<(.+?)\> (.+)/u', $msg, $match)) {
                                $player = $match[1];
                                $msg = $match[2];
                                $type = self::$messageTypes['chat'];
                                if ($player === 'Server') {
                                    $type = self::$messageTypes['god'];
                                    $player = 'GOTT';
                                }
                                if ($player === 'Rcon') {
                                    $type = self::$messageTypes['ignore'];
                                }
                            } elseif (strpos($msg, '[Server] ') === 0) {
                                $msg = substr($msg, strlen('[Server] '));
                                $player = 'GOTT';
                                $type = self::$messageTypes['god'];
                            } elseif (strpos($msg, 'Starting minecraft server version') === 0) {
                                $msg = 'Server: start (' . trim(str_replace('Starting minecraft server version', '', $msg)) . ')';
                                $type = self::$messageTypes['boot'];
                            } elseif (strpos($msg, 'Stopping server') === 0) {
                                $msg = 'Server: stop';
                                $type = self::$messageTypes['shutdown'];
                            } elseif (preg_match('/([^ \[]+).*(\d+\.\d+\.\d+\.\d+)?.*logged in/', $msg, $match)) {
                                $player = $match[1];
                                if (isset($match[2])) {
                                    $line['ip'] = $match[2];
                                }
                                $msg = null;
                                $type = self::$messageTypes['login'];
                            } elseif (preg_match('/([^ \[]+).*(\d+\.\d+\.\d+\.\d+)?.*lost connection: (.+)/', $msg, $match)) {
                                $player = $match[1];
                                if (isset($match[3])) {
                                    $line['ip'] = $match[2];
                                    $msg = $match[3];
                                } else {
                                    $msg = $match[2];
                                }
                                $type = self::$messageTypes['logout'];
                            } elseif (preg_match('/(.+?) whispers (.+) to (.+)/', $msg, $match)) {
                                $player = $match[1];
                                $msg = '@' . $match[3] . ': ' . $match[2];
                                $type = self::$messageTypes['whisper'];
                            }
                            
                            break;
                        case 'WARNING':
                            // $msg = 'Server: stop';
                            break;
                        case 'SEVERE':
                            $type = self::$messageTypes['shutdown'];
                            break;
                    }
                    $line['time'] = $time;
                    // my_dump($time);die();
                    $line['message'] = $msg;
                    $line['speaker'] = $player;
                    $line['type'] = $type;
                }
                $id = $line['id'];
                unset($line['id']);
                if ($line['time'] > 0) {
                    $this->dbmsTable->update($line, $id);
                } else {
                    $this->dbmsTable->delete($id);
                    // $this->dbmsTable->update($line, $id);
                }
                
                if (! isset($ret[$line['type']])) {
                    $ret[$line['type']] = 0;
                }
                $ret[$line['type']] ++;
            }
            unset($line);
        }
        return $ret;
    }
    /*
     * private $lines;
     * private $logDir;
     * public $doc;
     * public $root;
     * public function __construct($fileName, $logDir, $show, \DOMDocument $doc) {
     * $this->logDir = $logDir;
     * $logFiles = array();
     * for ($logNo = 0; file_exists('resources/log.'.$logNo.'.xml'); $logNo++) {
     * $logFiles[] = 'resources/log.'.$logNo.'.xml';
     * }
     * if ($show === null) {
     * $this->lines = file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
     * $logNo = max(count(scandir($this->logDir)) - 3, 0);
     * for ($startingLine = 0, $changed = 0; isset($this->lines[$startingLine]); $startingLine = $newStartingLine, $logNo++, $changed++) {
     * $this->doc = $doc;
     * $this->root = $this->doc->documentElement;
     * $source = $this->doc->createElement('source');
     * $this->root->appendChild($source);
     * $newStartingLine = $this->parseLines($startingLine);
     * $loggedLines = array_slice($this->lines, $startingLine, $newStartingLine - $startingLine);
     * $source->appendChild($this->doc->createCDATASection(self::CRLF . utf8_encode(implode(self::CRLF, $loggedLines)) . self::CRLF));
     * $this->root->setAttribute('messageCount', $this->root->childNodes->length - 1);
     * file_put_contents($this->logDir.$logNo.'.xml', $this->doc->saveXML());
     * //$logFiles[] = 'resources/log.'.$logNo.'.xml';
     * }
     * if ($changed > 1) {
     * file_put_contents($fileName, implode(self::CRLF, $loggedLines) . self::CRLF);
     * }
     * } else {
     * $this->doc = new \DOMDocument('1.0', 'UTF-8');
     * $this->doc->loadXML(file_get_contents($this->logDir.$show.'.xml'));
     * $this->root = $this->doc->documentElement;
     * }
     *
     * $this->searchLogs();
     * }
     * private function searchLogs() {
     * $parent = $this->doc->createElement('archive');
     * $this->root->appendChild($parent);
     * for ($i = 0; file_exists($this->logDir.$i.'.xml'); $i++) {
     * $node = $this->doc->createElement('log');
     * $node->setAttribute('src', $this->logDir.$i.'.xml');
     * $node->setAttribute('href', '?show='.$i);
     * $parent->appendChild($node);
     * }
     * }
     * private function parseLines($startAtLine) {
     * $serverStart = false;
     * for ($i = $startAtLine, $elementCount = 0; isset($this->lines[$i]); $i++) {
     * $line = &$this->lines[$i];
     * if (preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2}) \[(\w+?)\] (.+)/', $line, $match)) {
     * $time = mktime($match[4], $match[5], $match[6], $match[2], $match[3], $match[1]);
     * $msg = utf8_encode($match[8]);
     * //$msg = $match[8];
     * $node = null;
     * switch ($match[7]) {
     * case 'INFO':
     * if ($msg[0] === '<' and preg_match('/\<(.+?)\> (.+)/u', $msg, $match)
     * or $msg[0] === '[' and preg_match('/\[(.+?)\] (.+)/u', $msg, $match)) {
     * $node = $this->doc->createElement('chat');
     * $player = $match[1];
     * if ($player === 'CONSOLE') $player = 'GOTT';
     * $msg = $match[2];
     * //$msg = utf8_encode($match[2]);
     * //var_dump($match);
     * $node->setAttribute('player', $player);
     * } elseif (strpos($msg, 'Starting minecraft server version') === 0) {
     * if ($serverStart and $elementCount > self::ELEMENTS_PER_FILE) {
     * return $i;
     * }
     * $node = $this->doc->createElement('server');
     * $node->setAttribute('action', 'start');
     * $node->setAttribute('version', trim(str_replace('Starting minecraft server version', '', $msg)));
     * $serverStart = true;
     * $msg = null;
     * } elseif (strpos($msg, 'Stopping server') === 0) {
     * $node = $this->doc->createElement('server');
     * $node->setAttribute('action', 'stop');
     * $msg = null;
     * } elseif (preg_match('/(.+?) logged in/', $msg, $match)) {
     * $node = $this->doc->createElement('login');
     * $node->setAttribute('player', $match[1]);
     * $msg = null;
     * } elseif (preg_match('/(.+?) lost connection: (.+)/', $msg, $match)) {
     * $node = $this->doc->createElement('logout');
     * $node->setAttribute('player', $match[1]);
     * $msg = $match[2];
     * } elseif (preg_match('/§7(.+?) whispers (.+) to (.+)/', $msg, $match)) {
     * $node = $this->doc->createElement('whisper');
     * $node->setAttribute('from', $match[1]);
     * $node->setAttribute('to', $match[3]);
     * $msg = $match[2];
     * }
     *
     * break;
     * case 'WARNING':
     * //$node = $this->doc->createElement('warning');
     * break;
     * }
     * if ($node) {
     * $elementCount++;
     * $node->setAttribute('time-utc', date(DATE_ATOM, $time));
     * $node->setAttribute('time', date('d.m.y H:i:s', $time));
     * if ($msg !== null) {
     * $node->appendChild($this->doc->createTextNode($msg));
     * }
     * $this->root->appendChild($node);
     * }
     * }
     * unset($line);
     * }
     * return $i;
     * }
     *
     *
     * public static function resetTable($tableName) {
     * return \DB::update($tableName, ['type' => self::$messageTypes['raw']]);
     * }
     *
     * public static function parseTable($tableName) {
     * $ret = [];
     * if ($unparsed = \DB::select($tableName, true, 'type = ' . self::$messageTypes['raw'])) {
     * foreach ($unparsed as &$line) {
     * $line['type'] = self::$messageTypes['invalid'];
     * if (preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2}) \[(\w+?)\] (.+)/', $line['raw'], $match)) {
     * $time = mktime($match[4], $match[5], $match[6], $match[2], $match[3], $match[1]);
     * $msg = $match[8];
     * $player = null;
     * $type = self::$messageTypes['unknown'];
     * switch ($match[7]) {
     * case 'INFO':
     * if (preg_match('/^\<(.+?)\> (.+)/u', $msg, $match)
     * or preg_match('/^\[(.+?)\] (.+)/u', $msg, $match)) {
     * $player = $match[1];
     * $msg = $match[2];
     * $type = self::$messageTypes['chat'];
     * if ($player === 'Server') {
     * $type = self::$messageTypes['god'];
     * $player = 'GOTT';
     * }
     * if ($player === 'Rcon') {
     * $type = self::$messageTypes['ignore'];
     * }
     * } elseif (strpos($msg, 'Starting minecraft server version') === 0) {
     * $msg = 'Server: start ('.trim(str_replace('Starting minecraft server version', '', $msg)).')';
     * $type = self::$messageTypes['boot'];
     * } elseif (strpos($msg, 'Stopping server') === 0) {
     * $msg = 'Server: stop';
     * $type = self::$messageTypes['shutdown'];
     * } elseif (preg_match('/([^ \[]+).*(\d+\.\d+\.\d+\.\d+)?.*logged in/', $msg, $match)) {
     * $player = $match[1];
     * if (isset($match[2])) {
     * $line['ip'] = $match[2];
     * }
     * $msg = null;
     * $type = self::$messageTypes['login'];
     * } elseif (preg_match('/([^ \[]+).*(\d+\.\d+\.\d+\.\d+)?.*lost connection: (.+)/', $msg, $match)) {
     * $player = $match[1];
     * if (isset($match[3])) {
     * $line['ip'] = $match[2];
     * $msg = $match[3];
     * } else {
     * $msg = $match[2];
     * }
     * $type = self::$messageTypes['logout'];
     * } elseif (preg_match('/§7(.+?) whispers (.+) to (.+)/', $msg, $match)) {
     * $player = $match[1];
     * $msg = '@'.$match[3].': '.$match[2];
     * $type = self::$messageTypes['whisper'];
     * }
     *
     * break;
     * case 'WARNING':
     * //$msg = 'Server: stop';
     * break;
     * case 'SEVERE':
     * $type = self::$messageTypes['shutdown'];
     * break;
     * }
     * $line['time'] = $time;
     * //my_dump($time);die();
     * $line['message'] = $msg;
     * $line['speaker'] = $player;
     * $line['type'] = $type;
     * }
     * $id = $line['id'];
     * unset($line['id']);
     * if ($line['type'] > 0) {
     * \DB::update($tableName, $line, $id);
     * } else {
     * //\DB::delete($tableName, $id);
     * \DB::update($tableName, $line, $id);
     * }
     * if (!isset($ret[$line['type']])) {
     * $ret[$line['type']] = 0;
     * }
     * $ret[$line['type']]++;
     * }
     * unset($line);
     * }
     * return $ret;
     * }
     * //
     */
}

