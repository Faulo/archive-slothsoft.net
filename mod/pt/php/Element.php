<?php
/**
 * Element
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#ID-745549614
 */

namespace Slothsoft\PT;

class Element extends Node implements \w3c\dom\Element {
	
	protected function _canonicalizeNS(&$namespaceURI, &$name) {
		$namespaceURI = (string) $namespaceURI;
		if ($namespaceURI === '') {
			$namespaceURI = $this->getNamespaceURI();
		}
		if (strpos($name, 'data-') === 0) {
			$namespaceURI = '';
		}
	}
	protected function _appendAttribute($namespaceURI, $qualifiedName, $value) {
		$name = $qualifiedName;
		$this->_canonicalizeNS($namespaceURI, $name);
		
		$attr = $this->ownerDocument->createAttributeNS($namespaceURI, $name, $value, $this->data['id']);
		return $this->setAttributeNodeNS($attr);
	}
	/**
	 * @return string
	 */
	public function getTagName() {
		return $this->getNodeName();
	}

	/**
	 * @return TypeInfo
	 */
	public function getSchemaTypeInfo() {
		
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function getAttribute($name) {
		return $this->getAttributeNS(null, $name);
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @throws DOMException
	 * @return void
	 */
	public function setAttribute($name, $value) {
		return $this->setAttributeNS(null, $name, $value);
	}

	/**
	 * @param string $name
	 * @throws DOMException
	 * @return void
	 */
	public function removeAttribute($name) {
		return $this->removeAttributeNS(null, $name);
	}

	/**
	 * @param string $name
	 * @return Attr
	 */
	public function getAttributeNode($name) {
		return $this->getAttributeNodeNS(null, $name);
	}

	/**
	 * @param Attr $newAttr
	 * @throws DOMException
	 * @return Attr
	 */
	public function setAttributeNode(\w3c\dom\Attr $newAttr) {
		return $this->setAttributeNodeNS($newAttr);
	}

	/**
	 * @param Attr $oldAttr
	 * @throws DOMException
	 * @return Attr
	 */
	public function removeAttributeNode(\w3c\dom\Attr $oldAttr) {
		if ($this->readonly) {
			throw new DOMException('NO_MODIFICATION_ALLOWED_ERR');
		}
		if ($this->data['id'] !== $oldAttr->data['parent_id']) {
			throw new DOMException('NOT_FOUND_ERR');
		}
		$this->dataTable->updateNodeParent($oldAttr, null);
		if ($this->attributes !== null) {
			$found = false;
			foreach ($this->attributes as $name => $node) {
				if ($node === $oldAttr) {
					$found = true;
					break;
				}
			}
			if ($found) {
				unset($this->attributes[$name]);
			}
		}
		return $oldAttr;
	}

	/**
	 * @param string $name
	 * @return array
	 */
	public function getElementsByTagName($name) {
		
	}

	/**
	 * @param string $namespaceURI
	 * @param string $localName
	 * @throws DOMException
	 * @return string
	 */
	public function getAttributeNS($namespaceURI, $localName) {
		$name = $localName;
		$this->_canonicalizeNS($namespaceURI, $name);
		$ret = '';
		if ($attr = $this->getAttributeNodeNS($namespaceURI, $name)) {
			$ret = $attr->getValue();
		}
		return $ret;
	}

	/**
	 * @param string $namespaceURI
	 * @param string $qualifiedName
	 * @param string $value
	 * @throws DOMException
	 * @return void
	 */
	public function setAttributeNS($namespaceURI, $qualifiedName, $value) {
		$name = $qualifiedName;
		$this->_canonicalizeNS($namespaceURI, $name);
		if ($attr = $this->getAttributeNodeNS($namespaceURI, $name)) {
			$attr->setValue($value);
		} else {
			$this->_appendAttribute($namespaceURI, $qualifiedName, $value);
		}
	}

	/**
	 * @param string $namespaceURI
	 * @param string $localName
	 * @throws DOMException
	 * @return void
	 */
	public function removeAttributeNS($namespaceURI, $localName) {
		$name = $localName;
		$this->_canonicalizeNS($namespaceURI, $name);
		if ($attr = $this->getAttributeNodeNS($namespaceURI, $name)) {
			$this->removeAttributeNode($attr);
		}
	}

	/**
	 * @param string $namespaceURI
	 * @param string $localName
	 * @throws DOMException
	 * @return Attr
	 */
	public function getAttributeNodeNS($namespaceURI, $localName) {
		$name = $localName;
		$this->_canonicalizeNS($namespaceURI, $name);
		$retNode = null;
		$attrList = $this->getAttributes();
		foreach ($attrList as $attr) {
			if ($attr->getNamespaceURI() === $namespaceURI and $attr->getName() === $name) {
				$retNode = $attr;
				break;
			}
		}
		return $retNode;
	}

	/**
	 * @param Attr $newAttr
	 * @throws DOMException
	 * @return Attr
	 */
	public function setAttributeNodeNS(\w3c\dom\Attr $newAttr) {
		return $this->appendChild($newAttr);
	}

	/**
	 * @param string $namespaceURI
	 * @param string $localName
	 * @throws DOMException
	 * @return array
	 */
	public function getElementsByTagNameNS($namespaceURI, $localName) {
		$name = $localName;
		$this->_canonicalizeNS($namespaceURI, $name);
		
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasAttribute($name) {
		return $this->hasAttributeNS(null, $name);
	}

	/**
	 * @param string $namespaceURI
	 * @param string $localName
	 * @throws DOMException
	 * @return bool
	 */
	public function hasAttributeNS($namespaceURI, $localName) {
		$name = $localName;
		$this->_canonicalizeNS($namespaceURI, $name);
		return (bool) $this->getAttributeNodeNS($namespaceURI, $name);
	}

	/**
	 * @param string $name
	 * @param bool $isId
	 * @throws DOMException
	 * @return void
	 */
	public function setIdAttribute($name, $isId) {
		
	}

	/**
	 * @param string $namespaceURI
	 * @param string $localName
	 * @param bool $isId
	 * @throws DOMException
	 * @return void
	 */
	public function setIdAttributeNS($namespaceURI, $localName, $isId) {
		$name = $localName;
		$this->_canonicalizeNS($namespaceURI, $name);
		
	}

	/**
	 * @param Attr $idAttr
	 * @param bool $isId
	 * @throws DOMException
	 * @return void
	 */
	public function setIdAttributeNode(\w3c\dom\Attr $idAttr, $isId) {
		
	}
}