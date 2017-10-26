<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class BitFieldInstruction extends AbstractInstructionContent {
	public function __construct() {
		parent::__construct();
		$this->strucData['first-bit'] = '0';
		$this->strucData['last-bit'] = '0';
		$this->strucData['size'] = '1';
	}
	protected function loadStruc() {
		parent::loadStruc();
		$this->strucData['first-bit'] = $this->parseInt($this->strucData['first-bit']);
		$this->strucData['last-bit'] = $this->parseInt($this->strucData['last-bit']);
		
		if (!$this->strucData['last-bit']) {
			$this->strucData['last-bit'] = $this->strucData['size'] * 8 - 1;
		}
	}
	protected function loadInstruction() {
		$this->instructionElements = [];
		
		$parentNode = $this->createInstructionContainer();
			
		$max = $this->strucData['size'] - 1;
		for ($i = $this->strucData['first-bit']; $i <= $this->strucData['last-bit']; $i++) {
			$offset = (int) ($i / 8);
			$pos = $max - $offset;
			$bit = $i - 8 * $offset;
			
			$instruction = [];
			$instruction['position'] = $pos;
			$instruction['bit'] = $bit;
			if (isset($this->dictionaryOptions[$i])) {
				$instruction['name'] = $this->dictionaryOptions[$i];
			}
			$parentNode->appendChild($this->createInstructionElement('bit', $instruction));
		}
		
		$this->instructionElements[] = $parentNode;
	}
}