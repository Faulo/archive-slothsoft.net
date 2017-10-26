<?php
namespace Slothsoft\Core\Lambda;

use Slothsoft\CMS\HTTPStream;

class Stream extends HTTPStream {
	protected $pool;
	protected $workList;
	protected $currentMessage;
	protected $messageStack = [];
	
	public function __construct() {
		$this->mime = 'text/plain';
		$this->encoding = 'UTF-8';
		$this->sleepDuration = 10*TIME_MILLISECOND;
		$this->heartbeatContent = CHAR_ZEROWIDTHSPACE;
		$this->heartbeatContent = PHP_EOL;
		$this->heartbeatInterval = 60*TIME_SECOND;
		//$this->heartbeatEOL = PHP_EOL;
	}
	public function initWorkList(array $workList) {
		$this->workList = $workList;
		
		$this->pool = new Pool(Manager::THREAD_COUNT);
		$this->pool->submitList($this->workList);
	}
	public function initPool(Pool $pool) {
		$this->pool = $pool;
		$this->workList = $this->pool->getWorkList();
	}
	public function appendMessage($message) {
		$message = (string) $message;
		if ($message !== '') {
			$this->messageStack[] = $message;
		}
	}
	
	protected function parseStatus() {
		$this->status = self::STATUS_ERROR;
		if ($work = $this->pool->fetchUnfetchedWork()) {
			foreach ($work->getResult() as $message) {
				$this->appendMessage($message);
			}
			$this->appendMessage($work->getLog());
		}
		if (count($this->messageStack)) {
			$this->status = self::STATUS_CONTENT;
			$this->currentMessage = array_shift($this->messageStack);
		} else {
			if ($this->pool->hasUnfetchedWork()) {
				$this->status = self::STATUS_RETRY;
			} else {
				$this->status = self::STATUS_DONE;
			}
		}
	}
	protected function parseContent() {
		if ($this->currentMessage === null) {
			$this->content = '';
		} else {
			$this->content = $this->currentMessage;
			$this->currentMessage = null;
		}
	}
}