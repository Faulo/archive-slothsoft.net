<?php
$dbName = 'cms';
$tableName = 'unison';

$table = \DBMS\Manager::getTable($dbName, $tableName);

if ($key = $this->httpRequest->getInputValue('media-id')) {
	$status = $this->httpRequest->getInputJSON();
	$status['time'] = $this->httpRequest->timeFloat;
	
	
	if ($idList = $table->select('id', ['key' => $key])) {
		$table->delete($idList);
	}
	$table->insert(
		[
			'key' => $key,
			'status' => json_encode($status)
		]
	);
}

$this->httpResponse->setStatus(\CMS\HTTPResponse::STATUS_NO_CONTENT);