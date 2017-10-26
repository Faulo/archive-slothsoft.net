<?php
namespace Slothsoft\Core\FS;

use Exception;
use DOMXPath;

class DownloadManager {
	const STATUS_NEW = 1;
	const STATUS_INDEXING = 2;
	const STATUS_INDEXED = 3;
	const STATUS_FETCHING = 4;
	const STATUS_FETCHED = 5;
	
	const STATUS_DOWNLOADING = 6;
	const STATUS_DOWNLOADED = 7;
	
	protected $xpath;
	protected $_log;
	protected $_pool;
	protected $_options;
	protected $_currentOptionsList;
	protected $_status;
	protected $_currentWorkList;
	protected $_config = [
		'threads-active' => false,
		'threads-count' => 8,
	];
	
	public function __construct(DOMXPath $xpath) {
		$this->xpath = $xpath;
		$this->_log = '';
		$this->_status = self::STATUS_NEW;
		$this->_options = [];
		$this->_currentOptionsList = [];
		$this->_currentWorkList = [];
	}
	public function setConfig(array $config) {
		foreach ($this->_config as $key => &$val) {
			if (isset($config[$key])) {
				settype($config[$key], gettype($val));
				$val = $config[$key];
			}
		}
		unset($val);
	}
	public function setOptions(array $options) {
		$this->_options = $options;
	}
	public function getLog() {
		return $this->_log;
	}
	public function clearLog() {
		$this->_log = '';
	}
	public function getStatus() {
		return $this->_status;
	}
	
	public function run() {
		$this->startDownloading();
		while ($this->hasUnfetchedWork()) {
			$work = $this->fetchUnfetchedDownload(true);
			$this->log($work);
			sleep(1);
		}
		$this->stopDownloading();
		return $this->_log;
		/*
		$this->startIndexing();
		$workList = $this->stopIndexing();
		
		foreach ($workList as $work) {
			//$res = $work->getResult();
			//$this->log(sprintf('downloaded %d things for %s!', count($res), $work->getName()));
			$this->log($work);
		}
		
		$this->startFetching();
		$workList = $this->stopFetching();
		
		foreach ($workList as $work) {
			//$res = $work->getResult();
			//$this->log(sprintf('downloaded %d things for %s!', count($res), $work->getName()));
			$this->log($work);
		}
		
		return $this->_log;
		//*/
	}
	public function getStream() {
		return new DownloadManagerStream($this);
	}
	public function startDownloading() {
		$this->_status = self::STATUS_DOWNLOADING;
		$this->log('Starting update process...');
		
		$updateNodeList = $this->xpath->evaluate('//update');
		if ($this->_config['threads-active']) {
			$this->_pool = new DownloadPool($this->_config['threads-count']);
		}
		$optionCount = 0;
		foreach ($updateNodeList as $updateNode) {
			$options = $this->_options;
			if (!is_array($options)) {
				my_dump($options);
				throw new Exception('wtf');
			}
			do {
				if ($updateNode->nodeType === XML_ELEMENT_NODE) {
					foreach ($updateNode->attributes as $attr) {
						if (!isset($options[$attr->name])) {
							$options[$attr->name] = $attr->value;
						}
					}
					if ($php = $this->xpath->evaluate('string(php)', $updateNode)) {
						$options['success-php'] = $php;
					}
					if ($blacklist = $this->xpath->evaluate('string(blacklist)', $updateNode)) {
						$options['blacklist'] = $blacklist;
					}
				}
			} while($updateNode = $updateNode->parentNode);
			$optionCount += $this->_addDownload($options);
		}
		//$this->log(print_r($optionsList, true));
		
		//$this->_log .= PHP_EOL;
		$this->log(sprintf('Found %d things to check, downloading...', $optionCount));
	}
	public function stopDownloading() {
		$this->_status = self::STATUS_DOWNLOADED;
		if ($this->_pool) {
			$this->_pool->shutdown();
			$this->_pool = null;
		}
		$this->log('...done! \\o/');
	}
	public function startIndexing() {
		$this->_status = self::STATUS_INDEXING;
		//$this->_log .= PHP_EOL;
		$this->log('Starting update process...');
		
		$updateNodeList = $this->xpath->evaluate('//update');
		$optionsList = [];
		foreach ($updateNodeList as $updateNode) {
			$options = $this->_options;
			do {
				if ($updateNode->nodeType === XML_ELEMENT_NODE) {
					foreach ($updateNode->attributes as $attr) {
						if (!isset($options[$attr->name])) {
							$options[$attr->name] = $attr->value;
						}
					}
					if ($php = $this->xpath->evaluate('string(php)', $updateNode)) {
						$options['success-php'] = $php;
					}
				}
			} while($updateNode = $updateNode->parentNode);
			if ((int) $options['active']) {
				$optionsList[] = $options;
			}
		}
		//$this->log(print_r($optionsList, true));
		
		//$this->_log .= PHP_EOL;
		$this->log(sprintf('Found %d things to check, indexing...', count($optionsList)));
		
		if ($optionsList) {
			if ($this->_config['threads-active']) {
				$this->_pool = new DownloadPool($this->_config['threads-count']);
			}
			foreach ($optionsList as $options) {
				$options['mode'] = 'index';
				$this->_addDownload($options);
			}
		} else {
			$this->stopIndexing();
		}
	}
	public function stopIndexing() {
		$workList = [];
		$this->_status = self::STATUS_INDEXED;
		$this->_currentOptionsList = [];
		if ($this->_pool) {
			$this->_pool->shutdown();
			$workList = $this->_pool->getWorkList(DownloadPool::STATUS_DONE);
			foreach ($workList as $work) {
				$this->_currentOptionsList = array_merge($this->_currentOptionsList, $work->getResult());
			}
			$this->_pool = null;
		}
		return $workList;
	}
	public function startFetching() {
		$this->_status = self::STATUS_FETCHING;
		$optionsList = $this->_currentOptionsList;
		//$this->_log .= PHP_EOL;
		$this->log(sprintf('Found %d things to download, fetching...', count($optionsList)));
		if ($optionsList) {
			if ($this->_config['threads-active']) {
				$this->_pool = new DownloadPool($this->_config['threads-count']);
			}
			foreach ($optionsList as $options) {
				$options['mode'] = 'fetch';
				$this->_addDownload($options);
			}
		} else {
			$this->stopFetching();
		}
	}
	public function stopFetching() {
		$workList = [];
		$this->_status = self::STATUS_FETCHED;
		$this->_currentOptionsList = [];
		if ($this->_pool) {
			$this->_pool->shutdown();
			$workList = $this->_pool->getWorkList(DownloadPool::STATUS_DONE);
			foreach ($workList as $work) {
				$this->_currentOptionsList = array_merge($this->_currentOptionsList, $work->getResult());
			}
			$this->_pool = null;
		}
		//$this->_log .= PHP_EOL;
		$this->log('...done! \\o/');
		return $workList;
	}
	public function hasUnfetchedWork() {
		$ret = null;
		if ($this->_config['threads-active']) {
			if ($this->_pool) {
				$ret = $this->_pool->hasUnfetchedWork();
			}
		} else {
			$ret = (bool) count($this->_currentWorkList);
		}
		return $ret;
	}
	public function fetchUnfetchedWork($wait) {
		$retWork = null;
		if ($this->_config['threads-active']) {
			if ($this->_pool) {
				$retWork = $this->_pool->fetchUnfetchedWork($wait);
			}
		} else {
			if (count($this->_currentWorkList)) {
				$retWork = array_shift($this->_currentWorkList);
				//file_put_contents(__FILE__ . '.txt', print_r($retWork, true));
				$retWork->run();
				//file_put_contents(__FILE__ . '.txt', print_r($retWork, true));
			}
		}
		return $retWork;
	}
	public function fetchUnfetchedDownload($wait) {
		$retWork = $this->fetchUnfetchedWork($wait);
		if ($retWork) {
			$optionsList = $retWork->getResult();
			foreach ($optionsList as $options) {
				$this->_addDownload($options);
			}
		}
		return $retWork;
	}
	protected function _addDownload(array $options) {
		$ret = false;
		if ((int) $options['active']) {
			if ($this->_config['threads-active']) {
				if ($this->_pool) {
					$ret = true;
					$this->_pool->submit(new DownloadWork($options));
				}
			} else {
				$ret = true;
				$this->_currentWorkList[] = new DownloadWork($options);
			}
		}
		return $ret;
	}
	protected function log($message) {
		if ($message instanceof DownloadWork) {
			$this->_log .= $message->getLog();
		} else {
			$this->_log .= sprintf('[%s] %s: %s%s', date(DATE_DATETIME), __CLASS__, $message, PHP_EOL);
		}
	}
}