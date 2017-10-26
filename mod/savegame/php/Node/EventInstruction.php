<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class EventInstruction extends AbstractInstructionContent {
	public function __construct() {
		parent::__construct();
		$this->strucData['size'] = '0';
		$this->strucData['step-size'] = '0';
	}
	protected function loadStruc() {
		parent::loadStruc();
		$this->strucData['size'] = $this->parseInt($this->strucData['size']);
		$this->strucData['step-size'] = $this->parseInt($this->strucData['step-size']);
	}
	protected function loadInstruction() {
		$this->instructionElements = [];
		
		$parentNode = $this->createInstructionContainer();
		
		for ($i = 0; $i < $this->strucData['size']; $i += $this->strucData['step-size']) {
			$instructionTag = 'event-step';
			$instruction = [];
			$instruction['position'] = $i;
			$instruction['size'] = $this->strucData['step-size'];
			
			$parentNode->appendChild($this->createInstructionElement($instructionTag, $instruction));
		}
		
		$this->instructionElements[] = $parentNode;
	}
}
