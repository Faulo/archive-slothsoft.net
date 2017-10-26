<?php
namespace Therapy;

use \DOMDocument;
use \DOMElement;
use \DOMXPath;

class Goals {
	protected $node;
	protected $document;
	protected $xpath;
	
	protected $goalList;
	
	public function __construct(DOMXPath $xpath, DOMElement $node) {
		$this->xpath = $xpath;
		$this->node = $node;
		$this->document = $this->node->ownerDocument;
		
		$this->goalList = [];
		$nodeList = $this->xpath->evaluate('goal', $this->node);
		foreach ($nodeList as $node) {
			$this->goalList[] = new Goal($this->xpath, $node);
		}
		
		for ($i = 0, $j = max(2 - count($this->goalList), 0) + 1; $i < $j; $i++) {
			$node = $this->document->createElement('goal');
			$this->node->appendChild($node);
			$this->goalList[] = new Goal($this->xpath, $node);
		}
	}
	public function clearEmpty($removeSelf = true) {
		foreach ($this->goalList as $goal) {
			$goal->clearEmpty();
		}
		if ($removeSelf and $this->isEmpty()) {
			$this->node->parentNode->removeChild($this->node);
		}
	}
	public function isEmpty() {
		$ret = true;
		foreach ($this->goalList as $goal) {
			if (!$goal->isEmpty()) {
				$ret = false;
				break;
			}
		}
		return $ret;
	}
	public function getNode() {
		return $this->node;
	}
	public function setData(array $data) {
		if (isset($data['goal'])) {
			foreach ($data['goal'] as $i => $arr) {
				$i--;
				if (isset($this->goalList[$i])) {
					$this->goalList[$i]->setData($arr);
				}
			}
		}
	}
}