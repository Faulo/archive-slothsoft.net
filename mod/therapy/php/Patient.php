<?php
namespace Therapy;

use \DOMDocument;
use \DOMElement;
use \DOMXPath;

class Patient {
	protected $node;
	protected $document;
	protected $xpath;
	
	protected $globalGoals;
	protected $weeklyGoalsList;
	
	public function __construct(DOMXPath $xpath, DOMElement $node) {
		$this->xpath = $xpath;
		$this->node = $node;
		$this->document = $this->node->ownerDocument;
		
		$this->globalGoals = null;
		$nodeList = $this->xpath->evaluate('globalGoals', $this->node);
		foreach ($nodeList as $node) {
			$this->globalGoals = new Goals($this->xpath, $node);
		}
		if (!$this->globalGoals) {
			$node = $this->document->createElement('globalGoals');
			$this->node->appendChild($node);
			$this->globalGoals = new Goals($this->xpath, $node);
		}
		
		$this->weeklyGoalsList = [];
		$nodeList = $this->xpath->evaluate('weeklyGoals', $this->node);
		foreach ($nodeList as $node) {
			$this->weeklyGoalsList[] = new Goals($this->xpath, $node);
		}
		$node = $this->document->createElement('weeklyGoals');
		$this->node->appendChild($node);
		$this->weeklyGoalsList[] = new Goals($this->xpath, $node);
	}
	public function setName($name) {
		$this->node->setAttribute('name', $name);
	}
	public function getName() {
		return $this->node->getAttribute('name');
	}
	public function clearEmpty() {
		$this->globalGoals->clearEmpty(false);
		foreach ($this->weeklyGoalsList as $weeklyGoals) {
			$weeklyGoals->clearEmpty();
		}
		if ($this->isEmpty()) {
			$this->node->parentNode->removeChild($this->node);
		}
	}
	public function isEmpty() {
		return false;
	}
	public function setData(array $data) {
		if (isset($data['globalGoals'])) {
			$this->globalGoals->setData($data['globalGoals']);
		}
		if (isset($data['weeklyGoals'])) {
			foreach ($data['weeklyGoals'] as $i => $arr) {
				$i--;
				if (isset($this->weeklyGoalsList[$i])) {
					$this->weeklyGoalsList[$i]->setData($arr);
				}
			}
		}
	}
	public function asNode(DOMDocument $doc) {
		return $doc->importNode($this->node, true);
	}
}