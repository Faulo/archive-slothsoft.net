<?php
/**
 * XPathExpression
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-XPath/xpath.html#XPathExpression
 */

namespace Slothsoft\PT;

class XPathExpression implements \w3c\dom\XPathExpression {

	/**
	 * @param Node $contextNode
	 * @param int $type
	 * @param XPathResult $result
	 * @throws XPathException
	 * @throws DOMException
	 * @return XPathResult
	 */
	public function evaluate(\w3c\dom\Node $contextNode, $type, \w3c\dom\XPathResult $result) {
		
	}
}