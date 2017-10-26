<?php
/**
 * CharacterData
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#ID-FF21A306
 */

namespace PT;

class CharacterData extends Node implements \w3c\dom\CharacterData {

	/**
	 * @throws DOMException
	 * @return string
	 */
	public function getData() {
		
	}

	/**
	 * @param string $data
	 * @throws DOMException
	 * @return void
	 */
	public function setData($data) {
		
	}

	/**
	 * @return int
	 */
	public function getLength() {
		
	}

	/**
	 * @param int $offset
	 * @param int $count
	 * @throws DOMException
	 * @return string
	 */
	public function substringData($offset, $count) {
		
	}

	/**
	 * @param string $arg
	 * @throws DOMException
	 * @return void
	 */
	public function appendData($arg) {
		
	}

	/**
	 * @param int $offset
	 * @param string $arg
	 * @throws DOMException
	 * @return void
	 */
	public function insertData($offset, $arg) {
		
	}

	/**
	 * @param int $offset
	 * @param int $count
	 * @throws DOMException
	 * @return void
	 */
	public function deleteData($offset, $count) {
		
	}

	/**
	 * @param int $offset
	 * @param int $count
	 * @param string $arg
	 * @throws DOMException
	 * @return void
	 */
	public function replaceData($offset, $count, $arg) {
		
	}
}