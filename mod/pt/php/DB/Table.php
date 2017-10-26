<?php
namespace PT\DB;

abstract class Table {
	protected $dbName;
	protected $tableName;
	protected $dbmsTable;
	public function __construct($dbName, $tableName) {
		$this->dbName = $dbName;
		$this->tableName = $tableName;
		$this->dbmsTable = \DBMS\Manager::getTable($dbName, $tableName);
	}
	public function init() {
		if (!$this->exists()) {
			$this->install();
		}
	}
	public function exists() {
		return $this->dbmsTable->tableExists();
	}
	public function getDBName() {
		return $this->dbName;
	}
	public function getTableName() {
		return $this->tableName;
	}
	
	abstract protected function install();
}