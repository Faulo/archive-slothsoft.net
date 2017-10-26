<?php
namespace Slothsoft\CMS\Tracking;

use Slothsoft\DBMS\Manager as DBMSManager;
use Exception;

class Manager {
	protected static $dbName = 'tracking';
	protected static $archive = null;
	
	public static function getArchive() {
		if (!self::$archive) {
			$db = DBMSManager::getDatabase(self::$dbName);
			self::$archive = new Archive($db);
		}
		return self::$archive;
	}
	public static function getView() {
		$archive = self::getArchive();
		return new View($archive);
	}
	public static function track($request = null) {
		if ($request === null) {
			$request = $_SERVER;
		}
		try {
			$archive = self::getArchive();
			$archive->insertTemp(microtime(true), $request);
		} catch (Exception $e) {
			file_put_contents(__FILE__ . '.txt', $e->getMessage() . PHP_EOL, FILE_APPEND);
		}
	}
}