<?php
namespace Slothsoft\PT\DB;

use Slothsoft\DBMS\Manager;

abstract class Table {
	protected $dbName;
	protected $tableName;
	protected $dbmsTable;
	public function __construct($dbName, $tableName) {
		$this->dbName = $dbName;
		$this->tableName = $tableName;
		$this->dbmsTable = Manager::getTable($dbName, $tableName);
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