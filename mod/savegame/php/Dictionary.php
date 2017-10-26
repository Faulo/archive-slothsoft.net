<?php
namespace Slothsoft\Savegame;

use DOMDocument;
use Slothsoft\Core\DOMHelper;

declare(ticks = 1000);

class Dictionary {
	protected $strucDoc;
	protected $xpath;
	protected $config;
	protected $entries;
	public function __construct(array $config = []) {
		$this->config = $config;
		$this->entries = [];
	}
	public function load(string $file) {
		$this->strucDoc = DOMHelper::loadDocument($file);
		$this->xpath = DOMHelper::loadXPath($this->strucDoc);
		
		$this->entries = [];
		foreach ($this->xpath->evaluate('/dictionary/entry') as $entryNode) {
			if ($entryNode->hasAttribute('range-end')) {
				$start = $entryNode->getAttribute('range-start');
				$end = $entryNode->getAttribute('range-end');
				$label = $entryNode->getAttribute('range-label');
				foreach (range($start, $end) as $key) {
					$optionNode = $entryNode->ownerDocument->createElement('option');
					$optionNode->setAttribute('key', $key);
					$optionNode->setAttribute('val', $label ? sprintf($label, $key) : $key);
					$entryNode->appendChild($optionNode);
				}
			}
			$key = $entryNode->getAttribute('name');
			$this->entries[$key] = [];
			foreach ($this->xpath->evaluate('option', $entryNode) as $optionNode) {
				$arr = [];
				$arr['title'] = $optionNode->getAttribute('title');
				$arr['val'] = $optionNode->getAttribute('val');
				$arr['key'] = $optionNode->getAttribute('key');
				$this->entries[$key][$arr['key']] = $arr;
			}
		}
	}
	public function lookup(string $key) {
		return isset($this->entries[$key])
			? $this->entries[$key]
			: null;
	}
	public function getData() {
		return $this->entries;
	}
	public function asNode(DOMDocument $dataDoc) {
		return $this->strucDoc
			? $dataDoc->importNode($this->strucDoc->documentElement, true)
			: $dataDoc->createTextNode('');
	}
}