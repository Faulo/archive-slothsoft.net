<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\DOMHelper;
use DOMElement;
use DOMDocument;
use Exception;
use Slothsoft\Core\FileSystem;

declare(ticks = 1000);

class ArchiveNodeAMBR extends ArchiveNodeJH {
	public function getArchive() {
		$header = [];
		$body = [];
		foreach ($this->childNodeList as $child) {
			if ($child instanceof FileContainer) {
				$val = $child->getContent();
				$header[] = pack('N', strlen($val));
				$body[] = $val;
			}
		}
		array_unshift($header, 'AMBR' . pack('n', count($body)));
		
		$ret = implode('', $header) . implode('', $body);
		return $ret;
	}
}