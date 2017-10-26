<?php
/**
 * XPathExpression
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-XPath/xpath.html#XPathExpression
 */

namespace w3c\dom;

interface XPathExpression {

	/**
	 * @param Node $contextNode
	 * @param int $type
	 * @param XPathResult $result
	 * @throws XPathException
	 * @throws DOMException
	 * @return XPathResult
	 */
	public function evaluate(Node $contextNode, $type, XPathResult $result);
}