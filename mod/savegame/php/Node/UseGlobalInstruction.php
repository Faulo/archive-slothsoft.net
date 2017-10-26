<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class UseGlobalInstruction extends AbstractInstructionContent {
	public function __construct() {
		parent::__construct();
		$this->strucData['ref'] = '';
	}
	protected function loadInstruction() {
		$this->instructionElements = [];
		
		if ($node = $this->ownerEditor->getGlobalById($this->strucData['ref'])) {
			foreach ($node->childNodes as $childNode) {
				if ($childNode->nodeType === XML_ELEMENT_NODE) {
					$instructionNode = $childNode->cloneNode(true);
					$instructionNode->setAttribute('position', $this->parseInt($instructionNode->getAttribute('position')) + $this->strucData['position']);
					$this->instructionElements[] = $instructionNode;
				}
			}
		}
	}
}

