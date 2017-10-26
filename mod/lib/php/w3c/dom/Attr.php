<?php
/**
 * Attr
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#ID-637646024
 */

namespace w3c\dom;

interface Attr extends Node {

	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @return bool
	 */
	public function getSpecified();

	/**
	 * @throws DOMException
	 * @return string
	 */
	public function getValue();

	/**
	 * @param string $value
	 * @return void
	 */
	public function setValue($value);

	/**
	 * @return Element
	 */
	public function getOwnerElement();

	/**
	 * @return TypeInfo
	 */
	public function getSchemaTypeInfo();

	/**
	 * @return bool
	 */
	public function getIsId();
}