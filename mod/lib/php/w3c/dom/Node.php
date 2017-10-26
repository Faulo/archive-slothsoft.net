<?php
/**
 * Node
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#ID-1950641247
 */

namespace w3c\dom;

interface Node {

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

	/**
	 * @return string
	 */
	public function getNodeName();

	/**
	 * @throws DOMException
	 * @return string
	 */
	public function getNodeValue();

	/**
	 * @param string $nodeValue
	 * @throws DOMException
	 * @return void
	 */
	public function setNodeValue($nodeValue);

	/**
	 * @return int
	 */
	public function getNodeType();

	/**
	 * @return Node
	 */
	public function getParentNode();

	/**
	 * @return array
	 */
	public function getChildNodes();

	/**
	 * @return Node
	 */
	public function getFirstChild();

	/**
	 * @return Node
	 */
	public function getLastChild();

	/**
	 * @return Node
	 */
	public function getPreviousSibling();

	/**
	 * @return Node
	 */
	public function getNextSibling();

	/**
	 * @return array
	 */
	public function getAttributes();

	/**
	 * @return Document
	 */
	public function getOwnerDocument();

	/**
	 * @return string
	 */
	public function getNamespaceURI();

	/**
	 * @throws DOMException
	 * @return string
	 */
	public function getPrefix();

	/**
	 * @param string $prefix
	 * @return void
	 */
	public function setPrefix($prefix);

	/**
	 * @return string
	 */
	public function getLocalName();

	/**
	 * @return string
	 */
	public function getBaseURI();

	/**
	 * @throws DOMException
	 * @return string
	 */
	public function getTextContent();

	/**
	 * @param string $textContent
	 * @throws DOMException
	 * @return void
	 */
	public function setTextContent($textContent);

	/**
	 * @param Node $newChild
	 * @param Node $refChild
	 * @throws DOMException
	 * @return Node
	 */
	public function insertBefore(Node $newChild, Node $refChild);

	/**
	 * @param Node $newChild
	 * @param Node $oldChild
	 * @throws DOMException
	 * @return Node
	 */
	public function replaceChild(Node $newChild, Node $oldChild);

	/**
	 * @param Node $oldChild
	 * @throws DOMException
	 * @return Node
	 */
	public function removeChild(Node $oldChild);

	/**
	 * @param Node $newChild
	 * @throws DOMException
	 * @return Node
	 */
	public function appendChild(Node $newChild);

	/**
	 * @return bool
	 */
	public function hasChildNodes();

	/**
	 * @param bool $deep
	 * @return Node
	 */
	public function cloneNode($deep);

	/**
	 * @return void
	 */
	public function normalize();

	/**
	 * @param string $feature
	 * @param string $version
	 * @return bool
	 */
	public function isSupported($feature, $version);

	/**
	 * @return bool
	 */
	public function hasAttributes();

	/**
	 * @param Node $other
	 * @throws DOMException
	 * @return int
	 */
	public function compareDocumentPosition(Node $other);

	/**
	 * @param Node $other
	 * @return bool
	 */
	public function isSameNode(Node $other);

	/**
	 * @param string $namespaceURI
	 * @return string
	 */
	public function lookupPrefix($namespaceURI);

	/**
	 * @param string $namespaceURI
	 * @return bool
	 */
	public function isDefaultNamespace($namespaceURI);

	/**
	 * @param string $prefix
	 * @return string
	 */
	public function lookupNamespaceURI($prefix);

	/**
	 * @param Node $arg
	 * @return bool
	 */
	public function isEqualNode(Node $arg);

	/**
	 * @param string $feature
	 * @param string $version
	 * @return Node
	 */
	public function getFeature($feature, $version);

	/**
	 * @param string $key
	 * @param Object $data
	 * @param UserDataHandler $handler
	 * @return Object
	 */
	public function setUserData($key, $data, UserDataHandler $handler);

	/**
	 * @param string $key
	 * @return Object
	 */
	public function getUserData($key);
}