<?php
namespace Slothsoft\Savegame\Node;

use Exception;

declare(ticks = 1000);

class FileContainer extends AbstractContainerContent {
	protected $content;
	
	public function __construct() {
		parent::__construct();
		$this->strucData['file-name'] = '';
		$this->strucData['file-path'] = '';
	}
	protected function loadStruc() {
		parent::loadStruc();
		
		$this->ownerFile = $this;
		
		if (!$this->strucData['file-path']) {
			throw new Exception('empty filename?');
		}
		
		$this->setContent(file_get_contents($this->strucData['file-path']));
	}
	
	public function extractContent($offset, $length) {
		$ret = null;
		switch ($length) {
			case 'auto':
				$ret = '';
				for ($i = $offset, $j = strlen($this->content); $i < $j; $i++) {
					$char = $this->content[$i];
					if ($char === "\0") {
						break;
					} else {
						$ret .= $char;
					}
				}
				break;
			default:
				$ret = substr($this->content, $offset, $length);
				$ret = str_pad($ret, $length, "\0");
				break;
		}
		return $ret;
	}
	public function insertContent($offset, $length, $value) {
		$this->content = substr_replace($this->content, $value, $offset, $length);
	}
	public function insertContentBit($offset, $bit, $value) {
		//echo "setting bit $bit at position $offset to " . ($value?'ON':'OFF') . PHP_EOL;
		$byte = $this->extractContent($offset, 1);
		$byte = hexdec(bin2hex($byte));
		if ($value) {
			$byte |= $bit;
		} else {
			$byte &= ~ $bit;
		}
		$byte = substr(pack('N', $byte), -1);
		return $this->insertContent($offset, 1, $byte);
	}
	public function setContent($content) {
		$this->content = $content;
	}
	public function getContent() {
		return $this->content;
	}
}