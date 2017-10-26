<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\DOMHelper;
use DOMElement;
use DOMDocument;
use Slothsoft\Core\FileSystem;

declare(ticks = 1000);

class ArchiveNode extends AbstractNode {
	protected $fileList = [];
	protected $filePath;
	protected $tempDir;
	
	public function __construct() {
		parent::__construct();
		$this->strucData['file-name'] = '';
		$this->strucData['file-time'] = '';
		
		$this->filePath = '';
		$this->tempDir = temp_dir(__CLASS__);
	}
	
	public function loadStruc() {
		parent::loadStruc();
		
		$this->ownerArchive = $this;
		
		$defaultFile = $this->ownerEditor->buildDefaultFile($this->strucData['file-name']);
		$tempFile = $this->ownerEditor->buildTempFile($this->strucData['file-name']);
		
		if ($uploadedArchives = $this->ownerEditor->getConfigValue('uploadedArchives')) {
			if (isset($uploadedArchives[$this->strucData['file-name']])) {
				move_uploaded_file($uploadedArchives[$this->strucData['file-name']], $tempFile);
			}
		}
		
		$path = file_exists($tempFile)
			? $tempFile
			: $defaultFile;
		$this->setFilePath($path);
	}
	protected function loadNode() {
		if ($this->ownerEditor->shouldLoadArchive($this->strucData['file-name'])) {
			$this->loadArchive();
			
			$this->fileList = FileSystem::scanDir($this->tempDir, FileSystem::SCANDIR_REALPATH);
		}
	}
	protected function loadArchive() {
	}
	public function writeArchive() {
		$path = $this->ownerEditor->buildTempFile($this->strucData['file-name']);
		$ret = file_put_contents($path, $this->getArchive());
		if ($ret) {
			$this->setFilePath($path);
		}
		return $ret;
	}
	public function getArchive() {
		$ret = '';
		foreach ($this->childNodeList as $child) {
			if ($child instanceof FileContainer) {
				$ret .= $child->getContent();
			}
		}
		return $ret;
	}
	public function getFileList() {
		return $this->fileList;
	}
	public function getFileName() {
		return $this->strucData['file-name'];
	}
	protected function setFilePath($path) {
		$this->filePath = $path;
		$this->strucData['file-size'] = FileSystem::size($this->filePath);
		$this->strucData['file-time'] = date(DATE_DATETIME, FileSystem::changetime($this->filePath));
	}
}