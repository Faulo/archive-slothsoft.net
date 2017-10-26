<?php
namespace Therapy;

use \DOMDocument;
use \DOMElement;
use \DOMXPath;

class Goal {
	protected $node;
	protected $document;
	protected $xpath;
	
	protected $ratingList;
	
	public function __construct(DOMXPath $xpath, DOMElement $node) {
		$this->xpath = $xpath;
		$this->node = $node;
		$this->document = $this->node->ownerDocument;
		
		//$this->setActive($this->getActive());
		//$this->setOffset($this->getOffset());
		$this->node->removeAttribute('offset');
		$this->node->removeAttribute('active');
			
		$this->ratingList = [];
		$nodeList = $this->xpath->evaluate('rating', $this->node);
		foreach ($nodeList as $node) {
			$this->ratingList[] = new Rating($this->xpath, $node);
		}
		
		for ($i = 0, $j = max(100 - count($this->ratingList), 0) + 1; $i < $j; $i++) {
			$node = $this->document->createElement('rating');
			$this->node->appendChild($node);
			$this->ratingList[] = new Rating($this->xpath, $node);
		}
	}
	public function clearEmpty() {
		foreach ($this->ratingList as $rating) {
			$rating->clearEmpty();
		}
		if ($this->isEmpty()) {
			$this->node->parentNode->removeChild($this->node);
		}
	}
	public function isEmpty() {
		return ! (bool) strlen($this->getName());
	}
	public function getNode() {
		return $this->node;
	}
	public function setName($name) {
		$this->node->setAttribute('name', $name);
	}
	public function getName() {
		return $this->node->getAttribute('name');
	}
	public function setDescription($description) {
		$this->node->setAttribute('description', $description);
	}
	public function getDescription() {
		return $this->node->getAttribute('description');
	}
	public function setOffset($offset) {
		$this->node->setAttribute('offset', $offset);
	}
	public function getOffset() {
		return (int) $this->node->getAttribute('offset');
	}
	public function setActive($active) {
		$this->node->setAttribute('active', $active);
	}
	public function getActive() {
		return $this->node->hasAttribute('active')
			? (int) $this->node->getAttribute('active')
			: 1;
	}
	public function setData(array $data) {
		if (isset($data['name'])) {
			$this->setName($data['name']);
		}
		if (isset($data['description'])) {
			$this->setDescription($data['description']);
		}
		if (isset($data['offset'])) {
			$this->setOffset($data['offset']);
		}
		if (isset($data['rating'])) {
			foreach ($data['rating'] as $i => $arr) {
				$i--;
				if (isset($this->ratingList[$i])) {
					$this->ratingList[$i]->setData($arr);
				}
			}
		}
	}
}