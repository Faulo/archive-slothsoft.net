<?php
namespace Slothsoft\PT;

use Slothsoft\Core\Storage;

class NS {
	protected $ownerRepository;
	protected $indexData;
	protected $strucTable;
	protected $strucList;
	protected $isNullNS;
	protected $autoCreate;
	public function __construct(Repository $ownerRepository, array $indexData, DB\StrucTable $strucTable) {
		$this->ownerRepository = $ownerRepository;
		$this->indexData = $indexData;
		$this->strucTable = $strucTable;
		$this->strucTable->init();
		$this->strucList = $this->strucTable->getStrucList();
		if (!$this->strucList) {
			//$this->installSpec();
		}
		$this->installSpec();
		
		$this->isNullNS = $this->indexData['id'] === null;
		$this->autoCreate = $this->isNullNS;
	}
	
	//index
	public function getId() {
		return $this->indexData['id'];
	}
	public function getName() {
		return $this->indexData['name'];
	}
	public function getURI() {
		return $this->indexData['uri'];
	}
	public function getPrefix() {
		return $this->indexData['prefix'];
	}
	public function getSpec() {
		return $this->indexData['spec'];
	}
	
	//records
	public function getTagById($tagId) {
		$retTag = null;
		foreach ($this->strucList as $struc) {
			if ($struc['id'] === $tagId) {
				$retTag = $struc['name'];
				break;
			}
		}
		return $retTag;
	}
	public function getTagId($qualifiedName, $nodeType) {
		$retId = null;
		$name = $qualifiedName;
		foreach ($this->strucList as $struc) {
			if ($struc['name'] === $name and $struc['type'] === $nodeType) {
				$retId = $struc['id'];
				break;
			}
		}
		if (!$retId and $this->autoCreate) {
			my_dump(sprintf('Auto-creating %s...', $qualifiedName));
			//die();
			$struc = $this->createStruc($name, $nodeType);
			$retId = $struc['id'];
		}
		return $retId;
	}
	public function getIdAttributeList() {
		$ret = [];
		foreach ($this->strucList as $struc) {
			if ($struc['type'] === Node::ATTRIBUTE_NODE) {
				if ($struc['options']) {
					if (isset($struc['options']['isId'])) {
						$ret[] = $struc['id'];
					}
				}
			}
		}
		return $ret;
	}
	
	//spec
	public function createStruc($name, $nodeType, array $options = null) {
		if ($options !== null) {
			$options = json_encode($options);
		}
		$struc = $this->strucTable->createStruc($nodeType, $name, $options);
		$this->strucList[] = $struc;
		return $struc;
	}
	public function updateStruc($name, $nodeType, array $options = null) {
		if ($options !== null) {
			$options = json_encode($options);
		}
		$ret = false;
		foreach ($this->strucList as &$struc) {
			if ($struc['name'] === $name and $struc['type'] === $nodeType) {
				$ret = true;
				if ($struc['options'] !== $options) {
					$struc['options'] = $options;
					$this->strucTable->updateStrucOptions($struc['id'], $options);
				}
				break;
			}
		}
		unset($struc);
		return $ret;
	}
	public function installSpec() {
		if ($specURI = $this->getSpec()) {
			if ($doc = Storage::loadExternalDocument($specURI)) {
				$xpath = new \DOMXPath($doc);
				$xpath->registerNamespace('xsd', 'http://www.w3.org/2001/XMLSchema');
				
				//Elements
				$nodeList = $xpath->evaluate('//xsd:element[@name]');
				$type = Node::ELEMENT_NODE;
				$options = null;
				foreach ($nodeList as $node) {
					$name = $node->getAttribute('name');
					if (!$this->updateStruc($name, $type, $options)) {
						$this->createStruc($name, $type, $options);
					}
				}
				
				//Attributes
				$nodeList = $xpath->evaluate('//xsd:attribute[@name]');
				$type = Node::ATTRIBUTE_NODE;
				$options = [];
				foreach ($nodeList as $node) {
					$name = $node->getAttribute('name');
					$type = $node->getAttribute('type');
					switch ($type) {
						case 'id':
							$options['isId'] = true;
							break;
					}
					if (!$this->updateStruc($name, $type, $options)) {
						$this->createStruc($name, $type, $options);
					}
				}
			}
		}
	}
}