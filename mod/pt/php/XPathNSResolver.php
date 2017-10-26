<?php
/**
 * XPathNSResolver
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-XPath/xpath.html#XPathNSResolver
 */
namespace Slothsoft\PT;

class XPathNSResolver implements \w3c\dom\XPathNSResolver
{

    /**
     *
     * @param string $prefix
     * @return string
     */
    public function lookupNamespaceURI($prefix)
    {}
}