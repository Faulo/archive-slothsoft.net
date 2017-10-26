<?php
namespace Slothsoft\PT;

use Slothsoft\DBMS\Manager as DBMSManager;

class Manager
{

    const DATABASE_NAME = 'pt';

    const TABLE_INDEX_NAME = 'index';

    protected static $dmbsDB;

    protected static $indexTable = null;

    protected static $strucTableList = [];

    protected static $dataTableList = [];

    protected static $dataTableList = [];

    protected static $initialized = false;

    public static function init()
    {
        if (! self::$initialized) {
            self::$dmbsDB = DBMSManager::getDatabase(self::DATABASE_NAME);
            self::$indexTable = new DB\IndexTable(self::$dbms->name, self::TABLE_INDEX_NAME);
            
            self::$initialized = true;
        }
    }

    public static function getDocument($documentName)
    {
        $documentName = trim(strtolower($documentName));
        if (! isset(self::$dataList[$documentName])) {
            self::$dataList[$documentName] = new DB\DataTable(self::$dbms->name, $documentName);
        }
        return self::$dataList[$documentName];
    }
}