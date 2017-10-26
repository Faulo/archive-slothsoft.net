<?php
/**
 * ProcessingInstruction
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#ID-1004215813
 */

namespace Slothsoft\PT;

class ProcessingInstruction extends Node implements \w3c\dom\ProcessingInstruction {

	/**
	 * @return string
	 */
	public function getTarget() {
		if ($this->name === null) {
			$this->_loadName();
		}
		return $this->name;
	}

	/**
	 * @throws DOMException
	 * @return string
	 */
	public function getData() {
		return $this->getNodeValue();
	}

	/**
	 * @param string $data
	 * @return void
	 */
	public function setData($data) {
		return $this->setNodeValue($data);
	}
}