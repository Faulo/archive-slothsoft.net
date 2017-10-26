<?php
namespace Slothsoft\Savegame\Node;

declare(ticks = 1000);

class EventStepInstruction extends AbstractInstructionContent {
	/*
	const EVENT_TYPE_IF = 13;
	const EVENT_TYPE_TRIGGER = 16;
	const EVENT_TYPE_TEXT = 17;
	const EVENT_TYPE_CREATE = 18;
	const EVENT_TYPE_MEMBERSHIP = 23;
	
	const EVENT_IF_SWITCH = 0;
	const EVENT_IF_ITEM = 6;
	
	const EVENT_TRIGGER_KEYWORD = 0;
	const EVENT_TRIGGER_SHOW_ITEM = 1;
	const EVENT_TRIGGER_GIVE_ITEM = 2;
	const EVENT_TRIGGER_GIVE_GOLD = 3;
	const EVENT_TRIGGER_GIVE_FOOD = 4;
	const EVENT_TRIGGER_JOIN = 5;
	const EVENT_TRIGGER_LEAVE = 6;
	const EVENT_TRIGGER_GREETING = 7;
	const EVENT_TRIGGER_GOODBYE = 8;
	
	const EVENT_CREATE_ITEM = 0;
	const EVENT_CREATE_FOOD = 2;
	
	*/
	public function __construct() {
		parent::__construct();
		$this->strucData['size'] = '0';
	}
	protected function loadStruc() {
		parent::loadStruc();
		$this->strucData['size'] = $this->parseInt($this->strucData['size']);
	}
	protected function loadInstruction() {
		$this->instructionElements = [];
		
		$eventType = $this->ownerFile->extractContent($this->valueOffset, 1);
		$eventType = $this->converter->decodeInteger($eventType, 1);
		
		$eventSubType = $this->ownerFile->extractContent($this->valueOffset + 1, 1);
		$eventSubType = $this->converter->decodeInteger($eventSubType, 1);
		
		$ref = sprintf('event-%02d.%02d', $eventType, $eventSubType);
		
		$node = $this->ownerEditor->getGlobalById($ref);
		
		if (!$node) {
			$ref = sprintf('event-%02d', $eventType);
			$node = $this->ownerEditor->getGlobalById($ref);
		}
		
		if (!$node) {
			$ref = 'event-unknown';
			$node = $this->ownerEditor->getGlobalById($ref);
		}
		
		$parentNode = $this->createInstructionContainer();
		
		if ($node) {
			foreach ($node->childNodes as $childNode) {
				if ($childNode->nodeType === XML_ELEMENT_NODE) {
					$parentNode->appendChild($childNode->cloneNode(true));
				}
			}
		}
		
		$this->instructionElements[] = $parentNode;
		
		/*
		
		//$wordSize = 1;
		
		//$parentNode = $this->createInstructionContainer();
		
		for ($position = 0; $position < $this->strucData['size']; $position += $wordSize) {
			$instructionTag = 'integer';
			$instruction = [];
			$instruction['position'] = $position;
			$instruction['size'] = $wordSize;
			switch ($position) {
				case 0:
					$instructionTag = 'select';
					$instruction['name'] = 'event-type';
					$instruction['dictionary-ref'] = 'event-types';
					break;
				case 1:
					switch ($eventType) {
						case self::EVENT_TYPE_IF:
							$instructionTag = 'select';
							$instruction['name'] = 'event-if';
							$instruction['dictionary-ref'] = 'event-if-types';
							break;
						case self::EVENT_TYPE_TRIGGER:
							$instructionTag = 'select';
							$instruction['name'] = 'event-trigger';
							$instruction['dictionary-ref'] = 'event-trigger-types';
							break;
						case self::EVENT_TYPE_TEXT:
							$instruction['name'] = 'event-text-id';
							break;
						case self::EVENT_TYPE_CREATE:
							$instructionTag = 'select';
							$instruction['name'] = 'event-create';
							$instruction['dictionary-ref'] = 'event-create-types';
							break;
						case self::EVENT_TYPE_MEMBERSHIP:
							$instruction['size'] = 0;
							break;
					}
					break;
				case 2:
					switch ($eventType) {
						case self::EVENT_TYPE_TRIGGER:
						case self::EVENT_TYPE_TEXT:
						case self::EVENT_TYPE_MEMBERSHIP:
							$instruction['size'] = 0;
							break;
						case self::EVENT_TYPE_IF:
						default:
							$instruction['size'] = 2;
							break;
					}
					break;
				case 3:
					$instruction['size'] = 0;
					break;
				case 4:
					switch ($eventType) {
						case self::EVENT_TYPE_TRIGGER:
						case self::EVENT_TYPE_TEXT:
						case self::EVENT_TYPE_MEMBERSHIP:
							$instruction['size'] = 0;
							break;
						case self::EVENT_TYPE_IF:
						default:
							$instruction['size'] = 2;
							break;
					}
					break;
				case 5:
					$instruction['size'] = 0;
					break;
				case 6:
					switch ($eventType) {
						case self::EVENT_TYPE_IF:
							$instruction['size'] = 2;
							switch ($eventSubType) {
								case self::EVENT_IF_SWITCH:
									$instructionTag = 'select';
									$instruction['name'] = 'event-switch';
									$instruction['dictionary-ref'] = 'switches';	
									break;
								case self::EVENT_IF_ITEM:
									$instructionTag = 'select';
									$instruction['name'] = 'event-item';
									$instruction['dictionary-ref'] = 'items';	
									break;
								
							}								
							break;
						case self::EVENT_TYPE_TRIGGER:
							$instruction['size'] = 2;
							switch ($eventSubType) {
								case self::EVENT_TRIGGER_KEYWORD:
									$instructionTag = 'select';
									$instruction['name'] = 'event-keyword';
									$instruction['dictionary-ref'] = 'keywords';
									break;
								case self::EVENT_TRIGGER_SHOW_ITEM:
								case self::EVENT_TRIGGER_GIVE_ITEM:
									$instructionTag = 'select';
									$instruction['name'] = 'event-item';
									$instruction['dictionary-ref'] = 'items';
									break;
								case self::EVENT_TRIGGER_GIVE_GOLD:
									$instruction['name'] = 'event-gold';
									break;
								case self::EVENT_TRIGGER_GIVE_FOOD:
									$instruction['name'] = 'event-food';
									break;
							}
							break;
						case self::EVENT_TYPE_TEXT:
						case self::EVENT_TYPE_MEMBERSHIP:
							$instruction['size'] = 0;
							break;
					}
					break;
				case 7:
					$instruction['size'] = 0;
					break;
				case 8:
					switch ($eventType) {
						case self::EVENT_TYPE_TRIGGER:
						case self::EVENT_TYPE_TEXT:
						case self::EVENT_TYPE_MEMBERSHIP:
							$instruction['size'] = 0;
							break;
						case self::EVENT_TYPE_CREATE:
							$instruction['size'] = 2;
							switch ($eventSubType) {
								case self::EVENT_CREATE_ITEM:
									$instructionTag = 'select';
									$instruction['name'] = 'event-item';
									$instruction['dictionary-ref'] = 'items';
									break;
							}
							break;
						default:
							$instruction['size'] = 2;
							break;
					}
					break;
				case 9:
					$instruction['size'] = 0;
					break;
				case 10:
					$instruction['size'] = 2;
					$instruction['name'] = 'event-goto';
					break;
				case 11:
					$instruction['size'] = 0;
					break;
				default:
					throw new Exception('unknown bit step offset ' . $position . '!?');
					break;
			}
			if ($instruction['size']) {
				$parentNode->appendChild($this->createInstructionElement($instructionTag, $instruction));
			}
		}
		$this->instructionElements[] = $parentNode;
		//*/
	}
}
