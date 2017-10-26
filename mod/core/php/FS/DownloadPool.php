<?php
namespace Slothsoft\Core\FS;

use Slothsoft\Core\Lambda\Pool;

class DownloadPool extends Pool {
	/*
	protected $unfetchedWork = null;
	protected $_workList = [];
	const FETCHING_UTIME = 1000;
	
	const STATUS_RUNNING = 1;
	const STATUS_TERMINATED = 2;
	const STATUS_WAITING = 4;
	const STATUS_DONE = 8;
	
	public function submit($work) {
		$this->_initUnfetchedWork();
		$this->unfetchedWork[] = &$work;
		$this->_workList[] = &$work;
		return parent::submit($work);
	}
	
	public function getWorkList($status = null) {
		$retList = [];
		foreach ($this->_workList as $i => &$work) {
			if ($work) {
				if ($status === null) {
					$retList[$i] = &$work;
				} else {
					$add = false;
					if ($status & self::STATUS_RUNNING) {
						$add |= $work->isRunning();
					}
					if ($status & self::STATUS_TERMINATED) {
						$add |= $work->isTerminated();
					}
					if ($status & self::STATUS_WAITING) {
						$add |= $work->isWaiting();
					}
					if ($status & self::STATUS_DONE) {
						$add |= $work->isDone();
					}
					if ($add) {
						$retList[$i] = &$work;
					}
				}
			}
		}
		unset($work);
		return $retList;
	}
	protected function _initUnfetchedWork() {
		if ($this->unfetchedWork === null) {
			$this->unfetchedWork = [];
			foreach ($this->_workList as $i => &$work) {
				if ($work) {
					$this->unfetchedWork[] = &$work;
				}
			}
			unset($work);
		}
	}
	public function hasUnfetchedWork() {
		$this->_initUnfetchedWork();
		foreach ($this->unfetchedWork as &$work) {
			if ($work) {
				return true;
			}
		}
		unset($work);
		return false;
	}
	public function fetchUnfetchedWork($wait) {
		$this->_initUnfetchedWork();
		$ret = null;
		do {
			$hasWork = false;
			foreach ($this->unfetchedWork as $i => &$work) {
				if ($work) {
					$hasWork = true;
					if ($work->isDone()) {
						$ret = &$work;
						unset($this->unfetchedWork[$i]);
						break 2;
					}
				}
			}
			unset($work);
			if (!$wait) {
				break;
			}
			usleep(self::FETCHING_UTIME);
		} while($hasWork);
		return $ret;
	}
	//*/
}
