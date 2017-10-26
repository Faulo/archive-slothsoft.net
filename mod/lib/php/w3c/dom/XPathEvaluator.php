<?php
/**
 * XPathEvaluator
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-XPath/xpath.html#XPathEvaluator
 */
namespace w3c\dom;

interface XPathEvaluator
{

    /**
     *
     * @param string $expression
     * @param XPathNSResolver $resolver
     * @throws XPathException
     * @throws DOMException
     * @return XPathExpression
     */
    public function createExpression($expression, XPathNSResolver $resolver);

    /**
     *
     * @param Node $nodeResolver
     * @return XPathNSResolver
     */
    public function createNSResolver(Node $nodeResolver);

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
    public function evaluate($expression, Node $contextNode, XPathNSResolver $resolver, $type, XPathResult $result);
}