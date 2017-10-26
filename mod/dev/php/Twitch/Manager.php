<?php
namespace Twitch;

class Manager {
	protected static $dataList = [];
	protected static $dataTimeout = TIME_DAY;
	public static function downloadData($url) {
		$url = trim(strtolower($url));
		if (!isset(self::$dataList[$url])) {
			self::$dataList[$url] = \Storage::loadExternalJSON($url, self::$dataTimeout);
		}
		return self::$dataList[$url];
	}
	
	protected static $userList = [];
	public static function getUser($userName) {
		$userName = trim(strtolower($userName));
		if (!isset(self::$userList[$userName])) {
			self::$userList[$userName] = new User($userName);
		}
		return self::$userList[$userName];
	}
	
	protected static $videoList = [];
	public static function getVideo($videoId) {
		$videoId = trim(strtolower($videoId));
		if (!isset(self::$videoList[$videoId])) {
			self::$videoList[$videoId] = new Video($videoId);
		}
		return self::$videoList[$videoId];
	}
}