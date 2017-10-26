<?php
namespace Slothsoft\Core\FS;

use Slothsoft\CMS\HTTPStream;

class DownloadManagerStream extends HTTPStream {
	protected $ownerManager;
	protected $currentWork;
	protected $singlePass = true;
	public function __construct(DownloadManager $manager) {
		$this->ownerManager = $manager;
		
		$this->mime = 'text/plain';
		$this->encoding = 'UTF-8';
		
		$this->heartbeatContent = CHAR_ZEROWIDTHSPACE;
		$this->heartbeatInterval = 10*TIME_SECOND;
		//$this->heartbeatEOL = PHP_EOL;
	}
	
	protected function parseStatus() {
		$this->status = self::STATUS_ERROR;
		if ($this->singlePass) {
			switch ($this->ownerManager->getStatus()) {
				case DownloadManager::STATUS_NEW:
					$this->ownerManager->startDownloading();
					$this->status = self::STATUS_CONTENT;
					break;
				case DownloadManager::STATUS_DOWNLOADING:
					if ($work = $this->ownerManager->fetchUnfetchedDownload(false)) {
						$this->currentWork = $work;
						$this->status = self::STATUS_CONTENT;
					} else {
						if ($this->ownerManager->hasUnfetchedWork()) {
							$this->status = self::STATUS_RETRY;
						} else {
							$this->ownerManager->stopDownloading();
							$this->status = self::STATUS_RETRY;
						}
					}
					break;
				case DownloadManager::STATUS_DOWNLOADED:
					$this->status = self::STATUS_CONTENTDONE;
					break;
			}
		} else {
			switch ($this->ownerManager->getStatus()) {
				case DownloadManager::STATUS_NEW:
					$this->ownerManager->startIndexing();
					$this->status = self::STATUS_CONTENT;
					break;
				case DownloadManager::STATUS_INDEXING:
					if ($work = $this->ownerManager->fetchUnfetchedWork(false)) {
						$this->currentWork = $work;
						$this->status = self::STATUS_CONTENT;
					} else {
						if ($this->ownerManager->hasUnfetchedWork()) {
							$this->status = self::STATUS_RETRY;
						} else {
							$this->ownerManager->stopIndexing();
							$this->status = self::STATUS_RETRY;
						}
					}
					break;
				case DownloadManager::STATUS_INDEXED:
					$this->ownerManager->startFetching();
					$this->status = self::STATUS_CONTENT;
					break;
				case DownloadManager::STATUS_FETCHING:
					if ($work = $this->ownerManager->fetchUnfetchedWork(false)) {
						$this->currentWork = $work;
						$this->status = self::STATUS_CONTENT;
					} else {
						if ($this->ownerManager->hasUnfetchedWork()) {
							$this->status = self::STATUS_RETRY;
						} else {
							$this->ownerManager->stopFetching();
							$this->status = self::STATUS_RETRY;
						}
					}
					break;
				case DownloadManager::STATUS_FETCHED:
					$this->status = self::STATUS_CONTENTDONE;
					break;
			}
		}
	}
	protected function parseContent() {
		//$this->content = $this->ownerManager->getLog();
		$this->content = '';
		if ($this->currentWork) {
			$this->content = $this->currentWork->getLog();
			$this->currentWork = null;
		} else {
			$this->content = $this->ownerManager->getLog();
			$this->ownerManager->clearLog();
		}
	}
}