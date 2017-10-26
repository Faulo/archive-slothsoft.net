<?php
namespace Slothsoft\Core\Lambda;

class Manager {
	const THREAD_COUNT = 4;
	protected static $_workBackup = [];
	public static function streamWorkList(array $workList) {
		$stream = new Stream();
		$stream->initWorkList($workList);
		
		return $stream;
	}
	public static function runWorkList(array $workList) {
		$pool = new Pool(self::THREAD_COUNT);
		$pool->submitList($workList);
		$pool->shutdown();
		
		$retList = [];
		foreach ($workList as $i => $work) {
			$retList[$i] = $work->getResult();
		}
		return $retList;
	}
	public static function getClosureList($callable, array $paramList) {
		$workList = [];
		foreach ($paramList as $i => $param) {
			$workList[$i] = new Closure($callable, $param);
		}
		return $workList;
	}
	public static function streamClosureList($callable, array $paramList) {
		$workList = self::getClosureList($callable, $paramList);
		return self::streamWorkList($workList);
	}
	public static function runClosureList($callable, array $paramList) {
		$workList = self::getClosureList($callable, $paramList);
		return self::runWorkList($workList);
	}
	public static function executeList($callable, array $paramList) {
		return self::runClosureList($callable, $paramList);
	}
}