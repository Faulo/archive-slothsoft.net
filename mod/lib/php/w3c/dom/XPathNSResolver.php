<?php
/**
 * XPathNSResolver
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-XPath/xpath.html#XPathNSResolver
 */
namespace w3c\dom;

interface XPathNSResolver
{

    /**
     *
     * @param string $prefix
     * @return string
     */
    public function lookupNamespaceURI($prefix);
}