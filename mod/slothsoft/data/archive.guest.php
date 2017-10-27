<?php
namespace Slothsoft\CMS;

use Slothsoft\DBMS\Manager;

$dbName = 'cms';
$tableName = 'unison';

$table = Manager::getTable($dbName, $tableName);

$waitStep = 10000;
$waitTime = 300000000;

if ($key = $this->httpRequest->getInputValue('media-id')) {
    $lastId = $this->httpRequest->getInputJSON();
    
    for ($i = 0; $i < $waitTime; $i += $waitStep) {
        if ($res = $table->select(true, sprintf('id > %d AND `key` = "%s" ORDER BY id DESC LIMIT 1', $lastId, $key))) {
            $res = reset($res);
            break;
        }
        usleep($waitStep);
    }
    if ($res) {
        $status = json_decode($res['status'], true);
        if ($status['playing']) {
            $status['progress'] += $this->httpRequest->timeFloat - $status['time'] + $i / 1000000 + 0.2;
        }
        $status['id'] = $res['id'];
        $this->httpResponse->setStatus(HTTPResponse::STATUS_OK);
        $this->httpResponse->setBody(json_encode($status));
        $this->progressStatus = self::STATUS_RESPONSE_SET;
    } else {
        $this->httpResponse->setStatus(HTTPResponse::STATUS_NO_CONTENT);
    }
}