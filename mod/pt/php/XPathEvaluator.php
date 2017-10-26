<?php
/**
 * XPathEvaluator
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-XPath/xpath.html#XPathEvaluator
 */
namespace Slothsoft\PT;

class XPathEvaluator implements \w3c\dom\XPathEvaluator
{

    /**
     *
     * @param string $expression
     * @param XPathNSResolver $resolver
     * @throws XPathException
     * @throws DOMException
     * @return XPathExpression
     */
    public function createExpression($expression, \w3c\dom\XPathNSResolver $resolver)
    {}

    /**
     *
     * @param Node $nodeResolver
     * @return XPathNSResolver
     */
    public function createNSResolver(\w3c\dom\Node $nodeResolver)
    {}

    /**
     *
     * @param string $expression
     * @param Node $contextNode
     * @param XPathNSResolver $resolver
     * @param int $type
     * @param XPathResult $result
     * @throws XPathException
     * @throws DOMException
     * @return XPathResult
     */
    public function evaluate($expression, \w3c\dom\Node $contextNode, \w3c\dom\XPathNSResolver $resolver, $type, \w3c\dom\XPathResult $result)
    {}
}