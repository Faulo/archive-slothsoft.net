<?php
namespace Slothsoft\Savegame\Node;

use Slothsoft\Core\DOMHelper;
use DOMElement;
use DOMDocument;

declare(ticks = 1000);

class ArchiveNodeAM2 extends ArchiveNode {
	protected function loadArchive() {
		copy($this->filePath, $this->tempDir . DIRECTORY_SEPARATOR . '1');
	}
}