<?php
/**
 * ProcessingInstruction
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#ID-1004215813
 */

namespace w3c\dom;

interface ProcessingInstruction extends Node {

	/**
	 * @return string
	 */
	public function getTarget();

	/**
	 * @throws DOMException
	 * @return string
	 */
	public function getData();

	/**
	 * @param string $data
	 * @return void
	 */
	public function setData($data);
}