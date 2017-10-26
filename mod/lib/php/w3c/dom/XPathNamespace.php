<?php
/**
 * XPathNamespace
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-XPath/xpath.html#XPathNamespace
 */

namespace w3c\dom;

interface XPathNamespace extends Node {

	const XPATH_NAMESPACE_NODE = 13;

	/**
	 * @return Element
	 */
	public function getOwnerElement();
}