<?php
/***********************************************************************
 * Slothsoft\Chat\SSEServer v1.01 26.10.2017 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.01 26.10.2017
 *			refactoring
 *		v1.00 29.05.2014
 *			public function __construct($serverName)
 ***********************************************************************/
namespace Slothsoft\Chat;

use Slothsoft\SSE\Server;

class SSEServer extends Server
{

    protected $model;

    public function __construct($tableName, $dbName)
    {
        $this->model = new Model();
        $this->model->init($dbName, $tableName);
        
        parent::__construct($tableName, $dbName);
    }

    protected function install()
    {}

    public function dispatchEvent($type, $data, $time = null, $ip = null)
    {
        return $this->model->insert($data, $time, $ip);
    }

    public function fetchNewEvents($lastId)
    {
        if ($ret = $this->model->getMessageList($lastId)) {
            $ret = [
                $this->_parseEventList($ret)
            ];
        }
        return $ret;
    }

    public function fetchLastEvent()
    {
        if ($ret = parent::fetchLastEvent()) {
            $ret = $this->_parseEventList([
                $ret
            ]);
        }
        return $ret;
    }

    protected function _parseEventList(array $eventList)
    {
        $doc = $this->model->createRangeDocument($eventList);
        return [
            'id' => (int) $doc->documentElement->getAttribute('last-id'),
            'type' => 'message',
            'data' => $doc->saveXML()
        ];
    }
}