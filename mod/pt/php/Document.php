<?php
/**
 * Document
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#i-Document
 */

namespace PT;

class Document extends Node implements \w3c\dom\Document {
	protected $ownerRepository;
	protected $documentName;
	protected $loadedNodeList;
	protected $loadedNSList;
	public function __construct(Repository $ownerRepository, $documentName, DB\DataTable $dataTable) {
		$this->ownerRepository = $ownerRepository;
		$dataTable->init();
		$data = $dataTable->getNodeByType(self::DOCUMENT_NODE);
		if (!$data) {
			$data = $dataTable->createNode(self::DOCUMENT_NODE, null, null, null, null);
		}
		$this->loadedNSList = null;
		$this->loadedNodeList = [];
		$this->loadedNodeList[$data['id']] = $this;
		$this->init($this, $data, $dataTable);
		
		$dataTable->cleanse($this->data['id']);
	}
	protected function _createNode($type, $ns, $name, $value, $parentId = null) {
		$data = $this->dataTable->createNode($type, $ns, $name, $value, $parentId);
		return $this->_loadNode($data['id'], $data);
	}
	protected function _loadNode($id, array $data = null) {
		$retNode = null;
		if (isset($this->loadedNodeList[$id])) {
			$retNode = $this->loadedNodeList[$id];
		} else {
			if ($data === null) {
				$data = $this->dataTable->getNode($id);
			}
			if ($data !== null) {
				$retNode = $this->constructNode($data['type']);
				$retNode->init($this, $data, $this->dataTable);
				$this->loadedNodeList[$id] = $retNode;
			}
		}
		return $retNode;
	}
	protected function _loadNSList() {
		$this->loadedNSList = [];
		$idList = $this->dataTable->getNamespaceList();
		foreach ($idList as $id) {
			$this->_loadNS($id);
		}
	}
	protected function _loadNS($id) {
		$ns = is_string($id)
			? $this->ownerRepository->getNamespaceByURI($id)
			: $this->ownerRepository->getNamespaceById($id);
		if ($ns and $this->loadedNSList !== null) {
			$id = $ns->getId();
			if (!isset($this->loadedNSList[$id])) {
				$this->loadedNSList[$id] = $ns;
			}
		}
		return $ns;
	}
	protected function _loadDocument() {
		$dataList = $this->dataTable->getDataList();
		foreach ($dataList as $data) {
			$this->_loadNode($data['id'], $data);
		}
	}
	public function loadDOMDocument(\DOMDocument $doc) {
		while ($this->hasChildNodes()) {
			$this->removeChild($this->getLastChild());
		}
		$this->appendChild($this->loadDOMNode($doc));
	}
	public function saveDOMDocument() {
		$retDoc = new \DOMDocument();
		//$this->_loadDocument();
		$nodeList = $this->getChildNodes();
		foreach ($nodeList as $node) {
			if ($node = $node->saveDOMNode($retDoc)) {
				$retDoc->appendChild($node);
			}
		}
		return $retDoc;
	}

	/**
	 * @return DocumentType
	 */
	public function getDoctype() {
		return null;
	}

	/**
	 * @return DOMImplementation
	 */
	public function getImplementation() {
		return $this;
	}

	/**
	 * @return Element
	 */
	public function getDocumentElement() {
		$nodeList = $this->getChildNodes();
		foreach ($nodeList as $node) {
			if ($node->getNodeType() === self::ELEMENT_NODE) {
				return $node;
			}
		}
		return null;
	}

	/**
	 * @return string
	 */
	public function getInputEncoding() {
		
	}

	/**
	 * @return string
	 */
	public function getXmlEncoding() {
		
	}

	/**
	 * @throws DOMException
	 * @return bool
	 */
	public function getXmlStandalone() {
		
	}

	/**
	 * @param bool $xmlStandalone
	 * @return void
	 */
	public function setXmlStandalone($xmlStandalone) {
		
	}

	/**
	 * @throws DOMException
	 * @return string
	 */
	public function getXmlVersion() {
		
	}

	/**
	 * @param string $xmlVersion
	 * @return void
	 */
	public function setXmlVersion($xmlVersion) {
		
	}

	/**
	 * @return bool
	 */
	public function getStrictErrorChecking() {
		
	}

	/**
	 * @param bool $strictErrorChecking
	 * @return void
	 */
	public function setStrictErrorChecking($strictErrorChecking) {
		
	}

	/**
	 * @return string
	 */
	public function getDocumentURI() {
		
	}

	/**
	 * @param string $documentURI
	 * @return void
	 */
	public function setDocumentURI($documentURI) {
		
	}

	/**
	 * @return DOMConfiguration
	 */
	public function getDomConfig() {
		
	}

	/**
	 * @param string $tagName
	 * @throws DOMException
	 * @return Element
	 */
	public function createElement($tagName) {
		return $this->createElementNS(null, $tagName);
	}

	/**
	 * @return DocumentFragment
	 */
	public function createDocumentFragment() {
		return $this->_createNode(self::DOCUMENT_FRAGMENT_NODE, null, null, null);
	}

	/**
	 * @param string $data
	 * @return Text
	 */
	public function createTextNode($data) {
		return $this->_createNode(self::TEXT_NODE, null, null, $data);
	}

	/**
	 * @param string $data
	 * @return Comment
	 */
	public function createComment($data) {
		return $this->_createNode(self::COMMENT_NODE, null, null, $data);
	}

	/**
	 * @param string $data
	 * @throws DOMException
	 * @return CDATASection
	 */
	public function createCDATASection($data) {
		return $this->_createNode(self::CDATA_SECTION_NODE, null, null, $data);
	}

	/**
	 * @param string $target
	 * @param string $data
	 * @throws DOMException
	 * @return ProcessingInstruction
	 */
	public function createProcessingInstruction($target, $data) {
		$retNode = null;
		$namespaceURI = '';
		if ($ns = $this->lookupNS($namespaceURI)) {
			$nsId = $ns->getId();
			$tagId = $ns->getTagId($target, self::PROCESSING_INSTRUCTION_NODE);
			if ($tagId !== null) {
				$retNode = $this->_createNode(self::PROCESSING_INSTRUCTION_NODE, $nsId, $tagId, null);
				$retNode->setData($data);
			}
		}
		if (!$retNode) {
			throw new DOMException('NAMESPACE_ERR');
		}
		return $retNode;
	}

	/**
	 * @param string $name
	 * @throws DOMException
	 * @return Attr
	 */
	public function createAttribute($name) {
		return $this->createAttributeNS(null, $name);
	}

	/**
	 * @param string $name
	 * @throws DOMException
	 * @return EntityReference
	 */
	public function createEntityReference($name) {
		throw new DOMException('NOT_SUPPORTED_ERR');
	}
	
	//IMPORTED FROM DOMImplementation
	/**
	 * @param string $qualifiedName
	 * @param string $publicId
	 * @param string $systemId
	 * @throws DOMException
	 * @return DocumentType
	 */
	public function createDocumentType($qualifiedName, $publicId = null, $systemId = null) {
		$retNode = null;
		$namespaceURI = '';
		if ($ns = $this->lookupNS($namespaceURI)) {
			$nsId = $ns->getId();
			$tagId = $ns->getTagId($qualifiedName, self::DOCUMENT_TYPE_NODE);
			if ($tagId !== null) {
				$retNode = $this->_createNode(self::DOCUMENT_TYPE_NODE, $nsId, $tagId, null);
			}
		}
		if (!$retNode) {
			throw new DOMException('NAMESPACE_ERR');
		}
		return $retNode;
	}

	/**
	 * @param string $tagname
	 * @return array
	 */
	public function getElementsByTagName($tagname) {
		throw new DOMException('NOT_SUPPORTED_ERR');
	}

	/**
	 * @param Node $importedNode
	 * @param bool $deep
	 * @throws DOMException
	 * @return Node
	 */
	public function importNode(\w3c\dom\Node $importedNode, $deep) {
		throw new DOMException('NOT_SUPPORTED_ERR');
	}

	/**
	 * @param string $namespaceURI
	 * @param string $qualifiedName
	 * @throws DOMException
	 * @return Element
	 */
	public function createElementNS($namespaceURI, $qualifiedName) {
		$retNode = null;
		$namespaceURI = (string) $namespaceURI;
		if ($ns = $this->lookupNS($namespaceURI)) {
			$nsId = $ns->getId();
			$tagId = $ns->getTagId($qualifiedName, self::ELEMENT_NODE);
			if ($tagId !== null) {
				$retNode = $this->_createNode(self::ELEMENT_NODE, $nsId, $tagId, null);
			}
		}
		if (!$retNode) {
			throw new DOMException(sprintf('NAMESPACE_ERR (%s)', $namespaceURI));
		}
		return $retNode;
	}

	/**
	 * @param string $namespaceURI
	 * @param string $qualifiedName
	 * @throws DOMException
	 * @return Attr
	 */
	public function createAttributeNS($namespaceURI, $qualifiedName, $value = null, $parentId = null) {
		$retNode = null;
		$namespaceURI = (string) $namespaceURI;
		if ($ns = $this->lookupNS($namespaceURI)) {
			$nsId = $ns->getId();
			$tagId = $ns->getTagId($qualifiedName, self::ATTRIBUTE_NODE);
			if ($tagId !== null) {
				$retNode = $this->_createNode(self::ATTRIBUTE_NODE, $nsId, $tagId, $value, $parentId);
			}
		}
		if (!$retNode) {
			my_dump([$namespaceURI, $qualifiedName, $value, $parentId]);
			throw new DOMException('NAMESPACE_ERR');
		}
		return $retNode;
	}

	/**
	 * @param string $namespaceURI
	 * @param string $localName
	 * @return array
	 */
	public function getElementsByTagNameNS($namespaceURI, $localName) {
		$namespaceURI = (string) $namespaceURI;
		throw new DOMException('NOT_SUPPORTED_ERR');
	}

	/**
	 * @param string $elementId
	 * @return Element
	 */
	public function getElementById($elementId) {
		$retNode = null;
		if ($this->loadedNSList === null) {
			$this->_loadNSList();
		}
		$nameList = [];
		foreach ($this->loadedNSList as $ns) {
			$nameList = array_merge($nameList, $ns->getIdAttributeList());
		}
		if ($nameList) {
			$id = $this->dataTable->getIdByAttribute($elementId, $nameList);
			if ($id !== null) {
				$retNode = $this->_loadNode($id);
			}
		}
		return $retNode;
	}

	/**
	 * @param Node $source
	 * @throws DOMException
	 * @return Node
	 */
	public function adoptNode(\w3c\dom\Node $source) {
		throw new DOMException('NOT_SUPPORTED_ERR');
	}

	/**
	 * @return void
	 */
	public function normalizeDocument() {
		throw new DOMException('NOT_SUPPORTED_ERR');
	}

	/**
	 * @param Node $n
	 * @param string $namespaceURI
	 * @param string $qualifiedName
	 * @throws DOMException
	 * @return Node
	 */
	public function renameNode(\w3c\dom\Node $n, $namespaceURI, $qualifiedName) {
		$namespaceURI = (string) $namespaceURI;
		throw new DOMException('NOT_SUPPORTED_ERR');
	}
}