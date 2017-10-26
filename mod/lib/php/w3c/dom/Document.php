<?php
/**
 * Document
 * 
 * @link http://www.w3.org/TR/DOM-Level-3-Core/core.html#i-Document
 */
namespace w3c\dom;

interface Document extends Node
{

    /**
     *
     * @return DocumentType
     */
    public function getDoctype();

    /**
     *
     * @return DOMImplementation
     */
    public function getImplementation();

    /**
     *
     * @return Element
     */
    public function getDocumentElement();

    /**
     *
     * @return string
     */
    public function getInputEncoding();

    /**
     *
     * @return string
     */
    public function getXmlEncoding();

    /**
     *
     * @throws DOMException
     * @return bool
     */
    public function getXmlStandalone();

    /**
     *
     * @param bool $xmlStandalone
     * @return void
     */
    public function setXmlStandalone($xmlStandalone);

    /**
     *
     * @throws DOMException
     * @return string
     */
    public function getXmlVersion();

    /**
     *
     * @param string $xmlVersion
     * @return void
     */
    public function setXmlVersion($xmlVersion);

    /**
     *
     * @return bool
     */
    public function getStrictErrorChecking();

    /**
     *
     * @param bool $strictErrorChecking
     * @return void
     */
    public function setStrictErrorChecking($strictErrorChecking);

    /**
     *
     * @return string
     */
    public function getDocumentURI();

    /**
     *
     * @param string $documentURI
     * @return void
     */
    public function setDocumentURI($documentURI);

    /**
     *
     * @return DOMConfiguration
     */
    public function getDomConfig();

    /**
     *
     * @param string $tagName
     * @throws DOMException
     * @return Element
     */
    public function createElement($tagName);

    /**
     *
     * @return DocumentFragment
     */
    public function createDocumentFragment();

    /**
     *
     * @param string $data
     * @return Text
     */
    public function createTextNode($data);

    /**
     *
     * @param string $data
     * @return Comment
     */
    public function createComment($data);

    /**
     *
     * @param string $data
     * @throws DOMException
     * @return CDATASection
     */
    public function createCDATASection($data);

    /**
     *
     * @param string $target
     * @param string $data
     * @throws DOMException
     * @return ProcessingInstruction
     */
    public function createProcessingInstruction($target, $data);

    /**
     *
     * @param string $name
     * @throws DOMException
     * @return Attr
     */
    public function createAttribute($name);

    /**
     *
     * @param string $name
     * @throws DOMException
     * @return EntityReference
     */
    public function createEntityReference($name);

    /**
     *
     * @param string $tagname
     * @return array
     */
    public function getElementsByTagName($tagname);

    /**
     *
     * @param Node $importedNode
     * @param bool $deep
     * @throws DOMException
     * @return Node
     */
    public function importNode(Node $importedNode, $deep);

    /**
     *
     * @param string $namespaceURI
     * @param string $qualifiedName
     * @throws DOMException
     * @return Element
     */
    public function createElementNS($namespaceURI, $qualifiedName);

    /**
     *
     * @param string $namespaceURI
     * @param string $qualifiedName
     * @throws DOMException
     * @return Attr
     */
    public function createAttributeNS($namespaceURI, $qualifiedName);

    /**
     *
     * @param string $namespaceURI
     * @param string $localName
     * @return array
     */
    public function getElementsByTagNameNS($namespaceURI, $localName);

    /**
     *
     * @param string $elementId
     * @return Element
     */
    public function getElementById($elementId);

    /**
     *
     * @param Node $source
     * @throws DOMException
     * @return Node
     */
    public function adoptNode(Node $source);

    /**
     *
     * @return void
     */
    public function normalizeDocument();

    /**
     *
     * @param Node $n
     * @param string $namespaceURI
     * @param string $qualifiedName
     * @throws DOMException
     * @return Node
     */
    public function renameNode(Node $n, $namespaceURI, $qualifiedName);
}