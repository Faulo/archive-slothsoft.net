<?php
namespace Slothsoft\Dev;



abstract class Project {
	protected static $contentType = 'application/xhtml+xml';
	public static $FILES = array(

		

	);

	public static $DIRS = array(

		'root' => '/'

	);

	private static $Data;

	private static $Template;

	public static $Document;
	
	public static $LangArr;

	public static function construct() {

		self::$Document = new DOMDocumentSmart();

	}

	public static function getFilePath($file, $dir = null) {

		$path = ROOT . self::$DIRS['root'];

		if ($dir !== null) {

			$path .= self::$DIRS[$dir];

		}

		$path .= self::$FILES[$file];

		return $path;

	}

	public static function getFileArray($file, $dir = null) {

		$path = self::getFilePath($file, $dir);

		return file($path);

	}
	
	public static function getFileString($file, $dir = null) {

		$path = self::getFilePath($file, $dir);

		return file_get_contents($path);

	}

	public static function getFileJSON($file, $dir = null) {

		$path = self::getFilePath($file, $dir);
		//$str = file_get_contents($path);
		//var_dump(json_decode($str));die();
		//die('<script type="text/javascript">var i = '..';</script>');
		return json_decode(file_get_contents($path), true);

	}

	public static function loadData($data) {

		self::$Data = $data;

	}

	public static function loadTemplate($xml) {

		self::$Template = $xml;

	}
	
	public static function setLanguage($key) {
		self::$LangArr = self::getFileJSON('lang/'.$key);
		//var_dump(self::$LangArr);die();
	}
	public static function getLangString($key) {
		if (self::$LangArr and isset(self::$LangArr['lang/'.$key])) {
			return '##lang/'.$key.'##';
		}
		return $key;
	}
	protected static function header() {
		//XHTML Header senden, wenn UA XHTML kann

		//if (stristr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml')) 

			header('Content-Type: '.self::$contentType.'; charset=UTF-8');

		//else

		//	header('Content-Type: text/html; charset=UTF-8');

		header('Content-Script-Type: application/javascript');

		header('Content-Style-Type: text/css');
	}
	public static function echoHTML($tempfile = null) {

		//$Doc = self::$Document;

		$Doc = new DOMDocumentSmart();

		

		$Doc->loadXML(self::$Template);

		//self::$Document = $Doc->importNode(self::$Document->documentElement, true);

		self::$Data['PAGE_CURRENT'] = $_SERVER['SCRIPT_NAME'] . str_replace('&', '&amp;', $_SERVER['QUERY_STRING']);

		self::$Data['PAGE_FULL'] = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . str_replace('&', '&amp;', $_SERVER['QUERY_STRING']);

		$Doc->loadElements(self::$Data);
		
		if (self::$LangArr) {
			$Doc->loadLanguageArray(self::$LangArr);
		}
		self::header();
		
		$Doc->documentElement->appendChild($Doc->createComment('Rendering of this page took '.get_execution_time().' ms.'));
		
		$xml = $Doc->saveXML();
		if ($tempfile !== null) {
			file_put_contents($tempfile, $xml);
		}
		die($xml);

	}

}



