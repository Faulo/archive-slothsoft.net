<?php
/**
 * DOMImplementation
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#ID-102161490
 */
namespace Slothsoft\PT;

class DOMImplementation implements \w3c\dom\DOMImplementation
{

    /**
     *
     * @param string $feature
     * @param string $version
     * @return bool
     */
    public function hasFeature($feature, $version)
    {}

    /**
     *
     * @param string $qualifiedName
     * @param string $publicId
     * @param string $systemId
     * @throws DOMException
     * @return DocumentType
     */
    public function createDocumentType($qualifiedName, $publicId, $systemId)
    {}

    /**
     *
     * @param string $namespaceURI
     * @param string $qualifiedName
     * @param DocumentType $doctype
     * @throws DOMException
     * @return Document
     */
    public function createDocument($namespaceURI, $qualifiedName, \w3c\dom\DocumentType $doctype)
    {}

    /**
     *
     * @param string $feature
     * @param string $version
     * @return DOMImplementation
     */
    public function getFeature($feature, $version)
    {}
}