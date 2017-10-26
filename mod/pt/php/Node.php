<?php
/**
 * Node
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#ID-1950641247
 */

namespace Slothsoft\PT;

class Node implements \w3c\dom\Node {
	const CDATA_SECTION_NODE_NAME = '#cdata-section';
	const COMMENT_NODE_NAME = '#comment';
	const DOCUMENT_NODE_NAME = '#document';
	const DOCUMENT_FRAGMENT_NODE_NAME = '#document-fragment';
	const TEXT_NODE_NAME = '#text';
	
	const HTML_DOCUMENT_NODE = 13;
/*
	const ELEMENT_NODE = 1;
	const ATTRIBUTE_NODE = 2;
	const TEXT_NODE = 3;
	const CDATA_SECTION_NODE = 4;
	const ENTITY_REFERENCE_NODE = 5;
	const ENTITY_NODE = 6;
	const PROCESSING_INSTRUCTION_NODE = 7;
	const COMMENT_NODE = 8;
	const DOCUMENT_NODE = 9;
	const DOCUMENT_TYPE_NODE = 10;
	const DOCUMENT_FRAGMENT_NODE = 11;
	const NOTATION_NODE = 12;
	const DOCUMENT_POSITION_DISCONNECTED = 0x01;
	const DOCUMENT_POSITION_PRECEDING = 0x02;
	const DOCUMENT_POSITION_FOLLOWING = 0x04;
	const DOCUMENT_POSITION_CONTAINS = 0x08;
	const DOCUMENT_POSITION_CONTAINED_BY = 0x10;
	const DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC = 0x20;
	
		switch ($this->data['type']) {
			case self::ATTRIBUTE_NODE:
			case self::CDATA_SECTION_NODE:
			case self::COMMENT_NODE:
			case self::DOCUMENT_FRAGMENT_NODE:
			case self::DOCUMENT_NODE:
			case self::DOCUMENT_TYPE_NODE:
			case self::ELEMENT_NODE:
			case self::ENTITY_NODE:
			case self::ENTITY_REFERENCE_NODE:
			case self::NOTATION_NODE:
			case self::PROCESSING_INSTRUCTION_NODE:
			case self::TEXT_NODE:
				break;
		}
*/
	//DOM properties
	protected $ownerDocument;
	protected $parentNode;
	protected $childNodes;
	protected $attributes;
	protected $name;
	
	//implementation properties
	protected $readonly = false;
	public $data;
	protected $dataTable;
	
	protected function constructNode($type) {
		switch ($type) {
			case self::ELEMENT_NODE:				return new Element();
			case self::ATTRIBUTE_NODE:				return new Attr();
			case self::TEXT_NODE:					return new Text();
			case self::CDATA_SECTION_NODE:			return new CDATASection();
			case self::ENTITY_REFERENCE_NODE:		return new EntityReference();
			case self::ENTITY_NODE:					return new Entity();
			case self::PROCESSING_INSTRUCTION_NODE:	return new ProcessingInstruction();
			case self::COMMENT_NODE:				return new Comment();
			case self::DOCUMENT_NODE:				return new Document();
			case self::DOCUMENT_TYPE_NODE:			return new DocumentType();
			case self::DOCUMENT_FRAGMENT_NODE:		return new DocumentFragment();
			case self::NOTATION_NODE:				return new Notation();
		}
	}
	public function init(Document $ownerDocument, array $data, DB\DataTable $dataTable) {
		$this->ownerDocument = $ownerDocument;
		$this->data = $data;
		$this->dataTable = $dataTable;
	}
	protected function _updateDataValue() {
		$this->dataTable->updateNodeValue($this, $this->data['value']);
	}
	protected function _loadParentNode() {
		$this->parentNode = null;
		if ($this->data['parent_id'] !== null) {
			$this->parentNode = $this->ownerDocument->_loadNode($this->data['parent_id']);
		}
	}
	protected function _loadChildNodes() {
		$this->childNodes = [];
		$dataList = $this->dataTable->getChildNodes($this->data['id']);
		foreach ($dataList as $data) {
			if ($node = $this->ownerDocument->_loadNode($data['id'], $data)) {
				//echo $node . PHP_EOL;
				$this->childNodes[$data['id']] = $node;
			}
		}
	}
	protected function _loadAttributes() {
		$this->attributes = [];
		$dataList = $this->dataTable->getAttributes($this->data['id']);
		foreach ($dataList as $data) {
			if ($node = $this->ownerDocument->_loadNode($data['id'], $data)) {
				$name = $node->getNodeName();
				if (strlen($name)) {
					if (isset($this->attributes[$name])) {
						throw new DOMException('INUSE_ATTRIBUTE_ERR');
					} else {
						$this->attributes[$name] = $node;
					}
				}
			}
		}
	}
	protected function _loadName() {
		$this->name = null;
		$ns = $this->lookupNS($this->data['namespace']);
		if ($ns === null) {
			throw new DOMException('NS_ERR');
		}
		$this->name = $ns->getTagById($this->data['name']);
		if ($this->name === null) {
			throw new DOMException('NS_ERR');
		}
	}
	
	public function __toString() {
		$name = $this->getNodeName();
		if ($name === null) {
			$name = $this->getNodeType();
		}
		return sprintf('Node <%s> (%d): "%s"', $name,  $this->data['id'], $this->data['value'] === null ? 'NULL' : $this->data['value']);
	}
	
	public function loadDOMNode(\DOMNode $node) {
		$retNode = null;
		$doc = $this->ownerDocument;
		switch ($node->nodeType) {
			case self::ENTITY_NODE:
			case self::ENTITY_REFERENCE_NODE:
			case self::NOTATION_NODE:
				my_dump($node);
				break;
			case self::DOCUMENT_TYPE_NODE:
				$retNode = $doc->getImplementation()->createDocumentType($node->name, $node->publicId, $node->systemId);
				break;
			case self::PROCESSING_INSTRUCTION_NODE:
				$retNode = $doc->createProcessingInstruction($node->target, $node->data);
				break;
			case self::CDATA_SECTION_NODE:
				$retNode = $doc->createCDATASection($node->nodeValue);
				break;
			case self::COMMENT_NODE:
				$retNode = $doc->createComment($node->nodeValue);
				break;
			case self::DOCUMENT_FRAGMENT_NODE:
			case self::DOCUMENT_NODE:
			case self::HTML_DOCUMENT_NODE:
				$retNode = $doc->createDocumentFragment();
				break;
			case self::TEXT_NODE:
				$retNode = $doc->createTextNode($node->nodeValue);
				break;
			case self::ATTRIBUTE_NODE:
				$ns = $node->namespaceURI;
				if (!$ns) {
					$ns = $node->ownerElement->namespaceURI;
				}
				$retNode = $doc->createAttributeNS($ns, $node->nodeValue);
				$retNode->setTextContent($node->textContent);
				break;
			case self::ELEMENT_NODE:
				$retNode = $doc->createElementNS($node->namespaceURI, $node->localName);
				$attrList = $node->attributes;
				foreach ($attrList as $attr) {
					//echo sprintf('%s = "%s"', $attr->getNodeName(), $attr->getNodeValue()) . PHP_EOL;
					$retNode->setAttributeNS($attr->namespaceURI, $attr->name, $attr->value);
				}
				break;
			default:
				throw new DOMException(sprintf('NodeType not supported?! %d', $node->nodeType));
				break;
		}
		if ($retNode) {
			if ($node->childNodes) {
				$nodeList = $node->childNodes;
				foreach ($nodeList as $node) {
					if ($node = $this->loadDOMNode($node)) {
						$retNode->appendChild($node);
					}
				}
			}
		}
		return $retNode;
	}
	public function saveDOMNode(\DOMDocument $doc) {
		$retNode = null;
		switch ($this->data['type']) {
			case self::ENTITY_NODE:
			case self::ENTITY_REFERENCE_NODE:
			case self::NOTATION_NODE:
				my_dump($this);
				break;
			case self::DOCUMENT_TYPE_NODE:
				$retNode = $doc->implementation->createDocumentType($this->getName(), $this->getPublicId(), $this->getSystemId());
				break;
			case self::PROCESSING_INSTRUCTION_NODE:
				$retNode = $doc->createProcessingInstruction($this->getTarget(), $this->getData());
				break;
			case self::CDATA_SECTION_NODE:
				$retNode = $doc->createCDATASection($this->getNodeValue());
				break;
			case self::COMMENT_NODE:
				$retNode = $doc->createComment($this->getNodeValue());
				break;
			case self::DOCUMENT_FRAGMENT_NODE:
			case self::DOCUMENT_NODE:
				$retNode = $doc->createDocumentFragment();
				break;
			case self::TEXT_NODE:
				$retNode = $doc->createTextNode($this->getNodeValue());
				break;
			case self::ATTRIBUTE_NODE:
				$retNode = $doc->createAttribute($this->getLocalName());
				$retNode->textContent = $this->getTextContent();
				break;
			case self::ELEMENT_NODE:
				$retNode = $doc->createElementNS($this->getNamespaceURI(), $this->getLocalName());
				//*
				$attrList = $this->getAttributes();
				//my_dump($attrList);
				foreach ($attrList as $attr) {
					//echo sprintf('%s = "%s"', $attr->getNodeName(), $attr->getNodeValue()) . PHP_EOL;
					$uri = $attr->getNamespaceURI();
					$name = $attr->getName();
					if ($uri) {
						if ($uri === $this->getNamespaceURI()) {
							$uri = '';
						} else {
							$ns = $this->lookupNS($uri);
							$name = $ns->getPrefix() . ':' . $name;
						}
					}
					$retNode->setAttributeNS($uri, $name, $attr->getValue());
				}
				//*/
				break;
		}
		if ($retNode) {
			$nodeList = $this->getChildNodes();
			foreach ($nodeList as $node) {
				if ($node = $node->saveDOMNode($doc)) {
					$retNode->appendChild($node);
				}
			}
		}
		return $retNode;
	}
	
	/**
	 * @return string
	 */
	public function getNodeName() {
		$ret = null;
		switch ($this->data['type']) {
			case self::CDATA_SECTION_NODE:
				$ret = self::CDATA_SECTION_NODE_NAME;
				break;
			case self::COMMENT_NODE:
				$ret = self::COMMENT_NODE_NAME;
				break;
			case self::DOCUMENT_FRAGMENT_NODE:
				$ret = self::DOCUMENT_FRAGMENT_NODE_NAME;
				break;
			case self::DOCUMENT_NODE:
				$ret = self::DOCUMENT_NODE_NAME;
				break;
			case self::TEXT_NODE:
				$ret = self::TEXT_NODE_NAME;
				break;
			case self::ATTRIBUTE_NODE:
			case self::ELEMENT_NODE:
				$ret = $this->getLocalName();
				break;
			case self::DOCUMENT_TYPE_NODE:
			case self::ENTITY_NODE:
			case self::ENTITY_REFERENCE_NODE:
			case self::NOTATION_NODE:
			case self::PROCESSING_INSTRUCTION_NODE:
				break;
		}
		return $ret;
	}
	
	/**
	 * @throws DOMException
	 * @return string
	 */
	public function getNodeValue() {
		return $this->data['value'];
	}

	/**
	 * @param string $nodeValue
	 * @throws DOMException
	 * @return void
	 */
	public function setNodeValue($nodeValue) {
		$nodeValue = (string) $nodeValue;
		switch ($this->data['type']) {
			case self::ATTRIBUTE_NODE:
			case self::CDATA_SECTION_NODE:
			case self::COMMENT_NODE:
			case self::PROCESSING_INSTRUCTION_NODE:
			case self::TEXT_NODE:
				if ($this->readonly) {
					throw new DOMException('NO_MODIFICATION_ALLOWED_ERR');
				}
				$this->dataTable->updateNodeValue($this, $nodeValue);
				break;
			case self::DOCUMENT_FRAGMENT_NODE:
			case self::DOCUMENT_NODE:
			case self::DOCUMENT_TYPE_NODE:
			case self::ELEMENT_NODE:
			case self::ENTITY_NODE:
			case self::ENTITY_REFERENCE_NODE:
			case self::NOTATION_NODE:
				break;
		}
	}

	/**
	 * @return int
	 */
	public function getNodeType() {
		return $this->data['type'];
	}

	/**
	 * @return Node
	 */
	public function getParentNode() {
		$retNode = null;
		switch ($this->data['type']) {
			case self::ATTRIBUTE_NODE:
				break;
			case self::CDATA_SECTION_NODE:
			case self::COMMENT_NODE:
			case self::PROCESSING_INSTRUCTION_NODE:
			case self::TEXT_NODE:
			case self::DOCUMENT_FRAGMENT_NODE:
			case self::DOCUMENT_NODE:
			case self::DOCUMENT_TYPE_NODE:
			case self::ELEMENT_NODE:
			case self::ENTITY_NODE:
			case self::ENTITY_REFERENCE_NODE:
			case self::NOTATION_NODE:
				if ($this->parentNode === null) {
					$this->_loadParentNode();
				}
				$retNode = $this->parentNode;
				break;
		}
		return $retNode;
	}

	/**
	 * @return array
	 */
	public function getChildNodes() {
		if ($this->childNodes === null) {
			$this->_loadChildNodes();
		}
		return array_values($this->childNodes);
	}

	/**
	 * @return Node
	 */
	public function getFirstChild() {
		if ($this->childNodes === null) {
			$this->_loadChildNodes();
		}
		return reset($this->childNodes);
	}

	/**
	 * @return Node
	 */
	public function getLastChild() {
		if ($this->childNodes === null) {
			$this->_loadChildNodes();
		}
		return end($this->childNodes);
	}

	/**
	 * @return Node
	 */
	public function getPreviousSibling() {
		$retNode = null;
		if ($parentNode = $this->getParentNode()) {
			$nodeList = $parentNode->getChildNodes();
			$previousNode = null;
			foreach ($nodeList as $node) {
				if ($node === $this) {
					$retNode = $previousNode;
				}
				$previousNode = $node;
			}
		}
		return $retNode;
	}

	/**
	 * @return Node
	 */
	public function getNextSibling() {
		$retNode = null;
		if ($parentNode = $this->getParent()) {
			$nodeList = $parentNode->getChildNodes();
			$previousNode = null;
			foreach ($nodeList as $node) {
				if ($previousNode === $this) {
					$retNode = $node;
				}
				$previousNode = $node;
			}
		}
		return $retNode;
	}

	/**
	 * @return array
	 */
	public function getAttributes() {
		if ($this->attributes === null) {
			$this->_loadAttributes();
		}
		return $this->attributes;
	}

	/**
	 * @return Document
	 */
	public function getOwnerDocument() {
		return $this->ownerDocument;
	}

	/**
	 * @return string
	 */
	public function getNamespaceURI() {
		$ret = null;
		switch ($this->data['type']) {
			case self::CDATA_SECTION_NODE:
			case self::COMMENT_NODE:
			case self::DOCUMENT_FRAGMENT_NODE:
			case self::DOCUMENT_NODE:
			case self::DOCUMENT_TYPE_NODE:
			case self::ENTITY_NODE:
			case self::ENTITY_REFERENCE_NODE:
			case self::NOTATION_NODE:
			case self::PROCESSING_INSTRUCTION_NODE:
			case self::TEXT_NODE:
				break;
			case self::ATTRIBUTE_NODE:
			case self::ELEMENT_NODE:
				$ns = $this->lookupNS($this->data['namespace']);
				if (!$ns) {
					throw new DOMException('NS_ERR');
				}
				$ret = $ns->getURI();
				break;
		}
		return $ret;
	}

	/**
	 * @throws DOMException
	 * @return string
	 */
	public function getPrefix() {
		
	}

	/**
	 * @param string $prefix
	 * @return void
	 */
	public function setPrefix($prefix) {
		
	}

	/**
	 * @return string
	 */
	public function getLocalName() {
		$ret = null;
		switch ($this->data['type']) {
			case self::ATTRIBUTE_NODE:
			case self::ELEMENT_NODE:
				if ($this->name === null) {
					$this->_loadName();
				}
				$ret = $this->name;
				break;
			case self::CDATA_SECTION_NODE:
			case self::COMMENT_NODE:
			case self::DOCUMENT_FRAGMENT_NODE:
			case self::DOCUMENT_NODE:
			case self::TEXT_NODE:
			case self::DOCUMENT_TYPE_NODE:
			case self::ENTITY_NODE:
			case self::ENTITY_REFERENCE_NODE:
			case self::NOTATION_NODE:
			case self::PROCESSING_INSTRUCTION_NODE:
				break;
		}
		return $ret;
	}

	/**
	 * @return string
	 */
	public function getBaseURI() {
		
	}

	/**
	 * @throws DOMException
	 * @return string
	 */
	public function getTextContent() {
		switch ($this->data['type']) {
			case self::ATTRIBUTE_NODE:
			case self::CDATA_SECTION_NODE:
			case self::COMMENT_NODE:
			case self::PROCESSING_INSTRUCTION_NODE:
			case self::TEXT_NODE:
				$ret = $this->getNodeValue();
				break;
			case self::DOCUMENT_FRAGMENT_NODE:
			case self::ELEMENT_NODE:
			case self::ENTITY_NODE:
			case self::ENTITY_REFERENCE_NODE:
			case self::DOCUMENT_NODE:
			case self::DOCUMENT_TYPE_NODE:
			case self::NOTATION_NODE:
				$ret = '';
				$nodeList = $this->getChildNodes();
				foreach ($nodeList as $node) {
					$ret .= $node->getTextContent();
				}
				break;
		}
		return $ret;
	}

	/**
	 * @param string $textContent
	 * @throws DOMException
	 * @return void
	 */
	public function setTextContent($textContent) {
		$textContent = (string) $textContent;
		if ($this->readonly) {
			throw new DOMException('NO_MODIFICATION_ALLOWED_ERR');
		}
		switch ($this->data['type']) {
			case self::ATTRIBUTE_NODE:
			case self::CDATA_SECTION_NODE:
			case self::COMMENT_NODE:
			case self::PROCESSING_INSTRUCTION_NODE:
			case self::TEXT_NODE:
				$nodeList = $this->getChildNodes();
				foreach ($nodeList as $node) {
					$this->removeChild($node);
				}
				$this->setNodeValue($textContent);
				break;
			case self::DOCUMENT_FRAGMENT_NODE:
			case self::ELEMENT_NODE:
			case self::ENTITY_NODE:
			case self::ENTITY_REFERENCE_NODE:
				$nodeList = $this->getChildNodes();
				$textNode = null;
				foreach ($nodeList as $node) {
					if ($node->getNodeType() === self::TEXT_NODE and !$textNode) {
						$textNode = $node;
					} else {
						$this->removeChild($node);
					}
				}
				if (strlen($textContent)) {
					if ($textNode) {
						$textNode->setTextContent($textContent);
					} else {
						$textNode = $this->ownerDocument->createTextNode($textContent);
						$this->appendChild($textNode);
					}
				}
				break;
			case self::DOCUMENT_NODE:
			case self::DOCUMENT_TYPE_NODE:
			case self::NOTATION_NODE:
				break;
		}
	}

	/**
	 * @param Node $newChild
	 * @param Node $refChild
	 * @throws DOMException
	 * @return Node
	 */
	public function insertBefore(\w3c\dom\Node $newChild, \w3c\dom\Node $refChild) {
		return $this->appendChild($newChild);
	}

	/**
	 * @param Node $newChild
	 * @param Node $oldChild
	 * @throws DOMException
	 * @return Node
	 */
	public function replaceChild(\w3c\dom\Node $newChild, \w3c\dom\Node $oldChild) {
		$this->removeChild($oldChild);
		$this->appendChild($newChild);
		return $oldChild;
	}

	/**
	 * @param Node $oldChild
	 * @throws DOMException
	 * @return Node
	 */
	public function removeChild(\w3c\dom\Node $oldChild) {
		if ($this->readonly) {
			throw new DOMException('NO_MODIFICATION_ALLOWED_ERR');
		}
		if ($this->data['id'] !== $oldChild->data['parent_id']) {
			throw new DOMException('NOT_FOUND_ERR');
		}
		$childId = $oldChild->data['id'];
		$this->dataTable->updateNodeParent($oldChild, null);
		if ($this->childNodes !== null) {
			if (isset($this->childNodes[$childId])) {
				unset($this->childNodes[$childId]);
			} else {
				$this->childNodes = null;
			}
		}
		return $oldChild;
	}

	/**
	 * @param Node $newChild
	 * @throws DOMException
	 * @return Node
	 */
	public function appendChild(\w3c\dom\Node $newChild) {
		switch ($newChild->getNodeType()) {
			case self::DOCUMENT_FRAGMENT_NODE:
				$nodeList = $newChild->getChildNodes();
				foreach ($nodeList as $node) {
					$this->appendChild($node);
				}
				break;
			case self::ATTRIBUTE_NODE:
				if ($this->getNodeType() !== self::ELEMENT_NODE) {
					throw new DOMException('HIERARCHY_REQUEST_ERR');
				}
				if ($this->attributes === null) {
					$this->_loadAttributes();
				}
				$name = $newChild->getNodeName();
				if (isset($this->attributes[$name])) {
					$this->removeAttributeNode($this->attributes[$name]);
				}
				$this->dataTable->updateNodeParent($newChild, $this->data['id']);
				$this->attributes[$name] = $newChild;
				break;
			case self::CDATA_SECTION_NODE:
			case self::COMMENT_NODE:
			case self::DOCUMENT_NODE:
			case self::DOCUMENT_TYPE_NODE:
			case self::ELEMENT_NODE:
			case self::ENTITY_NODE:
			case self::ENTITY_REFERENCE_NODE:
			case self::NOTATION_NODE:
			case self::PROCESSING_INSTRUCTION_NODE:
			case self::TEXT_NODE:
				$this->dataTable->updateNodeParent($newChild, $this->data['id']);
				if ($this->childNodes !== null) {
					$this->childNodes[$newChild->data['id']] = $newChild;
				}
				break;
		}
		$this->ownerDocument->_loadNS($newChild->data['namespace']);
		return $newChild;
	}

	/**
	 * @return bool
	 */
	public function hasChildNodes() {
		return (bool) count($this->getChildNodes());
	}

	/**
	 * @param bool $deep
	 * @return Node
	 */
	public function cloneNode($deep) {
		throw new DOMException('NOT_SUPPORTED_ERR');
	}

	/**
	 * @return void
	 */
	public function normalize() {
		throw new DOMException('NOT_SUPPORTED_ERR');
	}

	/**
	 * @param string $feature
	 * @param string $version
	 * @return bool
	 */
	public function isSupported($feature, $version) {
		throw new DOMException('NOT_SUPPORTED_ERR');
	}

	/**
	 * @return bool
	 */
	public function hasAttributes() {
		throw new DOMException('NOT_SUPPORTED_ERR');
	}

	/**
	 * @param Node $other
	 * @throws DOMException
	 * @return int
	 */
	public function compareDocumentPosition(\w3c\dom\Node $other) {
		throw new DOMException('NOT_SUPPORTED_ERR');
	}

	/**
	 * @param Node $other
	 * @return bool
	 */
	public function isSameNode(\w3c\dom\Node $other) {
		throw new DOMException('NOT_SUPPORTED_ERR');
	}

	/**
	 * @param string $namespaceURI
	 * @return string
	 */
	public function lookupPrefix($namespaceURI) {
		throw new DOMException('NOT_SUPPORTED_ERR');
	}

	/**
	 * @param string $namespaceURI
	 * @return bool
	 */
	public function isDefaultNamespace($namespaceURI) {
		throw new DOMException('NOT_SUPPORTED_ERR');
	}

	/**
	 * @param string $prefix
	 * @return string
	 */
	public function lookupNamespaceURI($prefix) {
		throw new DOMException('NOT_SUPPORTED_ERR');
	}
	/**
	 * @param mixed $idOrURI
	 * @return NS
	 */
	public function lookupNS($idOrURI) {
		return $this->ownerDocument->_loadNS($idOrURI);
	}

	/**
	 * @param Node $arg
	 * @return bool
	 */
	public function isEqualNode(\w3c\dom\Node $arg) {
		throw new DOMException('NOT_SUPPORTED_ERR');
	}

	/**
	 * @param string $feature
	 * @param string $version
	 * @return Node
	 */
	public function getFeature($feature, $version) {
		throw new DOMException('NOT_SUPPORTED_ERR');
	}

	/**
	 * @param string $key
	 * @param Object $data
	 * @param UserDataHandler $handler
	 * @return Object
	 */
	public function setUserData($key, $data, \w3c\dom\UserDataHandler $handler) {
		throw new DOMException('NOT_SUPPORTED_ERR');
	}

	/**
	 * @param string $key
	 * @return Object
	 */
	public function getUserData($key) {
		throw new DOMException('NOT_SUPPORTED_ERR');
	}
}