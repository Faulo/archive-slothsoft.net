<?php
namespace Schema;
use DOMDocument;
use DOMXPath;
use DOMElement;

class DataElement {
	public static function loadDocument(DOMDocument $doc, array $schema) {
		$ret = null;
		$xpath = new DOMXpath($doc);
		$nodeList = $xpath->evaluate($schema['name']);
		foreach ($nodeList as $node) {
			$ret = new DataElement($xpath, $schema, $node);
		}
		return $ret;
	}
	protected $schema = [
		'name' => '',
		'attributes' => [],
		'elements' => [],
	];
	protected $dataXPath;
	protected $dataNode;
	protected $elementList = [];
	
	public function __construct(DOMXPath $dataXPath, array $schema, DOMElement $dataNode) {
		foreach ($this->schema as $key => &$val) {
			if (isset($schema[$key])) {
				$val = $schema[$key];
			}
		}
		unset($val);
		
		$this->dataXPath = $dataXPath;
		$this->dataNode = $dataNode;
		
		foreach ($this->schema['elements'] as $childSchema) {
			$childNodeList = $this->dataXPath->evaluate($childSchema['name'], $this->dataNode);
			foreach ($childNodeList as $childNode) {
				$this->elementList[] = new DataElement($this->dataXPath, $childSchema, $childNode);
			}
			//$childNode = $this->dataNode->ownerDocument->createElementNS($this->dataNode->namespaceURI, $childSchema['name']);
			//$this->dataNode->appendChild($childNode);
			//$this->elementList[] = new DataElement($this->dataXPath, $childSchema, $childNode);
		}
	}
	public function appendAttributeList(array $attributeList) {
		foreach ($attributeList as $key => $val) {
			$this->dataNode->setAttribute($key, $val);
		}
	}
	public function appendDataList($name, array $dataList) {
		foreach ($this->schema['elements'] as $childSchema) {
			if ($childSchema['name'] === $name) {
				foreach ($dataList as $data) {
					$childNode = $this->dataNode->ownerDocument->createElementNS($this->dataNode->namespaceURI, $childSchema['name']);
					foreach ($data as $key => $val) {
						$childNode->setAttribute($key, $val);
					}
					$this->dataNode->appendChild($childNode);
					$this->elementList[] = new DataElement($this->dataXPath, $childSchema, $childNode);
				}
			}
		}
	}
	public function getDataNode() {
		return $this->dataNode;
	}
	public function getElementList() {
		return $this->elementList;
	}
	public function getAttributes() {
		$ret = [];
		foreach ($this->schema['attributes'] as $key => $type) {
			$val = $this->dataNode->hasAttribute($key)
				? $this->dataNode->getAttribute($key)
				: null;
			switch ($type) {
				case 'string':
					break;
				case 'float':
					$val = floatval($val);
					break;
				case 'integer':
					$val = intval($val);
					break;
				case 'boolean':
					$val = $val !== null;
					break;
			}
			$ret[$key] = $val;
		}
		return $ret;
	}
}