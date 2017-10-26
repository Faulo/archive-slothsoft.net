<?php
/**
 * Attr
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#ID-637646024
 */

namespace PT;

class Attr extends Node implements \w3c\dom\Attr {

	/**
	 * @return string
	 */
	public function getName() {
		return $this->getNodeName();
	}

	/**
	 * @return bool
	 */
	public function getSpecified() {
		return true;
	}

	/**
	 * @throws DOMException
	 * @return string
	 */
	public function getValue() {
		return $this->getNodeValue();
	}

	/**
	 * @param string $value
	 * @return void
	 */
	public function setValue($value) {
		return $this->setNodeValue($value);
	}

	/**
	 * @return Element
	 */
	public function getOwnerElement() {
		if ($this->parentNode === null) {
			$this->_loadParentNode();
		}
		return $this->parentNode;
	}

	/**
	 * @return TypeInfo
	 */
	public function getSchemaTypeInfo() {
		
	}

	/**
	 * @return bool
	 */
	public function getIsId() {
		return false;
	}
}