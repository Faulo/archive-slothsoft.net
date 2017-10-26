<?php
namespace PT;

class Manager {
	const DATABASE_NAME = 'pt';
	const TABLE_INDEX_NAME = 'index';
	protected static $dmbsDB;
	
	protected static $indexTable = null;
	protected static $strucTableList = [];
	protected static $dataTableList = [];
	
	protected static $dataTableList = [];
	
	protected static $initialized = false;
	
	public static function init() {
		if (!self::$initialized) {
			self::$dmbsDB = \DBMS\Manager::getDatabase(self::DATABASE_NAME);
			self::$indexTable = new DB\Index(self::$dbms->name, self::TABLE_INDEX_NAME);
			
			self::$initialized = true;
		}
	}
	
	public static function getDocument($documentName) {
		$documentName = trim(strtolower($documentName));
		if (!isset(self::$dataList[$documentName])) {
			self::$dataList[$documentName] = new DB\Data(self::$dbms->name, $documentName);
		}
		return self::$dataList[$documentName];
	}
}